<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSshConnectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ssh_connections', function (Blueprint $table) {
            $table->increments('id');
			$table->string('host');
			$table->string('user');
			$table->string('model_type')->nullable();
			$table->integer('model_id')->unsigned()->nullable();
			$table->integer('auth_method_id')->unsigned();
			$table->string('auth');
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
        Schema::dropIfExists('ssh_connections');
    }
}
