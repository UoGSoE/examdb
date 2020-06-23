<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaperChecklistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paper_checklists', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('version')->default(1);
            $table->unsignedInteger('course_id');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->string('category');
            $table->string('year');
            $table->string('scqf_level');
            $table->unsignedInteger('course_credits');
            $table->text('course_coordinator_setting_comments')->nullable();
            $table->boolean('moderator_agree_marks_appropriate')->default(true);
            $table->text('moderator_inappropriate_comments')->nullable();
            $table->boolean('moderator_marks_adjusted')->default(false);
            $table->text('moderator_reason_marks_adjusted')->nullable();
            $table->text('moderator_further_comments')->nullable();
            $table->dateTime('moderator_approved_at')->nullable();
            $table->text('course_coordinator_moderating_comments')->nullable();
            $table->boolean('external_agree_with_moderator')->default(true);
            $table->text('external_rational')->nullable();
            $table->text('external_futher_comments')->nullable();
            $table->dateTime('external_completed_at')->nullable();
            $table->dateTime('archived_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('paper_checklists');
    }
}
