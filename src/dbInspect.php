<?php
error_reporting(E_ERROR | E_PARSE);

require_once './../vendor/autoload.php';
use iamcal\SQLParser;

$GLOBALS['parser'] = new SQLParser();

/*
Класс для генерации json схемы бд по готовой бд
*/
class dbInspect{
    public static function gen(){
        $relations = [];
        $pdo = new \PDO("sqlite:" . 'db');
        $scheme = $pdo->query("SELECT name, sql FROM sqlite_master
                                WHERE type='table'
                                ORDER BY name;
                                ");
        $res = [];
        $map = [];
        $counter = -1;
        foreach($scheme as $c){
            $text = $c['sql'];
            if($c['name'] == 'sqlite_sequence'){
                continue;
            }
            $item = $GLOBALS['parser']->parse($text);

            foreach($item as $key => $table){
                $t = ["tableName" => $key, "modelName" => "User", "generate" => true, "column" => []];
                $tc = [];
                foreach($table["fields"] as $field){
                    $tc[$field["name"]] = ["colName" => $field["name"], "colType" => $field["type"], "relation" => -1 ];
                }
                $t['column'] = $tc;

                $res[$t["tableName"]] = $t;
                $map[$t["tableName"]] = $counter++;
                //var_dump($table["indexes"]);
                foreach($table["indexes"] as $relation){
                   
                    if($relation["type"] == 'FOREIGN' && $relation["more"][3] == '"id"'){
                        $relations[$key][$relation["cols"][0]["name"]] = ["type" => 'belongsTo', 'table' => $relation["more"][1]];
                        $relations[$relation["more"][1]][$key.'s'] =  ["type" => 'hasMany', 'table' => $key];
                    }
                }

            }
        }
        foreach($relations as $key => $d){
            foreach($relations[$key] as $col => $c){
                if($c["type"] == "hasMany"){
                    $res[$key]["column"][$col] =["colName" => $col, "colType" => "hasMany", "relation" => $map[$c["table"]]];
                }
                if($c["type"] == "belongsTo"){
                    $res[$key]["column"][$col] =["colName" => $col, "colType" => "belongsTo", "relation" => $map[$c["table"]]];
                }

            }
        }

        file_put_contents('ouput.json', json_encode($res, JSON_UNESCAPED_UNICODE));
    }
}

dbInspect::gen();