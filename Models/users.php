
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class users extends Model
{
    use HasFactory;

            
    public function diaries()
    {
        return $this->hasMany("diaries");
    }

    public function posts()
    {
        return $this->hasMany("posts");
    }

}