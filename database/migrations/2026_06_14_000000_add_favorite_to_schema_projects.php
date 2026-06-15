<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schema_projects', function (Blueprint $table) {
            $table->boolean('favorite')->default(false)->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('schema_projects', function (Blueprint $table) {
            $table->dropColumn('favorite');
        });
    }
};
