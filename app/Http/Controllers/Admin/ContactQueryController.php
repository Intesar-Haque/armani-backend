<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactQueryController extends Controller
{
    public function index(Request $request): View
    {
        $query = ContactQuery::query()->latest();

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }
        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        return view('admin.queries.index', [
            'queries' => $query->paginate(20)->withQueryString(),
            'filters' => [
                'status' => $request->string('status')->toString(),
                'search' => $request->string('search')->toString(),
            ],
        ]);
    }

    public function show(ContactQuery $query): View
    {
        return view('admin.queries.show', ['query' => $query]);
    }

    public function update(Request $request, ContactQuery $query): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', ContactQuery::STATUSES)],
        ]);

        $query->update($data);

        return redirect()
            ->route('admin.queries.show', $query)
            ->with('status', 'Query updated.');
    }

    public function destroy(ContactQuery $query): RedirectResponse
    {
        $query->delete();

        return redirect()
            ->route('admin.queries.index')
            ->with('status', 'Query deleted.');
    }
}
