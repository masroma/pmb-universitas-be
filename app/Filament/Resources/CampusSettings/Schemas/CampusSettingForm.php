<?php

namespace App\Filament\Resources\CampusSettings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CampusSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('campus_name')
                    ->label('Nama Kampus')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('logo_path')
                    ->label('Logo Kampus')
                    ->disk('public')
                    ->directory('campus')
                    ->image()
                    ->imageEditor(),
                FileUpload::make('hero_image_path')
                    ->label('Gambar Hero')
                    ->disk('public')
                    ->directory('campus/hero')
                    ->image()
                    ->imageEditor()
                    ->columnSpanFull(),
                Textarea::make('address')
                    ->label('Alamat')
                    ->rows(4)
                    ->columnSpanFull(),
                TextInput::make('website')
                    ->label('Website')
                    ->url()
                    ->maxLength(255),
                TextInput::make('facebook')
                    ->label('Facebook')
                    ->url()
                    ->maxLength(255),
                TextInput::make('instagram')
                    ->label('Instagram')
                    ->url()
                    ->maxLength(255),
                TextInput::make('twitter')
                    ->label('Twitter / X')
                    ->url()
                    ->maxLength(255),
                TextInput::make('linkedin')
                    ->label('LinkedIn')
                    ->url()
                    ->maxLength(255),
                TextInput::make('youtube')
                    ->label('YouTube')
                    ->url()
                    ->maxLength(255),
                TextInput::make('fax')
                    ->label('Fax')
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('Telepon')
                    ->tel()
                    ->maxLength(255),
            ]);
    }
}
