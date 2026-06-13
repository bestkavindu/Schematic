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
        Schema::create('schema_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('schema_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schema_project_id')->constrained()->cascadeOnDelete();
            $table->string('client_id');
            $table->string('name');
            $table->string('color')->default('blue');
            $table->integer('pos_x')->default(0);
            $table->integer('pos_y')->default(0);
            $table->json('indexes')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['schema_project_id', 'client_id']);
        });

        Schema::create('schema_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schema_table_id')->constrained()->cascadeOnDelete();
            $table->string('client_id');
            $table->string('name');
            $table->string('type')->default('string');
            $table->boolean('is_nullable')->default(false);
            $table->boolean('is_pk')->default(false);
            $table->boolean('is_unique')->default(false);
            $table->boolean('is_index')->default(false);
            $table->string('default_value')->nullable();
            // Foreign-key target, stored by the table's client_id so it survives a wholesale resync.
            $table->string('fk_table')->nullable();
            $table->string('fk_column')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schema_columns');
        Schema::dropIfExists('schema_tables');
        Schema::dropIfExists('schema_projects');
    }
};
