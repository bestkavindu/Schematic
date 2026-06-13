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
            // Relationship cardinality + referential actions for the FK on this column.
            $table->string('fk_type')->nullable()->after('fk_column');       // '1:N' | '1:1'
            $table->string('fk_on_delete')->nullable()->after('fk_type');    // cascade|restrict|set null|no action
            $table->string('fk_on_update')->nullable()->after('fk_on_delete');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schema_columns', function (Blueprint $table) {
            $table->dropColumn(['fk_type', 'fk_on_delete', 'fk_on_update']);
        });
    }
};
