<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryGeneroTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_genero', function (Blueprint $table) {
            $table->uuid('category_id')->index();
            $table->foreign('category_id')->references('id')->on('categories');
            $table->uuid('genero_id')->index();
            $table->foreign('genero_id')->references('id')->on('generos');
            $table->unique(['category_id', 'genero_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_genero');
    }
}
