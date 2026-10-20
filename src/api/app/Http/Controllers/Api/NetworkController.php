<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alerta;
use App\Models\Enlace;
use App\Models\Sede;
use Illuminate\Http\JsonResponse;

class NetworkController extends Controller
{
    public function summary(): JsonResponse
    {
        $sedesTotal = Sede::query()->count();
        $sedesConectadas = Sede::query()->where('conectada', true)->count();

        $enlacesTotal = Enlace::query()->count();
        $enlacesActivos = Enlace::query()->where('estado', '!=', 'caido')->count();

        $latenciaMediaMs = Enlace::query()
            ->whereNotNull('latencia_ms')
            ->avg('latencia_ms');

        $alertasActivas = Alerta::query()->where('activa', true)->count();

        return response()->json([
            'sedesConectadas' => $sedesConectadas,
            'sedesTotal' => $sedesTotal,
            'enlacesActivos' => $enlacesActivos,
            'enlacesTotal' => $enlacesTotal,
            'latenciaMediaMs' => $latenciaMediaMs === null ? null : (int) round($latenciaMediaMs),
            'alertasActivas' => $alertasActivas,
        ]);
    }

    public function links(): JsonResponse
    {
        $links = Enlace::query()
            ->with(['sedeOrigen', 'sedeDestino'])
            ->orderBy('id')
            ->get()
            ->map(fn (Enlace $enlace) => [
                'id' => $enlace->id,
                'nombre' => $enlace->nombre,
                'latenciaMs' => $enlace->latencia_ms,
                'perdidaPct' => $enlace->perdida_pct,
                'estado' => $enlace->estado,
            ]);

        return response()->json([
            'data' => $links,
        ]);
    }
}
