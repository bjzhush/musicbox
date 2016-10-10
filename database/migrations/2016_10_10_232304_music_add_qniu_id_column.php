<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MusicAddQniuIdColumn extends Migration
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
            $table->string('qiniu_id', 200)->after('uploadcomment');
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
            $table->dropColumn('qiniu_id');
        });
    }
}
