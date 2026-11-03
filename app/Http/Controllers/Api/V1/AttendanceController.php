<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ClockRequest;
use App\Services\AttendanceService;
use App\Services\GeofencingService;
use App\Services\StationQrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function status(Request $request, AttendanceService $attendance)
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            abort(422, 'Usuario sin empleado.');
        }

        return response()->json($attendance->status($employee));
    }

    public function clock(
        ClockRequest $request,
        AttendanceService $attendance,
        StationQrService $qr,
        GeofencingService $geofencing
    ) {
        $employee = $request->user()->employee;
        if (! $employee) {
            abort(422, 'Usuario sin empleado.');
        }

        $data = [
            'source' => 'app',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'notes' => $request->notes,
        ];
        $photoPath = null;

        $usesStationQr = $employee->work_mode === 'office'
            || ($employee->work_mode === 'mixed' && $request->filled('station_token'));

        if (! $usesStationQr && ! $request->hasFile('photo')) {
            throw ValidationException::withMessages(['photo' => 'Debes tomar una selfie para registrar la asistencia.']);
        }

        if ($usesStationQr) {
            $location = $qr->verify((string) $request->station_token);
            if ($employee->work_mode === 'office' && $employee->work_location_id !== $location->id) {
                throw ValidationException::withMessages(['station_token' => 'El QR no corresponde a tu sede.']);
            }
            $data += [
                'work_location_id' => $location->id,
                'verification_method' => 'station_qr',
                'station_nonce' => hash('sha256', $request->station_token.$employee->id.$request->event_type),
            ];
        } else {
            if ($request->latitude === null || $request->longitude === null) {
                throw ValidationException::withMessages(['latitude' => 'La geolocalización es obligatoria.']);
            }

            $latitude = (float) $request->latitude;
            $longitude = (float) $request->longitude;
            $data += [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'verification_method' => 'geolocation',
            ];

            if ($employee->work_mode === 'mixed') {
                $nearest = $geofencing->nearestActiveLocation($latitude, $longitude);
                if (! $nearest) {
                    throw ValidationException::withMessages(['latitude' => 'No hay sedes activas disponibles para registrar la asistencia.']);
                }
                if (! $nearest['within']) {
                    throw ValidationException::withMessages(['latitude' => 'Estás fuera del rango permitido de las sedes disponibles.']);
                }
                $data += [
                    'work_location_id' => $nearest['location']->id,
                    'distance_meters' => $nearest['distance_meters'],
                    'in_geofence' => true,
                ];
            } elseif ($employee->work_mode === 'hospital' && $employee->workLocation) {
                $check = $geofencing->check($employee->workLocation, $latitude, $longitude);
                $data += [
                    'work_location_id' => $employee->work_location_id,
                    'distance_meters' => $check['distance_meters'],
                    'in_geofence' => $check['within'],
                ];
                if (! $check['within']) {
                    throw ValidationException::withMessages(['latitude' => 'Estás fuera de la geocerca permitida.']);
                }
            }
            // Home office conserva las coordenadas como evidencia, sin geocerca ni sede asociada.

            $photoPath = $request->file('photo')->store('attendance-selfies/'.now()->format('Y/m/d'), 'local');
            if (! $photoPath) {
                throw ValidationException::withMessages(['photo' => 'No fue posible guardar la selfie. Inténtalo nuevamente.']);
            }
            $data['photo_path'] = $photoPath;
        }

        try {
            $record = $attendance->record($employee, $request->event_type, $data);
        } catch (\Throwable $exception) {
            if ($photoPath) {
                Storage::disk('local')->delete($photoPath);
            }
            throw $exception;
        }

        return response()->json(['record' => $record, 'status' => $attendance->status($employee)], 201);
    }

    public function history(Request $request)
    {
        $employee = $request->user()->employee;

        return $employee->attendanceRecords()
            ->with('workLocation')
            ->when($request->from, fn ($query) => $query->whereDate('registered_at', '>=', $request->from))
            ->when($request->to, fn ($query) => $query->whereDate('registered_at', '<=', $request->to))
            ->latest('registered_at')
            ->paginate(min((int) $request->input('per_page', 20), 100));
    }
}
