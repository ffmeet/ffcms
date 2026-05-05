<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->string('favicon_path')->nullable()->after('admin_logo_path');
            $table->string('apple_touch_icon_path')->nullable()->after('favicon_path');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'favicon_path',
                'apple_touch_icon_path',
            ]);
        });
    }
};
