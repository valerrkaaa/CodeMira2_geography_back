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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_name_id');
            $table->unsignedBigInteger('pupil_id');
            $table->timestamps();

            $table->foreign('class_name_id')->references('id')->on('class_names');
            $table->foreign('pupil_id')->references('id')->on('users');

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
