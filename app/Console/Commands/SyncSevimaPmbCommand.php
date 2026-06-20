<?php

namespace App\Console\Commands;

use App\Services\SevimaPmbSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SyncSevimaPmbCommand extends Command
{
    protected $signature = 'sevima:sync-pmb {--no-details : Lewati invoice, pilihan prodi, dan nilai seleksi per pendaftar}';

    protected $description = 'Sinkronkan data PMB SEVIMA ke database lokal.';

    public function handle(SevimaPmbSyncService $syncService): int
    {
        try {
            $this->ensureLocalTablesExist();

            $counts = $syncService->sync(! $this->option('no-details'));
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Sinkronisasi Data PMB SEVIMA selesai.');

        foreach ($counts as $entityType => $count) {
            $this->line("- {$entityType}: {$count} data");
        }

        return self::SUCCESS;
    }

    private function ensureLocalTablesExist(): void
    {
        $requiredTables = [
            'pmb_sevima_records',
            'pmb_periods',
            'pmb_applicants',
            'pmb_study_programs',
            'pmb_registration_paths',
        ];

        foreach ($requiredTables as $table) {
            if (! Schema::hasTable($table)) {
                $this->warn('Tabel PMB lokal belum lengkap. Menjalankan migration dulu...');
                $this->call('migrate', ['--force' => true]);

                return;
            }
        }
    }
}
