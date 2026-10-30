<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('number_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 10);
            $table->string('year_month', 6);
            $table->integer('last_number')->default(0);
            $table->timestamps();

            $table->unique(['prefix', 'year_month']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('number_sequences');
    }
};
