<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alerta;
use Illuminate\Http\JsonResponse;

class AlertController extends Controller
{
    public function index(): JsonResponse
    {
        $alerts = Alerta::query()
            ->orderByDesc('ocurrio_en')
            ->limit(20)
            ->get()
            ->map(fn (Alerta $alerta) => [
                'id' => $alerta->id,
                'severidad' => $alerta->severidad,
                'mensaje' => $alerta->mensaje,
                'hora' => $alerta->ocurrio_en->format('H:i'),
                'activa' => $alerta->activa,
            ]);

        return response()->json([
            'data' => $alerts,
        ]);
    }
}
