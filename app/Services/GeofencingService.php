<?php
namespace App\Services;
use App\Models\WorkLocation;
class GeofencingService {
 public function calculateDistance(float $a,float $b,float $c,float $d): float { $r=6371000; $p1=deg2rad($a); $p2=deg2rad($c); $dp=deg2rad($c-$a); $dl=deg2rad($d-$b); $x=sin($dp/2)**2+cos($p1)*cos($p2)*sin($dl/2)**2; return 2*$r*asin(min(1,sqrt($x))); }
 public function check(WorkLocation $l,float $lat,float $lng): array { $d=(int)round($this->calculateDistance((float)$l->latitude,(float)$l->longitude,$lat,$lng)); return ['within'=>$d<=$l->radius_meters,'distance_meters'=>$d]; }

 /** Devuelve la sede activa más cercana y el resultado de su geocerca. */
 public function nearestActiveLocation(float $lat, float $lng): ?array
 {
     $nearest = null;
     foreach (WorkLocation::query()->where('is_active', true)->get() as $location) {
         $check = $this->check($location, $lat, $lng);
         if ($nearest === null || $check['distance_meters'] < $nearest['distance_meters']) {
             $nearest = ['location' => $location] + $check;
         }
     }
     return $nearest;
 }
}
