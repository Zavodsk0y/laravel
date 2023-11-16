<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFileIdTypeInFileAccesses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('file_accesses', function (Blueprint $table) {
            $table->string('file_id')->change();

            $table->foreign('file_id')
                ->references('file_id')
                ->on('files')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('file_accesses', function (Blueprint $table) {
            $table->string('file_id')->change();
            $table->dropForeign(['file_id']);
        });
    }
}
