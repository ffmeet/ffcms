<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('avatar_original_path')->nullable()->after('status');
            $table->string('avatar_large_path')->nullable()->after('avatar_original_path');
            $table->string('avatar_medium_path')->nullable()->after('avatar_large_path');
            $table->string('avatar_small_path')->nullable()->after('avatar_medium_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'avatar_original_path',
                'avatar_large_path',
                'avatar_medium_path',
                'avatar_small_path',
            ]);
        });
    }
};
