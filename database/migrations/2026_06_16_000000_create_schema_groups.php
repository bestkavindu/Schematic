<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schema_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schema_project_id')->constrained()->cascadeOnDelete();
            $table->string('client_id');
            $table->string('name');
            $table->string('color')->default('blue');
            $table->integer('pos_x')->default(0);
            $table->integer('pos_y')->default(0);
            $table->integer('width')->default(360);
            $table->integer('height')->default(260);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['schema_project_id', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schema_groups');
    }
};
