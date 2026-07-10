<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Document;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingController extends Controller
{
    public function show(Request $request, Booking $booking): View
    {
        abort_unless($booking->client_id === $request->user()->id, 403);

        $booking->load(['project', 'installments', 'documents']);

        $installments = $booking->installments;
        $paidInstallments = $installments->filter(fn($i) => $i->isPaid())->values();
        $overdueInstallments = $installments->filter(fn($i) => $i->isOverdue())->values();
        $upcomingInstallments = $installments->filter(fn($i) => $i->isUpcoming())->values();

        return view('client.bookings.show', [
            'booking' => $booking,
            'totalPaid' => $booking->totalPaid(),
            'balance' => $booking->balance(),
            'paidInstallments' => $paidInstallments,
            'overdueInstallments' => $overdueInstallments,
            'upcomingInstallments' => $upcomingInstallments,
            'deeds' => $booking->documents->where('type', Document::TYPE_DEED),
            'receipts' => $booking->documents->where('type', Document::TYPE_RECEIPT),
            'otherDocs' => $booking->documents->where('type', Document::TYPE_OTHER),
        ]);
    }

    public function downloadDocument(Request $request, Booking $booking, Document $document): StreamedResponse
    {
        abort_unless($booking->client_id === $request->user()->id, 403);
        abort_unless($document->booking_id === $booking->id, 404);
        abort_unless((bool) $document->file_path, 404);

        return Storage::disk('local')->download($document->file_path, $document->original_filename ?: $document->title);
    }

    public function viewReceipt(Request $request, Booking $booking, Document $document): View
    {
        abort_unless($booking->client_id === $request->user()->id, 403);
        abort_unless($document->booking_id === $booking->id, 404);

        return view('client.bookings.receipt', [
            'booking' => $booking,
            'document' => $document,
            'installment' => $document->installment,
        ]);
    }
}
