<?php

namespace App\Console\Commands;

use App\Services\Api\ReservationLifecycleService;
use Illuminate\Console\Command;

class ProcessReservationNoShows extends Command
{
    protected $signature = 'reservations:process-no-shows';

    protected $description = 'Mark reservations past their arrival deadline as no-shows';

    public function handle(ReservationLifecycleService $service): int
    {
        $count = $service->processNoShows();
        $this->info("Processed {$count} no-show reservation(s).");

        return self::SUCCESS;
    }
}
