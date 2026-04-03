<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('comment_reactions')) {
            return;
        }

        Schema::create('comment_reactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('comment_id')->constrained('comments')->cascadeOnDelete();
            $table->unsignedBigInteger('reactor_id');
            $table->string('reactor_type');
            $table->string('reaction', 50);
            $table->timestamps();

            $table->unique(['comment_id', 'reactor_id', 'reactor_type', 'reaction'], 'comment_reactor_reaction_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_reactions');
    }
};
