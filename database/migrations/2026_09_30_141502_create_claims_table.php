<?php

use App\Enums\ClaimStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('found_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('claimant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('lost_item_id')->nullable()->constrained('lost_items')->nullOnDelete();
            $table->foreignId('ai_match_id')->nullable()->constrained('ai_matches')->nullOnDelete();
            $table->text('identifying_details');
            $table->string('proof_image_path')->nullable();
            $table->enum('status', array_column(ClaimStatus::cases(), 'value'))->default(ClaimStatus::Pending->value);
            $table->text('finder_response')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
