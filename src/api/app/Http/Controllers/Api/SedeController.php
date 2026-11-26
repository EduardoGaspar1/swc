<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sede;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SedeController extends Controller
{
    public function index(): JsonResponse
    {
        $sedes = Sede::query()
            ->orderBy('nombre')
            ->get()
            ->map(fn (Sede $sede) => $this->sedePayload($sede));

        return response()->json([
            'data' => $sedes,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'codigo' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:sedes,codigo'],
            'conectada' => ['sometimes', 'boolean'],
        ]);

        $sede = Sede::query()->create([
            'nombre' => $validated['nombre'],
            'codigo' => strtolower($validated['codigo']),
            'conectada' => $validated['conectada'] ?? true,
        ]);

        return response()->json([
            'message' => 'Sede creada correctamente.',
            'data' => $this->sedePayload($sede),
        ], 201);
    }

    public function show(Sede $sede): JsonResponse
    {
        return response()->json([
            'data' => $this->sedePayload($sede),
        ]);
    }

    public function update(Request $request, Sede $sede): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'codigo' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('sedes', 'codigo')->ignore($sede->id),
            ],
            'conectada' => ['required', 'boolean'],
        ]);

        $sede->update([
            'nombre' => $validated['nombre'],
            'codigo' => strtolower($validated['codigo']),
            'conectada' => $validated['conectada'],
        ]);

        return response()->json([
            'message' => 'Sede actualizada correctamente.',
            'data' => $this->sedePayload($sede->fresh()),
        ]);
    }

    public function destroy(Sede $sede): JsonResponse
    {
        $sede->delete();

        return response()->json([
            'message' => 'Sede eliminada correctamente.',
        ]);
    }

    /**
     * @return array{id: int, nombre: string, codigo: string, conectada: bool}
     */
    private function sedePayload(Sede $sede): array
    {
        return [
            'id' => $sede->id,
            'nombre' => $sede->nombre,
            'codigo' => $sede->codigo,
            'conectada' => $sede->conectada,
        ];
    }
}
