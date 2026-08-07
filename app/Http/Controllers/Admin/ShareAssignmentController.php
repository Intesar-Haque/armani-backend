<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ShareAssignment;
use App\Models\User;
use App\Services\ShareAssignmentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShareAssignmentController extends Controller
{
    public function __construct(private ShareAssignmentService $shareAssignments) {}

    public function index(Request $request): View
    {
        $query = ShareAssignment::query()->with(['project', 'client'])->latest();

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }
        if ($projectId = $request->integer('project_id')) {
            $query->where('project_id', $projectId);
        }
        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        return view('admin.share-assignments.index', [
            'assignments' => $query->paginate(20)->withQueryString(),
            'projects' => Project::orderBy('name')->get(),
            'filters' => [
                'status' => $request->string('status')->toString(),
                'project_id' => $request->integer('project_id'),
                'search' => $request->string('search')->toString(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.share-assignments.create', [
            'projects' => Project::whereNotNull('total_shares')->orderBy('name')->get(),
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
            'shares_count' => ['required', 'integer', 'min:1'],
            'assigned_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        if (! empty($data['client_id'])) {
            $existing = User::findOrFail($data['client_id']);
            $data['client_email'] = $existing->email;
            $data['client_name'] = $existing->name;
            $data['client_phone'] = $existing->phone;
        }

        [$assignment] = $this->shareAssignments->assignShares($data);

        return redirect()->route('admin.share-assignments.show', $assignment)->with('status', 'Shares assigned.');
    }

    public function show(ShareAssignment $shareAssignment): View
    {
        $shareAssignment->load(['project', 'client']);

        return view('admin.share-assignments.show', ['assignment' => $shareAssignment]);
    }

    public function update(Request $request, ShareAssignment $shareAssignment): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', ShareAssignment::STATUSES)],
            'notes' => ['nullable', 'string'],
        ]);

        $shareAssignment->update($data);

        return back()->with('status', 'Share assignment updated.');
    }

    public function destroy(ShareAssignment $shareAssignment): RedirectResponse
    {
        $shareAssignment->delete();

        return redirect()->route('admin.share-assignments.index')->with('status', 'Share assignment removed.');
    }
}
