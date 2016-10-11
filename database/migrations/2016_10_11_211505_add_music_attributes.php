<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMusicAttributes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('music', function ($table) {
            $table->integer('marked')->default(0)->after('uploadcomment');
            $table->integer('artistid')->after('uploadcomment');
            $table->string('musicname', 200)->after('uploadcomment');
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
        Schema::table('music', function ($table) {
            $table->dropColumn('marked');
            $table->dropColumn('artist');
            $table->dropColumn('musicname');
        });
    }
}
