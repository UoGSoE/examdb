<?php

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::raw('UPDATE comments SET commentable_type = "App\Models\Paper" WHERE commentable_type = "App\Paper"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::raw('UPDATE comments SET commentable_type = "App\Paper" WHERE commentable_type = "App\Models\Paper"');
    }
};
