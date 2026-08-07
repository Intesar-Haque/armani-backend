<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectDirector;
use App\Models\User;
use App\Services\ClientAccountService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProjectDirectorController extends Controller
{
    public function __construct(private ClientAccountService $clients) {}

    public function index(Request $request): View
    {
        $query = ProjectDirector::query()->with(['project', 'client'])->latest();

        if ($projectId = $request->integer('project_id')) {
            $query->where('project_id', $projectId);
        }

        return view('admin.project-directors.index', [
            'directors' => $query->paginate(30)->withQueryString(),
            'projects' => Project::orderBy('name')->get(),
            'filters' => [
                'project_id' => $request->integer('project_id'),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.project-directors.create', [
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
            'designation' => ['nullable', 'string', 'max:150'],
            'appointed_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        if (! empty($data['client_id'])) {
            $existing = User::findOrFail($data['client_id']);
            $data['client_email'] = $existing->email;
            $data['client_name'] = $existing->name;
            $data['client_phone'] = $existing->phone;
        }

        [$client] = $this->clients->findOrCreate($data);

        if (ProjectDirector::where('project_id', $data['project_id'])->where('client_id', $client->id)->exists()) {
            throw ValidationException::withMessages([
                'client_id' => 'This client is already a director of the selected project.',
            ]);
        }

        ProjectDirector::create([
            'project_id' => $data['project_id'],
            'client_id' => $client->id,
            'designation' => $data['designation'] ?? null,
            'appointed_date' => $data['appointed_date'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('admin.project-directors.index')->with('status', 'Director assigned.');
    }

    public function update(Request $request, ProjectDirector $projectDirector): RedirectResponse
    {
        $data = $request->validate([
            'designation' => ['nullable', 'string', 'max:150'],
            'appointed_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $projectDirector->update($data);

        return back()->with('status', 'Director updated.');
    }

    public function destroy(ProjectDirector $projectDirector): RedirectResponse
    {
        $projectDirector->delete();

        return redirect()->route('admin.project-directors.index')->with('status', 'Director removed.');
    }
}
