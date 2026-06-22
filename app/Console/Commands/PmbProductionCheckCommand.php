<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PmbProductionCheckCommand extends Command
{
    protected $signature = 'pmb:production-check';

    protected $description = 'Check minimum PMB production readiness.';

    public function handle(): int
    {
        $checks = [
            'Database connection' => fn (): bool => (bool) DB::select('select 1'),
            'Institution configured' => fn (): bool => DB::table('institutions')->where('is_active', true)->exists(),
            'Campus locations configured' => fn (): bool => DB::table('campuses')->where('is_active', true)->exists(),
            'Study programs configured' => fn (): bool => DB::table('study_programs')->where('is_active', true)->exists(),
            'Registration options configured' => fn (): bool => DB::table('pmb_registration_options')->where('is_active', true)->exists(),
            'Tuition fees configured' => fn (): bool => DB::table('tuition_fee_schemes')->where('is_active', true)->exists(),
            'Storage disk writable' => fn (): bool => Storage::disk('public')->put('healthcheck.txt', now()->toISOString()),
        ];

        $failed = false;

        foreach ($checks as $label => $check) {
            try {
                $ok = $check();
            } catch (\Throwable) {
                $ok = false;
            }

            $this->line(($ok ? '<info>OK</info> ' : '<error>FAIL</error> ').$label);
            $failed = $failed || ! $ok;
        }

        Storage::disk('public')->delete('healthcheck.txt');

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
