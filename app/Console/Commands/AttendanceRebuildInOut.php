<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AttendanceRebuildInOut extends Command
{
    protected $signature = 'attendance:rebuild-inout {--date=} {--days=7}';
    protected $description = 'Recalcula type=in/out por día usando primera y última marca del día.';

    public function handle(): int
    {
        $date = $this->option('date');
        $days = (int) $this->option('days');

        if ($date) {
            $start = $date;
            $end = $date;
        } else {
            $start = now()->subDays($days)->toDateString();
            $end   = now()->toDateString();
        }

        // Traer pares (employee_id, fecha) con marcas en ese rango
        $pairs = DB::table('attendance_records')
            ->selectRaw('employee_id, DATE(registered_at) as d')
            ->whereBetween(DB::raw('DATE(registered_at)'), [$start, $end])
            ->whereNotNull('employee_id')
            ->groupBy('employee_id', DB::raw('DATE(registered_at)'))
            ->get();

        $updated = 0;

        foreach ($pairs as $p) {
            $employeeId = $p->employee_id;
            $d = $p->d;

            // Obtener ids ordenados por hora
            $ids = DB::table('attendance_records')
                ->where('employee_id', $employeeId)
                ->whereDate('registered_at', $d)
                ->orderBy('registered_at')
                ->pluck('id')
                ->all();

            if (count($ids) === 0) continue;

            $firstId = $ids[0];
            $lastId  = $ids[count($ids) - 1];

            // Por defecto, todo lo del día lo dejamos en 'in' (o null) y marcamos first/last
            // Si solo hay 1 marca: queda 'in'
            DB::table('attendance_records')
                ->where('employee_id', $employeeId)
                ->whereDate('registered_at', $d)
                ->update(['type' => 'in']);

            $updated += DB::table('attendance_records')->where('id', $firstId)->update(['type' => 'in']);

            if ($lastId !== $firstId) {
                $updated += DB::table('attendance_records')->where('id', $lastId)->update(['type' => 'out']);
            }
        }

        $this->info("OK. Registros ajustados: {$updated}. Rango: {$start} a {$end}");
        return self::SUCCESS;
    }
}
