<?php
/*
Класс для генерации php фаилов бд по json схемы бд
*/
$GLOBALS['id'] = uniqid();
function force_file_put_contents($pathWithFileName, $data, $flags = 0)
{
    $dirPathOnly = dirname($pathWithFileName);
    if (!file_exists($dirPathOnly)) {
        mkdir($dirPathOnly, 0777, true); // folder permission 0775
    }
    file_put_contents($pathWithFileName, $data, $flags);
}

class codegen
{

    static public function gen()
    {
        $scheme = json_decode(file_get_contents("php://input"), 1)['data'];
        self::createMigration($scheme);
        self::createModels($scheme);


    }

    static public function createModels($scheme)
    {
        $mapmtm = [];
        foreach ($scheme as $table) {
            if (!$table['generate']) {
                continue;
            }
            $tModel = '';
            $tModel .= '<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ' . $table['modelNamae'] . ' extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $table = "' . $table["tableName"] . '";

        ';
            foreach ($table["column"] as $col) {
                if ($col['colType'] == 'int') {
                    $tModel .= '
    public int $' . $col['colName'] . ';
';
                }

                if ($col['colType'] == 'text') {
                    $tModel .= '
    public string $' . $col['colName'] . ';
';
                }

                if ($col['colType'] == 'belongsTo') {
                    $tModel .= '
    public function ' . explode('_', $col['colName'])[0] . '()
    {
        return $this->belongsTo("\App\Models\\' . $scheme[$col['relation']]['modelName'] . '");
    }
';
                }
                if ($col['colType'] == 'hasMany') {
                    $tModel .= '
    public function ' . $col['colName'] . '()
    {
        return $this->hasMany("\App\Models\\' . $scheme[$col['relation']]['modelName'] . '");
    }
';
                }

                if ($col['colType'] == 'belongsToMany') {
                    $tabl = $table["tableName"] . '_' . $scheme[$col['relation']]["tableName"];
                    if ($mapmtm[$tabl]) {
                        $tabl = $scheme[$col['relation']]["tableName"] . '_' . $table["tableName"];
                    }
                    $mapmtm[$table["tableName"] . '_' . $scheme[$col['relation']]["tableName"]] = true;
                    $mapmtm[$scheme[$col['relation']]["tableName"] . '_' . $table["tableName"]] = true;

                    $tModel .= '
    public function ' . $col['colName'] . '()
    {
        return $this->belongsToMany("\App\Models\\' . $scheme[$col['relation']]['modelName'] . '", "' . $tabl . '");
    }
';
                }

            }
            $tModel .= '
}';
            force_file_put_contents('./' . $GLOBALS['id'] . '//models/' . $table['modelName'] . '.php', $tModel);

        }
    }

    static public function createMigration($scheme)
    {
        foreach ($scheme as $table) {
            if (!$table['generate']) {
                continue;
            }
            $tMigration = '';
            $tMigration .= '
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
/**
* Run the migrations.
*
* @return void
*/
    public function up()
    {
        Schema::create("' . $table['tableName'] . '", function (Blueprint $table) {
            $table->id();            
';
            foreach ($table["column"] as $col) {
                if ($col['colType'] == 'text') {
                    $tMigration .= "\t\t" . '$table->text("' . $col['colName'] . '")->nullable();';
                    $tMigration .= "\n";
                }
                if ($col['colType'] == 'int') {
                    $tMigration .= "\t\t" . '$table->integer("' . $col['colName'] . '")->nullable();';
                    $tMigration .= "\n";
                }
                if ($col['colType'] == 'belongsTo') {
                    $tMigration .= "\t\t" . '$table->unsignedBigInteger("' . $col['colName'] . '")->nullable();';
                    $tMigration .= "\n";
                    $tMigration .= "\t\t" . '$table->foreign("' . $col['colName'] . '")->references("id")->on("' . $scheme[$col['relation']]['tableName'] . '");';
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
        Schema::dropIfExists("' . $table['tableName'] . '");
    }
};
            ';
            force_file_put_contents('./' . $GLOBALS['id'] . '//migrations/' . $table['tableName'] . '_table.php', $tMigration);

            //many to many
            $mtm = [];
            $map = [];
            foreach ($scheme as $table) {
                foreach ($table["column"] as $col) {
                    if ($col['colType'] == 'belongsToMany') {
                        if ($map[$table["tableName"] . '_' . $scheme[$col['relation']]["tableName"]]) {
                            continue;
                        }
                        $map[$table["tableName"] . '_' . $scheme[$col['relation']]["tableName"]] = true;
                        $map[$scheme[$col['relation']]["tableName"] . '_' . $table["tableName"]] = true;

                        $mtm[] = $table["tableName"] . '_' . $scheme[$col['relation']]["tableName"];
                    }
                }
            }
            foreach ($mtm as $table) {
                $tMigration = '';
                $tMigration .= '
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
/**
* Run the migrations.
*
* @return void
*/
    public function up()
    {
        
        Schema::create("' . $table . '", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("' . explode("_", $table)[0] . '_id");
            $table->unsignedBigInteger("' . explode("_", $table)[1] . '_id");
            $table->foreign("' . explode("_", $table)[0] . '_id")->references("id")->on("' . explode("_", $table)[0] . '");
            $table->foreign("' . explode("_", $table)[1] . '_id")->references("id")->on("' . explode("_", $table)[1] . '");
        });
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::dropIfExists("' . $table . '");
    }
};
        ';
                force_file_put_contents('./' . $GLOBALS['id'] . '/' . 'migrations/' . $table . '_table.php', $tMigration);

            }

        }
    }
}

codegen::gen();
$rootPath = rtrim($rootPath, '\\/');

// Get real path for our folder
$rootPath = realpath($GLOBALS['id']);
$dels = [];
$zip = new ZipArchive;
if ($zip->open($GLOBALS['id'] . '.zip', ZipArchive::CREATE) === TRUE) {
    // Add files to the zip file inside demo_folder
    foreach (['./' . $GLOBALS['id'] . '/migrations', './' . $GLOBALS['id'] . '/models'] as $path) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                $dels[] = $filePath;
                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }
    }
    // All files are added, so close the zip file.
    $zip->close();
}

echo "/db-eloquent-codegen/" . $GLOBALS['id'] . '.zip';
foreach ($dels as $path) {
    unlink($path);
}