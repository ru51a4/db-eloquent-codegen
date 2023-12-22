
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatepostsTable extends Migration
{
/**
* Run the migrations.
*
* @return void
*/
    public function up()
    {
        Schema::create("posts", function (Blueprint $table) {
            $table->id();            
		$table->text("message");
		$table->unsignedBigInteger("user_id");
		$table->foreign("user_id")->references("id")->on("users");
		$table->unsignedBigInteger("diary_id");
		$table->foreign("diary_id")->references("id")->on("diaries");

        });
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::dropIfExists("posts");
    }
}
            