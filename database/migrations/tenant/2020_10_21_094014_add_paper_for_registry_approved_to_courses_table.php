<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaperForRegistryApprovedToCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->boolean('registry_approved_main')->default(false);
            $table->boolean('registry_approved_resit')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('registry_approved_main');
            $table->dropColumn('registry_approved_resit');
        });
    }
}
