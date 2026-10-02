<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Office drop-off: a finder can turn a found item in at the school's
 * lost & found office. From then on, office staff (admins) handle the
 * claims and the handover instead of the student finder.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('found_items', function (Blueprint $table) {
            $table->timestamp('surrendered_at')->nullable()->after('current_location');
            $table->foreignId('surrendered_to')->nullable()->after('surrendered_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('found_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('surrendered_to');
            $table->dropColumn('surrendered_at');
        });
    }
};
