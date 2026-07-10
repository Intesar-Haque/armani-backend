<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteVisit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteVisitController extends Controller
{
    public function index(Request $request): View
    {
        $query = SiteVisit::query()->latest();

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

        return view('admin.site-visits.index', [
            'visits' => $query->paginate(20)->withQueryString(),
            'filters' => [
                'status' => $request->string('status')->toString(),
                'search' => $request->string('search')->toString(),
            ],
        ]);
    }

    public function show(SiteVisit $siteVisit): View
    {
        return view('admin.site-visits.show', ['visit' => $siteVisit]);
    }

    public function update(Request $request, SiteVisit $siteVisit): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', SiteVisit::STATUSES)],
        ]);

        $siteVisit->update($data);

        return redirect()
            ->route('admin.site-visits.show', $siteVisit)
            ->with('status', 'Site visit updated.');
    }

    public function destroy(SiteVisit $siteVisit): RedirectResponse
    {
        $siteVisit->delete();

        return redirect()
            ->route('admin.site-visits.index')
            ->with('status', 'Site visit deleted.');
    }
}
