<?php

use App\Enums\AiMatchStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lost_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('found_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->text('reason')->nullable();
            $table->enum('status', array_column(AiMatchStatus::cases(), 'value'))->default(AiMatchStatus::Suggested->value);
            $table->foreignId('actioned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('model_used', 100)->nullable();
            $table->timestamps();

            $table->unique(['lost_item_id', 'found_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_matches');
    }
};
