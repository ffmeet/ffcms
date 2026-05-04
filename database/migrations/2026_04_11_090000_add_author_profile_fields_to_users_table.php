<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('display_name')->nullable()->after('username');
            $table->string('headline')->nullable()->after('avatar_small_path');
            $table->text('bio')->nullable()->after('headline');
            $table->json('social_links')->nullable()->after('bio');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'display_name',
                'headline',
                'bio',
                'social_links',
            ]);
        });
    }
};
