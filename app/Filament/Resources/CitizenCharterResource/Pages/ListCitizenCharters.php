<?php

namespace App\Filament\Resources\CitizenCharterResource\Pages;

use App\Filament\Resources\CitizenCharterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCitizenCharters extends ListRecords
{
    protected static string $resource = CitizenCharterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
