<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSysadminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sysadmins', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('surname');
            $table->string('forenames');
            $table->string('email')->unique();
            $table->boolean('is_staff')->default(true);
            $table->boolean('is_sysadmin')->default(false);
            $table->string('password');
            $table->rememberToken();
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
        Schema::dropIfExists('sysadmins');
    }
}
