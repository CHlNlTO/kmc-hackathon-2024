<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_technology', function (Blueprint $table) {
            $table->integer('years_experience')->after('technology_id')->unsigned();
        });
    }

    public function down(): void
    {
        Schema::table('project_technology', function (Blueprint $table) {
            $table->dropColumn('years_experience');
        });
    }
};
