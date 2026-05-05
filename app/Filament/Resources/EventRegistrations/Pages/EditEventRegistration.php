<?php

namespace App\Filament\Resources\EventRegistrations\Pages;

use App\Filament\Resources\EventRegistrations\EventRegistrationResource;
use Filament\Resources\Pages\EditRecord;

class EditEventRegistration extends EditRecord
{
    protected static string $resource = EventRegistrationResource::class;
}
