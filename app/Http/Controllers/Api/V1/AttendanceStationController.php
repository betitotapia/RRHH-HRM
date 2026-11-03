<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkLocation;
use App\Services\StationQrService;

class AttendanceStationController extends Controller
{
 public function token(WorkLocation $workLocation,StationQrService $qr){abort_unless(auth()->user()->isSuperAdmin(),403);return response()->json($qr->issue($workLocation));}
}
