<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntrustPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * TODO:闭包表
         */
        Schema::create(config('entrust.permissions_table', 'entrust_permissions'), function (Blueprint $table) {
            $table->increments('id');
            $table->integer('p_id')->unsigned()->default(0)->comment('父级ID');
            $table->string('name')->unique('IDX_NAME')->comment('组件|功能英文名称');
            $table->string('display_name')->nullable()->comment('组件|功能中文名称');
            $table->string('description')->nullable()->comment('组件|功能介绍');
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
        Schema::dropIfExists(config('entrust.permissions_table', 'entrust_permissions'));
    }
}
