<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ClientAccountService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct(private ClientAccountService $clients) {}

    public function index(Request $request): View
    {
        $query = User::where('role', User::ROLE_CLIENT)->withCount('bookings')->latest();

        if ($search = $request->string('q')->trim()->value()) {
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('nid', 'like', "%{$search}%"));
        }

        return view('admin.clients.index', [
            'clients' => $query->paginate(30)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_name' => ['required', 'string', 'max:150'],
            'client_email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'client_phone' => ['nullable', 'string', 'max:30'],
            'nid' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'father_name' => ['nullable', 'string', 'max:150'],
            'mother_name' => ['nullable', 'string', 'max:150'],
        ]);

        [$client, $password] = $this->clients->findOrCreate($data);

        return redirect()->route('admin.clients.index')
            ->with('status', "Client {$client->email} registered. Temporary password: {$password}");
    }
}
