<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProxyListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proxy_lists', function (Blueprint $table) {
            $table->id();

            $table->ipAddress('ip');
            $table->unsignedInteger('port');
            $table->string('login')->nullable();
            $table->string('password')->nullable();
            $table->string('url');
            $table->boolean('blocked')->default(false);

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
        Schema::dropIfExists('proxy_lists');
    }
}
