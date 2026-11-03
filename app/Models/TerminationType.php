<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TerminationType extends Model
{
    protected $fillable = ['name', 'active'];
    protected $casts = ['active' => 'boolean'];

    public function terminations()
    {
        return $this->hasMany(EmployeeTermination::class);
    }
}
