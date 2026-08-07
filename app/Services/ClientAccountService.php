<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientAccountService
{
    /**
     * Find a client by email, or create a new client user.
     * Returns [User $client, ?string $plainPassword]. $plainPassword is
     * non-null when a new client user was created.
     *
     * Expected input: client_email, client_name, client_phone (optional)
     */
    public function findOrCreate(array $input): array
    {
        $generatedPassword = null;

        $client = User::where('email', $input['client_email'])->first();
        if (! $client) {
            $generatedPassword = Str::random(10);
            $client = User::create([
                'name' => $input['client_name'],
                'email' => $input['client_email'],
                'phone' => $input['client_phone'] ?? null,
                'password' => Hash::make($generatedPassword),
                'role' => User::ROLE_CLIENT,
                'must_change_password' => true,
            ]);
        } elseif ($client->isAdmin()) {
            throw new \RuntimeException('An admin user already exists with that email.');
        }

        return [$client, $generatedPassword];
    }
}
