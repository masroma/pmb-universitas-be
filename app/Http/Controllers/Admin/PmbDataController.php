<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampusSetting;
use App\Models\PmbSevimaRecord;
use App\Services\SevimaPmbSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PmbDataController extends Controller
{
    public function index(Request $request): View
    {
        $entityType = $request->string('entity_type')->toString();

        $records = PmbSevimaRecord::query()
            ->when($entityType !== '', fn ($query) => $query->where('entity_type', $entityType))
            ->latest('synced_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.pmb-data.index', [
            'campusSetting' => $this->campusSetting(),
            'entityType' => $entityType,
            'entityTypes' => PmbSevimaRecord::query()
                ->select('entity_type')
                ->distinct()
                ->orderBy('entity_type')
                ->pluck('entity_type'),
            'records' => $records,
            'summary' => PmbSevimaRecord::query()
                ->selectRaw('entity_type, count(*) as total')
                ->groupBy('entity_type')
                ->orderBy('entity_type')
                ->pluck('total', 'entity_type'),
        ]);
    }

    public function sync(Request $request, SevimaPmbSyncService $syncService): RedirectResponse
    {
        try {
            $counts = $syncService->sync(! $request->boolean('no_details'));
        } catch (Throwable $exception) {
            return redirect()
                ->route('admin.pmb-data.index')
                ->withErrors(['sync' => $exception->getMessage()]);
        }

        $total = array_sum($counts);

        return redirect()
            ->route('admin.pmb-data.index')
            ->with('status', "Sync Data PMB selesai. {$total} data diproses.");
    }

    private function campusSetting(): CampusSetting
    {
        return CampusSetting::query()->first() ?? CampusSetting::query()->create([
            'campus_name' => 'Universitas',
        ]);
    }
}
