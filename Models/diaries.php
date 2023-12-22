
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class diaries extends Model
{
    use HasFactory;

            
    public function posts()
    {
        return $this->hasMany("posts");
    }

    public function user()
    {
        return $this->belongsTo("users");
    }

}