<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneroVideoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('genero_video', function (Blueprint $table) {
            $table->uuid('genero_id')->index();
            $table->foreign('genero_id')->references('id')->on('generos');
            $table->uuid('video_id')->index();
            $table->foreign('video_id')->references('id')->on('videos');
            $table->unique(['genero_id', 'video_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('genero_video');
    }
}
