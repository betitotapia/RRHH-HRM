<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departament extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'active',
    ];

    public function positions()
{
    return $this->hasMany(Positions::class);
}
public function employees()
{
    return $this->hasMany(Employee::class, 'departament_id');
}
    
}

