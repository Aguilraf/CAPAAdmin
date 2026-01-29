<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Community;
use App\Models\Firefighter;
use App\Models\Capture;

class ImportFirefightersData extends Command
{
    protected $signature = 'firefighters:import {--db=u553580668_bomberos}';
    protected $description = 'Importar datos históricos de bomberos desde otra base de datos';

    public function handle()
    {
        $sourceDb = $this->option('db');

        $this->info("Importando datos desde: {$sourceDb}");

        try {
            // Importar Comunidades
            $this->info("\n📍 Importando Comunidades...");
            $communities = DB::select("SELECT * FROM {$sourceDb}.communities");
            $communityMap = [];

            foreach ($communities as $comm) {
                $newCommunity = Community::updateOrCreate(
                    ['name' => $comm->name],
                    [
                        'geolocation' => $comm->geolocation ?? null,
                        'created_at' => $comm->created_at,
                        'updated_at' => $comm->updated_at,
                    ]
                );
                $communityMap[$comm->id] = $newCommunity->id;
                $this->line("  ✓ {$comm->name}");
            }

            $this->info("✅ " . count($communities) . " comunidades importadas");

            // Importar Bomberos
            $this->info("\n👨‍🚒 Importando Bomberos...");
            $firefighters = DB::select("SELECT * FROM {$sourceDb}.firefighters");
            $firefighterMap = [];

            foreach ($firefighters as $ff) {
                $newFirefighter = Firefighter::updateOrCreate(
                    ['name' => $ff->name],
                    [
                        'community_id' => $communityMap[$ff->community_id] ?? null,
                        'active' => $ff->active ?? true,
                        'max_rounding_amount' => $ff->max_rounding_amount ?? 0,
                        'created_at' => $ff->created_at,
                        'updated_at' => $ff->updated_at,
                    ]
                );
                $firefighterMap[$ff->id] = $newFirefighter->id;
                $this->line("  ✓ {$ff->name}");
            }

            $this->info("✅ " . count($firefighters) . " bomberos importados");

            // Importar Capturas
            $this->info("\n📊 Importando Capturas...");
            $captures = DB::select("SELECT * FROM {$sourceDb}.captures ORDER BY date ASC");
            $imported = 0;
            $skipped = 0;

            $progressBar = $this->output->createProgressBar(count($captures));
            $progressBar->start();

            foreach ($captures as $cap) {
                // Verificar si ya existe
                $exists = Capture::where('date', $cap->date)
                    ->where('firefighter_id', $firefighterMap[$cap->firefighter_id] ?? null)
                    ->exists();

                if (!$exists && isset($firefighterMap[$cap->firefighter_id])) {
                    Capture::create([
                        'date' => $cap->date,
                        'year' => $cap->year,
                        'firefighter_id' => $firefighterMap[$cap->firefighter_id],
                        'subtotal' => $cap->subtotal,
                        'commission' => $cap->commission ?? ($cap->subtotal * 0.15),
                        'total' => $cap->total ?? ($cap->subtotal + ($cap->subtotal * 0.15)),
                        'rounding_commission' => $cap->rounding_commission ?? 0,
                        'rounding_total' => $cap->rounding_total ?? 0,
                        'requirement_year' => $cap->requirement_year ?? null,
                        'requirement_number' => $cap->requirement_number ?? null,
                        'assignment_type' => $cap->assignment_type ?? null,
                        'created_at' => $cap->created_at,
                        'updated_at' => $cap->updated_at,
                    ]);
                    $imported++;
                } else {
                    $skipped++;
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);

            $this->info("✅ {$imported} capturas importadas");
            $this->line("⏭️  {$skipped} capturas omitidas (duplicadas o sin bombero)");

            $this->newLine();
            $this->info("🎉 Importación completada exitosamente!");
            $this->newLine();

            // Resumen
            $this->table(
                ['Tipo', 'Cantidad'],
                [
                    ['Comunidades', count($communities)],
                    ['Bomberos', count($firefighters)],
                    ['Capturas Nuevas', $imported],
                    ['Capturas Omitidas', $skipped],
                    ['Total Capturas', count($captures)],
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Error durante la importación: " . $e->getMessage());
            $this->error("Línea: " . $e->getLine());
            $this->error("Archivo: " . $e->getFile());

            return Command::FAILURE;
        }
    }
}
