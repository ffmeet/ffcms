<?php

namespace App\Filament\Concerns;

trait UsesSettingsShell
{
    public function getLayout(): string
    {
        return 'filament.components.layout.settings-shell';
    }
}
