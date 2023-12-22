
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Createusers_statusesTable extends Migration
{
/**
* Run the migrations.
*
* @return void
*/
    public function up()
    {
        
        Schema::create("users_statuses", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("users_id");
            $table->unsignedBigInteger("statuses_id");
            $table->foreign("users_id")->references("id")->on("users");
            $table->foreign("statuses_id")->references("id")->on("statuses");
        });
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::dropIfExists("users_statuses");
    }
}
        