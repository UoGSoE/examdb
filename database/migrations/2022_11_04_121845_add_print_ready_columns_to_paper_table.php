<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('papers', function (Blueprint $table) {
            $table->char('print_ready_approved')->nullable()->default(null);
            $table->string('print_ready_comment')->nullable();
            $table->dateTime('print_ready_reminder_sent')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('papers', function (Blueprint $table) {
            $table->dropColumn('print_ready_approved');
            $table->dropColumn('print_ready_comment');
            $table->dropColumn('print_ready_reminder_sent');
        });
    }
};
