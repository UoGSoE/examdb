<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::raw('UPDATE comments SET commentable_type = "App\Models\Paper" WHERE commentable_type = "App\Paper"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::raw('UPDATE comments SET commentable_type = "App\Paper" WHERE commentable_type = "App\Models\Paper"');
    }
};
