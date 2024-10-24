<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_technology', function (Blueprint $table) {
            $table->integer('years_experience')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('project_technology', function (Blueprint $table) {
            $table->integer('years_experience')->nullable(false)->change();
        });
    }
};
