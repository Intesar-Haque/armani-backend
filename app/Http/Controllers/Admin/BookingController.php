<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BookingCreatedMail;
use App\Mail\InstallmentPaidMail;
use App\Models\Apartment;
use App\Models\Booking;
use App\Models\Document;
use App\Models\Installment;
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
            'apartments' => Apartment::available()->with('project')->orderBy('floor_number')->orderBy('apartment_number')->get(),
            'clients' => User::where('role', User::ROLE_CLIENT)->orderBy('name')->get(),
            'plans' => Booking::PLANS,
            'bookingMoney' => Booking::BOOKING_MONEY,
            'fullPaymentDiscount' => Booking::FULL_PAYMENT_DISCOUNT_PERCENT,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $planOptions = implode(',', array_merge([Booking::PAYMENT_TYPE_FULL], array_keys(Booking::PLANS)));

        $data = $request->validate([
            'apartment_id' => ['required', 'exists:apartments,id'],
            'client_id' => ['nullable', 'exists:users,id'],
            'client_name' => ['required_without:client_id', 'nullable', 'string', 'max:150'],
            'client_email' => ['required', 'email', 'max:150'],
            'client_phone' => ['nullable', 'string', 'max:30'],
            'booking_amount' => ['required', 'numeric', 'min:' . Booking::BOOKING_MONEY],
            'down_payment' => ['nullable', 'numeric', 'min:0'],
            'payment_plan' => ['required', 'in:' . $planOptions],
            'booking_date' => ['required', 'date'],
            'first_installment_date' => ['required', 'date'],
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
        $booking->load(['project', 'apartment', 'client', 'installments.receipts', 'documents']);

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

        if ($data['status'] === 'cancelled' && $booking->status !== 'cancelled') {
            $booking->update(['notes' => $data['notes'] ?? $booking->notes]);
            $this->bookings->cancelBooking($booking);
        } else {
            $booking->update($data);
        }

        return back()->with('status', 'Booking updated.');
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        if ($booking->status !== 'cancelled') {
            $booking->apartment?->update(['status' => Apartment::STATUS_AVAILABLE]);
        }
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
