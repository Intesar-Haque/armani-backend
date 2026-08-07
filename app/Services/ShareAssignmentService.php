<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ShareAssignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShareAssignmentService
{
    public function __construct(private ClientAccountService $clients) {}

    /**
     * Assign shares of a project to a client. Returns [ShareAssignment, ?string $generatedPassword].
     *
     * Expected input:
     *  - project_id, shares_count, assigned_date
     *  - client_id OR client_name/client_email/client_phone
     *  - notes (optional)
     */
    public function assignShares(array $input): array
    {
        return DB::transaction(function () use ($input) {
            [$client, $generatedPassword] = $this->clients->findOrCreate($input);

            $project = Project::lockForUpdate()->findOrFail($input['project_id']);

            if ($project->total_shares === null || $project->share_value === null) {
                throw ValidationException::withMessages([
                    'project_id' => 'This project does not have shares configured.',
                ]);
            }

            $sharesCount = (int) $input['shares_count'];
            if ($sharesCount < 1) {
                throw ValidationException::withMessages([
                    'shares_count' => 'Shares count must be at least 1.',
                ]);
            }

            if ($sharesCount > $project->sharesRemaining()) {
                throw ValidationException::withMessages([
                    'shares_count' => "Only {$project->sharesRemaining()} share(s) remaining for {$project->name}.",
                ]);
            }

            $shareValue = (float) $project->share_value;
            $totalAmount = round($shareValue * $sharesCount, 2);

            $assignment = ShareAssignment::create([
                'project_id' => $project->id,
                'client_id' => $client->id,
                'shares_count' => $sharesCount,
                'share_value' => $shareValue,
                'total_amount' => $totalAmount,
                'assigned_date' => Carbon::parse($input['assigned_date'])->toDateString(),
                'status' => ShareAssignment::STATUS_ACTIVE,
                'notes' => $input['notes'] ?? null,
            ]);

            return [$assignment->fresh(['project', 'client']), $generatedPassword];
        });
    }
}
