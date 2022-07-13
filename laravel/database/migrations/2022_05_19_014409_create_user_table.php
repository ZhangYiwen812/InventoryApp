<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->string('phonenumber',11)->unique();
            $table->string('name',20);
            $table->string('email',320);
            $table->string('password',20);
            $table->string('adminphone',11);
            $table->string('adminkey',5);
            $table->integer('subordernumber')->default(0);
            $table->integer('auth')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user');
    }
}
