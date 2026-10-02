<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Email verification was switched on after people had already registered.
 * This one-time data fix marks those existing accounts as verified so
 * nobody gets locked out. New accounts must verify their email as usual.
 * (No columns are added or changed.)
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        // Nothing to undo: we can't tell which users were verified by this fix.
    }
};
