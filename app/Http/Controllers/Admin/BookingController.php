<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BookingCreatedMail;
use App\Mail\InstallmentPaidMail;
use App\Models\Booking;
use App\Models\Document;
use App\Models\Installment;
use App\Models\Project;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookings) {}

    public function index(Request $request): View
    {
        $query = Booking::query()->with(['project', 'client'])->latest();

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }
        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('unit_number', 'like', "%{$search}%")
                    ->orWhereHas('client', fn($c) => $c->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    ->orWhereHas('project', fn($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        return view('admin.bookings.index', [
            'bookings' => $query->paginate(20)->withQueryString(),
            'filters' => [
                'status' => $request->string('status')->toString(),
                'search' => $request->string('search')->toString(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.bookings.create', [
            'projects' => Project::orderBy('name')->get(),
            'clients' => User::where('role', User::ROLE_CLIENT)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'client_id' => ['nullable', 'exists:users,id'],
            'client_name' => ['required_without:client_id', 'nullable', 'string', 'max:150'],
            'client_email' => ['required', 'email', 'max:150'],
            'client_phone' => ['nullable', 'string', 'max:30'],
            'unit_number' => ['nullable', 'string', 'max:60'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'booking_amount' => ['required', 'numeric', 'min:0', 'lte:total_amount'],
            'booking_date' => ['required', 'date'],
            'installment_count' => ['required', 'integer', 'min:0', 'max:120'],
            'first_installment_date' => ['required_if:installment_count,>,0', 'nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        if (! empty($data['client_id'])) {
            $existing = User::findOrFail($data['client_id']);
            $data['client_email'] = $existing->email;
            $data['client_name'] = $existing->name;
            $data['client_phone'] = $existing->phone;
        }

        [$booking, $plainPassword] = $this->bookings->createBooking($data);

        Mail::to($booking->client->email)->send(new BookingCreatedMail($booking, $plainPassword));

        return redirect()->route('admin.bookings.show', $booking)->with('status', 'Booking created and receipt emailed.');
    }

    public function show(Booking $booking): View
    {
        $booking->load(['project', 'client', 'installments.receipts', 'documents']);

        return view('admin.bookings.show', [
            'booking' => $booking,
            'totalPaid' => $booking->totalPaid(),
            'balance' => $booking->balance(),
        ]);
    }

    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', Booking::STATUSES)],
            'notes' => ['nullable', 'string'],
        ]);
        $booking->update($data);

        return back()->with('status', 'Booking updated.');
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        $booking->delete();
        return redirect()->route('admin.bookings.index')->with('status', 'Booking removed.');
    }

    public function payInstallment(Request $request, Booking $booking, Installment $installment): RedirectResponse
    {
        abort_if($installment->booking_id !== $booking->id, 404);

        $data = $request->validate([
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'paid_at' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'string', 'max:60'],
            'payment_reference' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->bookings->markInstallmentPaid($installment, $data);
        Mail::to($booking->client->email)->send(new InstallmentPaidMail($installment->fresh(['booking.project', 'booking.client'])));

        return back()->with('status', 'Installment marked paid and receipt emailed.');
    }

    public function uploadDocument(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:' . implode(',', Document::TYPES)],
            'title' => ['required', 'string', 'max:150'],
            'file' => ['required', 'file', 'max:15360'],
            'issued_at' => ['nullable', 'date'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $path = $request->file('file')->store("bookings/{$booking->id}/documents", 'local');

        Document::create([
            'booking_id' => $booking->id,
            'type' => $data['type'],
            'title' => $data['title'],
            'file_path' => $path,
            'original_filename' => $request->file('file')->getClientOriginalName(),
            'amount' => $data['amount'] ?? null,
            'issued_at' => $data['issued_at'] ?? now()->toDateString(),
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('status', 'Document uploaded.');
    }

    public function destroyDocument(Booking $booking, Document $document): RedirectResponse
    {
        abort_if($document->booking_id !== $booking->id, 404);
        if ($document->file_path) {
            Storage::disk('local')->delete($document->file_path);
        }
        $document->delete();

        return back()->with('status', 'Document removed.');
    }

    public function downloadDocument(Booking $booking, Document $document)
    {
        abort_if($document->booking_id !== $booking->id, 404);
        abort_unless((bool) $document->file_path, 404);

        return Storage::disk('local')->download($document->file_path, $document->original_filename ?: $document->title);
    }
}
