<?php

namespace App\Console\Commands;

use App\Services\PmbRegistrationCascadeSyncService;
use Illuminate\Console\Command;

class SyncPmbCascadeCommand extends Command
{
    protected $signature = 'pmb:sync-cascade';

    protected $description = 'Sinkronkan data cascade pendaftaran dari pmb_sevima_records ke tabel lokal';

    public function handle(PmbRegistrationCascadeSyncService $syncService): int
    {
        $counts = $syncService->syncFromSevimaRecords();

        $this->info('Cascade sync selesai.');
        $this->line('Periode pendaftaran: '.$counts['periods']);
        $this->line('Program studi dibuka: '.$counts['open_programs']);

        return self::SUCCESS;
    }
}
