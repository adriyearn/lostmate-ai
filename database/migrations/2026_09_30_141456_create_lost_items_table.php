<?php

use App\Enums\ItemStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lost_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('item_name', 150);
            $table->string('color', 50)->nullable();
            $table->string('brand', 100)->nullable();
            $table->text('description');
            $table->string('location_lost');
            $table->date('date_lost');
            $table->time('time_lost')->nullable();
            $table->enum('status', array_column(ItemStatus::cases(), 'value'))->default(ItemStatus::Open->value);
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'status']);
            $table->index('date_lost');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lost_items');
    }
};
