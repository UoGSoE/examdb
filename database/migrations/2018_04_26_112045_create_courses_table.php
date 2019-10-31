<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->unique();
            $table->string('title');
            $table->boolean('is_active')->default(true);
            $table->boolean('setter_approved_main')->default(false);
            $table->boolean('moderator_approved_main')->default(false);
            $table->boolean('external_approved_main')->default(false);
            $table->boolean('setter_approved_resit')->default(false);
            $table->boolean('moderator_approved_resit')->default(false);
            $table->boolean('external_approved_resit')->default(false);
            $table->boolean('external_notified')->default(false);
            $table->unsignedInteger('discipline_id')->nullable();
            $table->foreign('discipline_id')->references('id')->on('disciplines')->onDelete('set null');
            $table->softDeletes();
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
        Schema::dropIfExists('courses');
    }
}
