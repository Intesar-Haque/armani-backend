<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ContactQuery;
use App\Models\Enquiry;
use App\Models\Project;
use App\Models\SiteVisit;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'enquiriesTotal' => Enquiry::count(),
            'enquiriesNew' => Enquiry::where('status', 'new')->count(),
            'visitsTotal' => SiteVisit::count(),
            'visitsNew' => SiteVisit::where('status', 'new')->count(),
            'queriesTotal' => ContactQuery::count(),
            'queriesNew' => ContactQuery::where('status', 'new')->count(),
            'projectsTotal' => Project::count(),
            'bookingsTotal' => Booking::count(),
            'clientsTotal' => User::where('role', User::ROLE_CLIENT)->count(),
            'revenueTotal' => (float) \App\Models\Installment::sum('paid_amount'),
            'recentEnquiries' => Enquiry::latest()->limit(5)->get(),
            'recentVisits' => SiteVisit::latest()->limit(5)->get(),
            'recentBookings' => Booking::with(['project', 'client'])->latest()->limit(5)->get(),
        ]);
    }
}
