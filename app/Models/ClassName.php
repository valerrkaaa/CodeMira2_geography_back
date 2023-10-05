<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassName extends Model
{
    use HasFactory, SoftDeletes;

    public function pupils(){
        return $this->hasMany(Classes::class);
    }

    // public function pupils(){
    //     return $this->hasManyThrough(User::class, Classes::class);
    // }

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
