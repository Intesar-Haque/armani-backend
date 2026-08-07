<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Landowner;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LandownerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Landowner::query()->latest();

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }
        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('land_locality', 'like', "%{$search}%");
            });
        }

        return view('admin.landowners.index', [
            'landowners' => $query->paginate(20)->withQueryString(),
            'filters' => [
                'status' => $request->string('status')->toString(),
                'search' => $request->string('search')->toString(),
            ],
        ]);
    }

    public function show(Landowner $landowner): View
    {
        return view('admin.landowners.show', ['landowner' => $landowner]);
    }

    public function update(Request $request, Landowner $landowner): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', Landowner::STATUSES)],
        ]);

        $landowner->update($data);

        return redirect()
            ->route('admin.landowners.show', $landowner)
            ->with('status', 'Landowner updated.');
    }

    public function destroy(Landowner $landowner): RedirectResponse
    {
        $landowner->delete();

        return redirect()
            ->route('admin.landowners.index')
            ->with('status', 'Landowner deleted.');
    }
}
