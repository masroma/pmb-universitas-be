<?php

namespace App\Filament\Resources\CampusSettings;

use App\Filament\Resources\CampusSettings\Pages\EditCampusSetting;
use App\Filament\Resources\CampusSettings\Pages\ListCampusSettings;
use App\Filament\Resources\CampusSettings\Schemas\CampusSettingForm;
use App\Filament\Resources\CampusSettings\Tables\CampusSettingsTable;
use App\Models\CampusSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CampusSettingResource extends Resource
{
    protected static ?string $model = CampusSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Pengaturan Kampus';

    protected static ?string $modelLabel = 'Pengaturan Kampus';

    protected static ?string $pluralModelLabel = 'Pengaturan Kampus';

    public static function form(Schema $schema): Schema
    {
        return CampusSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CampusSettingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCampusSettings::route('/'),
            'edit' => EditCampusSetting::route('/{record}/edit'),
        ];
    }
}
