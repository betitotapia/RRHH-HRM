<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushToken extends Model
{
    protected $fillable = ['user_id', 'token', 'platform', 'device_uid', 'last_seen_at', 'disabled_at'];
    protected $hidden = ['token'];
    protected $casts = ['last_seen_at' => 'datetime', 'disabled_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function tickets() { return $this->hasMany(PushNotificationTicket::class); }
}
