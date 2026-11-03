<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;




class Commission extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'active',
    ];

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'commission_employee')
                    ->withPivot('role')
                    ->withTimestamps();
    }
}

?>
