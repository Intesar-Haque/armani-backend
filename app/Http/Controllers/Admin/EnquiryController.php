<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Enquiry::query()->latest();

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }
        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('project_name', 'like', "%{$search}%");
            });
        }

        return view('admin.enquiries.index', [
            'enquiries' => $query->paginate(20)->withQueryString(),
            'filters' => [
                'status' => $request->string('status')->toString(),
                'search' => $request->string('search')->toString(),
            ],
        ]);
    }

    public function show(Enquiry $enquiry): View
    {
        return view('admin.enquiries.show', ['enquiry' => $enquiry]);
    }

    public function update(Request $request, Enquiry $enquiry): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', Enquiry::STATUSES)],
        ]);

        $enquiry->update($data);

        return redirect()
            ->route('admin.enquiries.show', $enquiry)
            ->with('status', 'Enquiry updated.');
    }

    public function destroy(Enquiry $enquiry): RedirectResponse
    {
        $enquiry->delete();

        return redirect()
            ->route('admin.enquiries.index')
            ->with('status', 'Enquiry deleted.');
    }
}
