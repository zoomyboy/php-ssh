<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSshAuthMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ssh_auth_methods', function (Blueprint $table) {
            $table->increments('id');
			$table->string('title');
        });

        \Zoomyboy\PhpSsh\AuthMethod::create(['title' => 'KeyFile']);
        \Zoomyboy\PhpSsh\AuthMethod::create(['title' => 'Password']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ssh_auth_methods');
    }
}
