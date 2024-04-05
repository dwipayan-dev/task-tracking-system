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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->nullable();
            $table->string('user_uuid')->nullable();
            $table->string('task_id')->nullable();
            $table->string('task_name')->nullable();
            $table->string('project_name')->nullable();
            $table->decimal('estimation_hrs', 8, 1)->nullable();
            $table->decimal('completion_hrs', 8, 1)->default(0);
            $table->enum('type', ['0', '1'])->comment('0=> Newly Created, 1=> Carry Forward')->default(0);
            $table->tinyInteger('status')->comment('0=>pending, 1=>start, 2=>pause, 3=>complete')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
