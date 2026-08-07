<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApartmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Apartment::query()
            ->with(['project', 'bookings' => fn ($q) => $q->whereIn('status', ['active', 'completed'])->with('client')])
            ->orderBy('floor_number')
            ->orderBy('apartment_number');

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }
        if ($projectId = $request->integer('project_id')) {
            $query->where('project_id', $projectId);
        }
        if ($search = $request->string('search')->toString()) {
            $query->where('apartment_number', 'like', "%{$search}%");
        }

        return view('admin.apartments.index', [
            'apartments' => $query->paginate(30)->withQueryString(),
            'projects' => Project::orderBy('name')->get(),
            'filters' => [
                'status' => $request->string('status')->toString(),
                'project_id' => $request->integer('project_id'),
                'search' => $request->string('search')->toString(),
            ],
            'counts' => [
                'available' => Apartment::where('status', Apartment::STATUS_AVAILABLE)->count(),
                'booked' => Apartment::where('status', Apartment::STATUS_BOOKED)->count(),
                'sold' => Apartment::where('status', Apartment::STATUS_SOLD)->count(),
            ],
        ]);
    }

    public function update(Request $request, Apartment $apartment): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', Apartment::STATUSES)],
        ]);

        $apartment->update($data);

        return back()->with('status', "Apartment {$apartment->apartment_number} updated.");
    }
}
