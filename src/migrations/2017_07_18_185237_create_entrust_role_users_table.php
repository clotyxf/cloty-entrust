<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntrustRoleUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('entrust.role_user_table', 'entrust_role_users'), function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->comment('用户ID');
            $table->integer('role_id')->unsigned()->comment('角色ID');

            $table->foreign('user_id')->references(config('entrust.user_key_name', 'id'))->on(config('entrust.users_table', 'users'))
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on(config('entrust.roles_table', 'entrust_roles'))
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['user_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('entrust.role_user_table', 'entrust_role_users'));
    }
}
