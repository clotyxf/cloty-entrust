<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntrustRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('entrust.roles_table', 'entrust_roles'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique('IDX_NAME')->comment('角色英文名称');
            $table->string('display_name')->nullable()->comment('角色中文名称');
            $table->string('description')->nullable()->comment('角色介绍');
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
        Schema::dropIfExists(config('entrust.roles_table', 'entrust_roles'));
    }
}
