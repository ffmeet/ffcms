<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Support\SiteIconManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteFaviconGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_logo_generates_favicon_assets(): void
    {
        Storage::fake('public');

        $settings = SiteSetting::current();
        $settings->update([
            'frontend_logo_path' => UploadedFile::fake()->image('logo.png', 240, 240)->store('branding/frontend', 'public'),
        ]);

        app(SiteIconManager::class)->regenerate($settings->fresh());

        $settings->refresh();

        $this->assertNotNull($settings->favicon_path);
        $this->assertNotNull($settings->apple_touch_icon_path);
        Storage::disk('public')->assertExists($settings->favicon_path);
        Storage::disk('public')->assertExists($settings->apple_touch_icon_path);
    }

    public function test_frontend_layout_includes_generated_favicon_links(): void
    {
        Storage::fake('public');

        $settings = SiteSetting::current();
        $settings->update([
            'frontend_logo_path' => UploadedFile::fake()->image('logo.png', 240, 240)->store('branding/frontend', 'public'),
        ]);

        app(SiteIconManager::class)->regenerate($settings->fresh());

        $response = $this->get(route('site.home'));

        $response->assertOk();
        $response->assertSee('rel="icon"', false);
        $response->assertSee('rel="apple-touch-icon"', false);
        $response->assertSee('/storage/branding/favicon/site-icon-32.png', false);
    }
}
