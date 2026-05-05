<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteThemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_frontend_can_switch_to_editorial_theme_layout(): void
    {
        $settings = SiteSetting::current();
        $businessSettings = $settings->business_settings ?? [];
        $businessSettings['active_theme'] = 'editorial';

        $settings->update([
            'business_settings' => $businessSettings,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Editorial Theme');
    }
}
