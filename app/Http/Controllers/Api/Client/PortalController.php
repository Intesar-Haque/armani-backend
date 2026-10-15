<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Document;
use App\Models\Installment;
use App\Models\ProjectDirector;
use App\Models\ShareAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PortalController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $client = $request->user();

        $bookings = $client->bookings()->withSum('installments as total_paid', 'paid_amount')->get();
        $totalValue = $bookings->sum(fn($b) => (float) $b->total_amount);
        $totalPaid = $bookings->sum(fn($b) => (float) ($b->total_paid ?? 0));

        $installments = Installment::whereIn('booking_id', $bookings->pluck('id'))->orderBy('due_date')->get();
        $shares = ShareAssignment::where('client_id', $client->id)->where('status', ShareAssignment::STATUS_ACTIVE)->get();

        return response()->json([
            'bookings_count' => $bookings->count(),
            'total_value' => $totalValue,
            'total_paid' => $totalPaid,
            'total_balance' => $totalValue - $totalPaid,
            'overdue_installments_count' => $installments->filter->isOverdue()->count(),
            'next_installment' => $installments->first(fn($i) => ! $i->isPaid()),
            'shares_count' => (int) $shares->sum('shares_count'),
            'shares_value' => (float) $shares->sum('total_amount'),
            'directorships_count' => ProjectDirector::where('client_id', $client->id)->count(),
        ]);
    }

    public function bookings(Request $request): JsonResponse
    {
        $bookings = $request->user()->bookings()
            ->with(['project', 'apartment'])
            ->withSum('installments as total_paid', 'paid_amount')
            ->latest('booking_date')
            ->get()
            ->each(function (Booking $b) {
                $b->total_paid = (float) ($b->total_paid ?? 0);
                $b->balance = (float) $b->total_amount - $b->total_paid;
                $b->plan_label = $b->planLabel();
            });

        return response()->json(['data' => $bookings]);
    }

    public function booking(Request $request, Booking $booking): JsonResponse
    {
        $this->authorizeBooking($request, $booking);

        $booking->load(['project', 'apartment', 'installments', 'documents']);
        $booking->installments->each(fn(Installment $i) => $i->status = $i->statusLabel());
        $booking->documents->each(fn(Document $d) => $d->download_url = $this->downloadUrl($booking, $d));
        $booking->total_paid = $booking->totalPaid();
        $booking->balance = $booking->balance();
        $booking->plan_label = $booking->planLabel();

        return response()->json(['data' => $booking]);
    }

    public function installments(Request $request): JsonResponse
    {
        $installments = Installment::with('booking.project')
            ->whereHas('booking', fn($q) => $q->where('client_id', $request->user()->id))
            ->orderBy('due_date')
            ->get()
            ->each(fn(Installment $i) => $i->status = $i->statusLabel());

        if ($status = $request->query('status')) {
            $installments = $installments->where('status', $status)->values();
        }

        return response()->json(['data' => $installments]);
    }

    public function downloadDocument(Request $request, Booking $booking, Document $document): StreamedResponse
    {
        $this->authorizeBooking($request, $booking);
        abort_unless($document->booking_id === $booking->id, 404);
        abort_unless((bool) $document->file_path, 404);

        return Storage::disk('local')->download($document->file_path, $document->original_filename ?: $document->title);
    }

    public function shares(Request $request): JsonResponse
    {
        return response()->json([
            'data' => ShareAssignment::with('project')
                ->where('client_id', $request->user()->id)
                ->latest('assigned_date')
                ->get(),
        ]);
    }

    public function directorships(Request $request): JsonResponse
    {
        return response()->json([
            'data' => ProjectDirector::with('project')
                ->where('client_id', $request->user()->id)
                ->latest('appointed_date')
                ->get(),
        ]);
    }

    private function authorizeBooking(Request $request, Booking $booking): void
    {
        abort_unless($booking->client_id === $request->user()->id, 403);
    }

    private function downloadUrl(Booking $booking, Document $document): ?string
    {
        return $document->file_path
            ? route('api.client.bookings.documents.download', [$booking, $document])
            : null;
    }
}
