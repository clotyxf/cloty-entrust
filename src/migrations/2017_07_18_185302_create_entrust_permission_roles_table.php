<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntrustPermissionRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('entrust.permission_role_table', 'entrust_permission_roles'), function (Blueprint $table) {
            $table->integer('permission_id')->unsigned()->comment('组件|功能ID');
            $table->integer('role_id')->unsigned()->comment('角色ID');

            $table->foreign('permission_id')->references('id')->on(config('entrust.permissions_table', 'entrust_permissions'))
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on(config('entrust.roles_table', 'entrust_roles'))
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['permission_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('entrust.permission_role_table', 'entrust_permission_roles'));
    }
}
