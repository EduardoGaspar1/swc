<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enlace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EnlaceController extends Controller
{
    private const ESTADOS = ['operativo', 'caido', 'ruta_alternativa'];

    public function index(): JsonResponse
    {
        $enlaces = Enlace::query()
            ->with(['sedeOrigen', 'sedeDestino'])
            ->orderBy('id')
            ->get()
            ->map(fn (Enlace $enlace) => $this->enlacePayload($enlace));

        return response()->json([
            'data' => $enlaces,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatedPayload($request);

        $enlace = Enlace::query()->create($validated);
        $enlace->load(['sedeOrigen', 'sedeDestino']);

        return response()->json([
            'message' => 'Enlace creado correctamente.',
            'data' => $this->enlacePayload($enlace),
        ], 201);
    }

    public function show(Enlace $enlace): JsonResponse
    {
        $enlace->load(['sedeOrigen', 'sedeDestino']);

        return response()->json([
            'data' => $this->enlacePayload($enlace),
        ]);
    }

    public function update(Request $request, Enlace $enlace): JsonResponse
    {
        $validated = $this->validatedPayload($request, $enlace);

        $enlace->update($validated);
        $enlace->load(['sedeOrigen', 'sedeDestino']);

        return response()->json([
            'message' => 'Enlace actualizado correctamente.',
            'data' => $this->enlacePayload($enlace),
        ]);
    }

    public function destroy(Enlace $enlace): JsonResponse
    {
        $enlace->delete();

        return response()->json([
            'message' => 'Enlace eliminado correctamente.',
        ]);
    }

    /**
     * @return array{sede_origen_id: int, sede_destino_id: int, estado: string, latencia_ms: int|null, perdida_pct: int}
     */
    private function validatedPayload(Request $request, ?Enlace $enlace = null): array
    {
        $validated = $request->validate([
            'sedeOrigenId' => ['required', 'integer', 'exists:sedes,id', 'different:sedeDestinoId'],
            'sedeDestinoId' => ['required', 'integer', 'exists:sedes,id'],
            'estado' => ['required', 'string', Rule::in(self::ESTADOS)],
            'latenciaMs' => ['nullable', 'integer', 'min:0', 'max:60000'],
            'perdidaPct' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $origenId = (int) $validated['sedeOrigenId'];
        $destinoId = (int) $validated['sedeDestinoId'];

        $duplicate = Enlace::query()
            ->where('sede_origen_id', $origenId)
            ->where('sede_destino_id', $destinoId)
            ->when($enlace, fn ($query) => $query->where('id', '!=', $enlace->id))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'sedeDestinoId' => ['Ya existe un enlace entre estas sedes.'],
            ]);
        }

        return [
            'sede_origen_id' => $origenId,
            'sede_destino_id' => $destinoId,
            'estado' => $validated['estado'],
            'latencia_ms' => $validated['latenciaMs'] ?? null,
            'perdida_pct' => (int) $validated['perdidaPct'],
        ];
    }

    /**
     * @return array{
     *   id: int,
     *   nombre: string,
     *   sedeOrigenId: int,
     *   sedeDestinoId: int,
     *   sedeOrigen: array{id: int, nombre: string, codigo: string}|null,
     *   sedeDestino: array{id: int, nombre: string, codigo: string}|null,
     *   estado: string,
     *   latenciaMs: int|null,
     *   perdidaPct: int
     * }
     */
    private function enlacePayload(Enlace $enlace): array
    {
        return [
            'id' => $enlace->id,
            'nombre' => $enlace->nombre,
            'sedeOrigenId' => $enlace->sede_origen_id,
            'sedeDestinoId' => $enlace->sede_destino_id,
            'sedeOrigen' => $enlace->sedeOrigen ? [
                'id' => $enlace->sedeOrigen->id,
                'nombre' => $enlace->sedeOrigen->nombre,
                'codigo' => $enlace->sedeOrigen->codigo,
            ] : null,
            'sedeDestino' => $enlace->sedeDestino ? [
                'id' => $enlace->sedeDestino->id,
                'nombre' => $enlace->sedeDestino->nombre,
                'codigo' => $enlace->sedeDestino->codigo,
            ] : null,
            'estado' => $enlace->estado,
            'latenciaMs' => $enlace->latencia_ms,
            'perdidaPct' => $enlace->perdida_pct,
        ];
    }
}
