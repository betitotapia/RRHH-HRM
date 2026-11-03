<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IclockController extends Controller
{
    public function getrequest(Request $request)
    {
        Log::info('ZK GETREQUEST', [
            'ip'    => $request->ip(),
            'query' => $request->query(),
        ]);

        return response("OK", 200)->header('Content-Type', 'text/plain');
    }

    public function cdata(Request $request)
    {
        $sn    = (string) $request->query('SN');       // Serial del equipo
        $table = (string) $request->query('table');    // ATTLOG, etc.
        $body  = (string) $request->getContent();

        Log::info('ZK CDATA', [
            'ip'    => $request->ip(),
            'SN'    => $sn,
            'table' => $table,
            'bytes' => strlen($body),
        ]);

        // 1) Guardar crudo (siempre)
        DB::table('attendance_logs')->insert([
            'device_sn'  => $sn ?: null,
            'device_ip'  => $request->ip(),
            'table_name' => $table ?: null,
            'payload'    => $body,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2) Solo procesamos marcajes
        if (strtoupper(trim($table)) !== 'ATTLOG') {
            return response("OK", 200)->header('Content-Type', 'text/plain');
        }

        // 3) Procesar líneas -> attendance_records
        $lines = preg_split("/\r\n|\n|\r/", trim($body));

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            // Ejemplo esperado (UA860/UA8600):
            // UID  YYYY-MM-DD  HH:MM:SS  verify  status
            // (separado por tabs o espacios)
            $parts = preg_split('/\s+/', $line);

            if (count($parts) < 3) {
                Log::warning('ATTLOG inválida (menos de 3 columnas)', ['line' => $line]);
                continue;
            }

            $deviceUid = $parts[0] ?? null;
            $date      = $parts[1] ?? null;
            $time      = $parts[2] ?? null;

            if (!$deviceUid || !$date || !$time) {
                Log::warning('ATTLOG inválida (campos vacíos)', ['line' => $line]);
                continue;
            }

            // Validación básica de fecha/hora
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
                Log::warning('ATTLOG inválida (formato fecha/hora)', ['line' => $line]);
                continue;
            }

            $checkTime = "{$date} {$time}";

            $verify = $parts[3] ?? null;
            $status = $parts[4] ?? null;

            // Liga por device_uid numérico (tu caso)
            $employeeId = DB::table('employees')
                ->where('device_uid', (int) $deviceUid)
                ->value('id');

            $registeredAt = $checkTime; // "YYYY-MM-DD HH:MM:SS"

            // Buscar el último registro del MISMO DÍA para ese empleado
            $type = 'in';

            if ($employeeId) {
                $last = DB::table('attendance_records')
                    ->where('employee_id', $employeeId)
                    ->whereDate('registered_at', substr($registeredAt, 0, 10))
                    ->orderBy('registered_at', 'desc')
                    ->first();

                if ($last) {
                    $type = ($last->type === 'in') ? 'out' : 'in';
                }
            }

            // Deduplicar por UID + fecha/hora + SN (o lo que tú uses como llave)
            DB::table('attendance_records')->updateOrInsert(
                [
                    'device_uid'     => (string) $deviceUid,
                    'device_sn'      => $sn ?: null,
                    'registered_at'  => $registeredAt,
                ],
                [
                    'employee_id' => $employeeId,
                    'type'        => $type,            // ✅ AQUÍ está la clave
                    'source'      => 'device',
                    'device_ip'  => $request->ip(),
                    'user_agent'  => substr((string)$request->userAgent(), 0, 255),
                    'updated_at'  => now(),
                    'created_at'  => now(),
                ]
            );


           
        }

        return response("OK", 200)->header('Content-Type', 'text/plain');
    }

    
}
