<?php

namespace App\Filament\Resources\CitizenCharterResource\Pages;

use App\Filament\Resources\CitizenCharterResource;
use Filament\Resources\Pages\EditRecord;

class EditCitizenCharter extends EditRecord
{
    protected static string $resource = CitizenCharterResource::class;

    /**
     * No delete action: a procedure is retired by clearing "Active" on the form
     * below. Documents reference it by id with no foreign key behind them, so
     * deleting one still in use would orphan every document classified under it.
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
