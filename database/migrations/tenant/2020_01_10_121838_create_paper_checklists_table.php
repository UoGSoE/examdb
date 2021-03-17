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
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->string('category');
            $table->dateTime('archived_at')->nullable();
            $table->json('fields');
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
