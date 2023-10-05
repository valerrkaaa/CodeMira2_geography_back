<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classes extends Model
{
    use HasFactory, SoftDeletes;

    public function pupils(){
        return $this->hasMany(User::class);
    }

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
