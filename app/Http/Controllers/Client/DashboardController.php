<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $client = $request->user();

        $bookings = Booking::with('project')
            ->where('client_id', $client->id)
            ->withSum('installments as total_paid', 'paid_amount')
            ->latest('booking_date')
            ->get();

        $totalValue = $bookings->sum(fn($b) => (float) $b->total_amount);
        $totalPaid = $bookings->sum(fn($b) => (float) ($b->total_paid ?? 0));

        return view('client.dashboard', [
            'bookings' => $bookings,
            'totalValue' => $totalValue,
            'totalPaid' => $totalPaid,
            'totalBalance' => $totalValue - $totalPaid,
        ]);
    }
}
