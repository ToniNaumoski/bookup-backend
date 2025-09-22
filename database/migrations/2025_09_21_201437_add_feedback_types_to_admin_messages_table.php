<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the enum to include 'note' and 'suggestion'
        DB::statement("ALTER TABLE admin_messages MODIFY COLUMN type ENUM('info', 'warning', 'error', 'note', 'suggestion') NOT NULL DEFAULT 'info'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE admin_messages MODIFY COLUMN type ENUM('info', 'warning', 'error') NOT NULL DEFAULT 'info'");
    }
};
