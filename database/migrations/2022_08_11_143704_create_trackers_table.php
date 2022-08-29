<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trackers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('imei')->unique();
            $table->string('phone')->unique();
            $table->double('balance');
            $table->double('power');
            $table->boolean('is_charging');

            $table->unsignedBigInteger('car_id')->nullable();
            $table->unsignedBigInteger('person_id')->nullable();
            $table->unsignedBigInteger('responsible_id');

            $table->foreign('car_id')->references('id')->on('cars');
            $table->foreign('person_id')->references('id')->on('people');
            $table->foreign('responsible_id')->references('id')->on('people');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trackers');
    }
};
