<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Departament;

class Positions extends Model
{
    use HasFactory;

    protected $fillable = [
        'departament_id',                                                                     
        'name',
        'code',
        'level',
        'base_salary',
        'description',
        'profile_pdf_path',
        'active',
    ];

    public function departament()
    {
        return $this->belongsTo(Departament::class);
    }

    public function employees()
{
    return $this->hasMany(Employee::class);
}
    
}
