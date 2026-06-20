<?php

namespace App\Filament\Resources\CampusSettings\Pages;

use App\Filament\Resources\CampusSettings\CampusSettingResource;
use Filament\Resources\Pages\EditRecord;

class EditCampusSetting extends EditRecord
{
    protected static string $resource = CampusSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
