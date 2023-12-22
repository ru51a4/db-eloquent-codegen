
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class statuses extends Model
{
    use HasFactory;

            
    public function user()
    {
        return $this->belongsToMany("users", "users_statuses");
    }

}