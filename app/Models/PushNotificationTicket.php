<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushNotificationTicket extends Model
{
    protected $fillable = ['push_token_id', 'leave_request_id', 'ticket_id', 'status', 'error_code', 'checked_at'];
    protected $casts = ['checked_at' => 'datetime'];

    public function pushToken() { return $this->belongsTo(PushToken::class); }
    public function leaveRequest() { return $this->belongsTo(LeaveRequest::class); }
}
