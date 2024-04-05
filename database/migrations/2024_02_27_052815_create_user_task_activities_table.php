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
        Schema::create('user_task_activities', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->nullable();
            $table->string('user_uuid')->nullable();
            $table->string('task_uuid')->nullable();
            $table->tinyInteger('status')->comment('0=>pending, 1=>start, 2=>pause, 3=>complete')->default(0);
            $table->integer('task_progress_percentage')->default(0);
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_task_activities');
    }
};
