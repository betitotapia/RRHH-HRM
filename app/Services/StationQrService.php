<?php
namespace App\Services;
use App\Models\WorkLocation;
use Illuminate\Validation\ValidationException;
class StationQrService {
 public function issue(WorkLocation $l): array { $slot=intdiv(now()->timestamp,60); $raw=$l->id.'|'.$slot; return ['token'=>base64_encode($raw.'|'.hash_hmac('sha256',$raw,$l->qr_secret)),'expires_at'=>now()->addSeconds(60-now()->second)->toIso8601String()]; }
 public function verify(string $token): WorkLocation { $v=explode('|',(string)base64_decode($token,true)); if(count($v)!==3) throw ValidationException::withMessages(['station_token'=>'QR inválido.']); [$id,$slot,$sig]=$v; $l=WorkLocation::whereKey($id)->where('is_active',true)->first(); if(!$l || abs(intdiv(now()->timestamp,60)-(int)$slot)>1 || !hash_equals(hash_hmac('sha256',$id.'|'.$slot,$l->qr_secret),$sig)) throw ValidationException::withMessages(['station_token'=>'QR vencido o inválido.']); return $l; }
}
