<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateBirthDatesFromCurp extends Command
{
    protected $signature = 'app:update-birth-dates-from-curp';
    protected $description = 'Updates birth date and sex for employees based on their CURP';

    public function handle()
    {
        $empleados = \App\Models\Empleado::whereNotNull('curp')->whereRaw('LENGTH(curp) = 18')->get();
        $count = 0;

        foreach ($empleados as $empleado) {
            $curp = strtoupper($empleado->curp);

            // Extract Date: YYMMDD (Positions 4-9 -> index 4-9)
            // PHP substr is 0-indexed, length based. 
            // 4, 5, 6, 7, 8, 9 -> Start at index 4, length 6
            $datePart = substr($curp, 4, 6); // YYMMDD
            $yy = substr($datePart, 0, 2);
            $mm = substr($datePart, 2, 2);
            $dd = substr($datePart, 4, 2);

            // Extract Sex: Position 11 -> index 10
            $sexChar = substr($curp, 10, 1);

            // Determine Century using Homoclave (Position 17 -> index 16)
            // RENAPO Rule: 0-9 (Numeric) -> 19xx, A-Z (Letter) -> 20xx
            $homoclave = substr($curp, 16, 1);
            $is1900s = is_numeric($homoclave);
            $century = $is1900s ? '19' : '20';

            $birthDate = "{$century}{$yy}-{$mm}-{$dd}";

            $empleado->fecha_nacimiento = $birthDate;
            $empleado->sexo = $sexChar;
            $empleado->save();
            $count++;
        }

        $this->info("Updated {$count} employees.");
    }
}
