<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schema_columns', function (Blueprint $table) {
            // Structured attributes of the neutral logical type system. All optional:
            // legacy rows leave them null/false and are normalized on read.
            $table->integer('size')->nullable()->after('type');            // varchar/char length
            $table->integer('precision')->nullable()->after('size');       // decimal precision
            $table->integer('scale')->nullable()->after('precision');      // decimal scale
            $table->boolean('unsigned')->default(false)->after('scale');
            $table->boolean('auto_increment')->default(false)->after('unsigned');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schema_columns', function (Blueprint $table) {
            $table->dropColumn(['size', 'precision', 'scale', 'unsigned', 'auto_increment']);
        });
    }
};
