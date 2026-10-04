<?php

namespace App\Console\Commands;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Console\Command;

class SuspendInactiveUsers extends Command
{
    protected $signature = 'users:suspend-inactive';

    protected $description = 'Suspend users who have not logged in for six months';

    public function handle(): int
    {
        $cutoff = now()->subMonths(6);

        $suspendedCount = User::query()
            ->where('status', UserStatus::ACTIVE->value)
            ->where(function ($query) use ($cutoff) {
                $query
                    ->where('last_login_at', '<', $cutoff)
                    ->orWhere(function ($query) use ($cutoff) {
                        $query
                            ->whereNull('last_login_at')
                            ->where('created_at', '<', $cutoff);
                    });
            })
            ->update(['status' => UserStatus::SUSPENDED->value]);

        $this->info("Suspended {$suspendedCount} inactive user(s).");

        return self::SUCCESS;
    }
}
