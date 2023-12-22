
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiariesTable extends Migration
{
/**
* Run the migrations.
*
* @return void
*/
    public function up()
    {
        Schema::create("diaries", function (Blueprint $table) {
            $table->id();            
			$table->text("name");
			$table->text("description");
			$table->unsignedBigInteger("user_id");
			$table->foreign("user_id")->references("id")->on("users");

        });
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::dropIfExists("diaries");
    }
}
            