<?php
/*
Класс для генерации php фаилов бд по json схемы бд
*/
class codegen{
    static public function gen(){
        $migrations = [];
        $models = [];

        $scheme = json_decode(file_get_contents('./scheme_diary.json'),1);
         
        self::createMigration($scheme);
        self::createModels($scheme);
        
       
    }
    static public function createModels($scheme){
         foreach($scheme as $table){
            $tModel = '';
            $tModel .= '
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class '.$table['tableName'].' extends Model
{
    use HasFactory;

            ';
            foreach($table["column"] as $col){
                if($col['colType'] == 'belongsTo'){
                    $tModel .= '
    public function '.explode('_',$col['colName'])[0].'()
    {
        return $this->belongsTo("'.$scheme[$col['relation']]['tableName'].'");
    }
';
                }
                if($col['colType'] == 'hasMany'){
                    $tModel .= '
    public function '.$col['colName'].'()
    {
        return $this->hasMany("'.$scheme[$col['relation']]['tableName'].'");
    }
';
                }
  
            }
            $tModel .= '
}';
        file_put_contents('./Models/'.$table['tableName'].'.php', $tModel);

    }
}

    static public function createMigration($scheme){
         foreach($scheme as $table){
            $tMigration = '';
            $tMigration .= '
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
        Schema::create("'.$table['tableName'].'", function (Blueprint $table) {
            $table->id();            
';
            foreach($table["column"] as $col){
                if($col['colType'] == 'text'){
                    $tMigration .= "\t\t\t".'$table->text("'.$col['colName'].'");';
                    $tMigration .= "\n";
                }
                if($col['colType'] == 'int'){
                    $tMigration .= "\t\t\t".'$table->integer("'.$col['colName'].'");';
                    $tMigration .= "\n";
                }
                if($col['colType'] == 'belongsTo'){
                    $tMigration .= "\t\t\t".'$table->unsignedBigInteger("'.$col['colName'].'");';
                    $tMigration .= "\n"; 
                    $tMigration .= "\t\t\t".'$table->foreign("'.$col['colName'].'")->references("id")->on("'.$scheme[$col['relation']]['tableName'].'");';
                    $tMigration .= "\n";
                }
            }
            $tMigration .= '
        });
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::dropIfExists("'.$table['tableName'].'");
    }
}
            ';
            file_put_contents('./Migrations/'.$table['tableName'].'.php', $tMigration);
        }
    }
}
codegen::gen();