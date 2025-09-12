<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->text('admin_remarks')->nullable()->after('status');
            $table->json('admin_messages')->nullable()->after('admin_remarks');
            $table->string('review_status')->default('pending')->after('admin_messages');
            $table->timestamp('last_reviewed_at')->nullable()->after('review_status');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('last_reviewed_at');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn([
                'admin_remarks',
                'admin_messages',
                'review_status',
                'last_reviewed_at',
                'reviewed_by'
            ]);
        });
    }
};
