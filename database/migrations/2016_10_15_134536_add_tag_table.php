<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('tag', function ($table) {

            $table->increments('id');
            $table->string('tagname', 200);
            $table->timestamps();
        });
        
        Schema::create('musictag', function ($table) {

            $table->increments('id');
            $table->integer('musid');
            $table->integer('tagid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('tag');
        Schema::dropIfExists('musictag');
    }
}
