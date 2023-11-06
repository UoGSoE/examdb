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
        Schema::table('disciplines', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_session_id')->nullable();
            $table->foreign('academic_session_id')->references('id')->on('academic_sessions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('disciplines', function (Blueprint $table) {
            $table->dropColumn(['academic_session_id']);
        });
    }
};
