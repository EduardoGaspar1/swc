<?php

namespace Database\Seeders;

use App\Models\Alerta;
use App\Models\Enlace;
use App\Models\Sede;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class EjemploBasicoSeeder extends Seeder
{
    /**
     * Escenario básico para probar el dashboard:
     * 3 sedes, 3 enlaces (uno caído + ruta alternativa) y 2 alertas.
     */
    public function run(): void
    {
        Alerta::query()->delete();
        Enlace::query()->delete();
        Sede::query()->delete();

        $central = Sede::create([
            'nombre' => 'Central',
            'codigo' => 'central',
            'conectada' => true,
        ]);

        $sede2 = Sede::create([
            'nombre' => 'Sede 2',
            'codigo' => 'sede-2',
            'conectada' => true,
        ]);

        $sede3 = Sede::create([
            'nombre' => 'Sede 3',
            'codigo' => 'sede-3',
            'conectada' => true,
        ]);

        Enlace::create([
            'sede_origen_id' => $central->id,
            'sede_destino_id' => $sede2->id,
            'estado' => 'operativo',
            'latencia_ms' => 12,
            'perdida_pct' => 0,
        ]);

        $enlaceCentralSede3 = Enlace::create([
            'sede_origen_id' => $central->id,
            'sede_destino_id' => $sede3->id,
            'estado' => 'caido',
            'latencia_ms' => null,
            'perdida_pct' => 100,
        ]);

        Enlace::create([
            'sede_origen_id' => $sede2->id,
            'sede_destino_id' => $sede3->id,
            'estado' => 'ruta_alternativa',
            'latencia_ms' => 28,
            'perdida_pct' => 0,
        ]);

        $hoy = Carbon::today();

        Alerta::create([
            'enlace_id' => $enlaceCentralSede3->id,
            'severidad' => 'critical',
            'mensaje' => 'Enlace Central ↔ Sede 3 caído. Ruta alternativa activada por la Sede 2.',
            'activa' => true,
            'ocurrio_en' => $hoy->copy()->setTime(14, 32),
        ]);

        Alerta::create([
            'enlace_id' => $enlaceCentralSede3->id,
            'severidad' => 'warning',
            'mensaje' => 'Latencia alta en Central ↔ Sede 3 (185 ms).',
            'activa' => false,
            'ocurrio_en' => $hoy->copy()->setTime(14, 29),
        ]);
    }
}
