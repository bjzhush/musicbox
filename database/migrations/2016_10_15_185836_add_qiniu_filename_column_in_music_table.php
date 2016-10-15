<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQiniuFilenameColumnInMusicTable extends Migration
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
            $table->string('qiniu_filename', 300)->after('qiniu_id');
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
            $table->dropColumn('qiniu_filename');
        });
    }
}
