<?php

use App\Enums\ItemStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('found_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('item_name', 150);
            $table->string('color', 50)->nullable();
            $table->string('brand', 100)->nullable();
            $table->text('description');
            $table->text('hidden_details')->nullable();
            $table->string('location_found');
            $table->date('date_found');
            $table->time('time_found')->nullable();
            $table->string('current_location')->nullable();
            $table->enum('status', array_column(ItemStatus::cases(), 'value'))->default(ItemStatus::Open->value);
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'status']);
            $table->index('date_found');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('found_items');
    }
};
