<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sede;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SedeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Sede::query()->with('company:id,nombre,codigo')->orderBy('nombre');

        $user = $request->user();
        if (! $user->isAdmin() && $user->company_id) {
            $query->where('company_id', $user->company_id);
        }

        $sedes = $query
            ->get()
            ->map(fn (Sede $sede) => $this->sedePayload($sede));

        return response()->json([
            'data' => $sedes,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $rules = [
            'nombre' => ['required', 'string', 'max:255'],
            'codigo' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:sedes,codigo'],
            'conectada' => ['sometimes', 'boolean'],
        ];

        if ($user->isAdmin()) {
            $rules['company_id'] = ['required', 'integer', 'exists:companies,id'];
        }

        $validated = $request->validate($rules);

        $companyId = $user->isAdmin()
            ? $validated['company_id']
            : $user->company_id;

        if (! $companyId) {
            return response()->json([
                'message' => 'El usuario no tiene una empresa asignada.',
            ], 422);
        }

        $sede = Sede::query()->create([
            'nombre' => $validated['nombre'],
            'codigo' => strtolower($validated['codigo']),
            'conectada' => $validated['conectada'] ?? true,
            'company_id' => $companyId,
        ]);

        $sede->load('company:id,nombre,codigo');

        return response()->json([
            'message' => 'Sede creada correctamente.',
            'data' => $this->sedePayload($sede),
        ], 201);
    }

    public function show(Request $request, Sede $sede): JsonResponse
    {
        if ($response = $this->authorizeSede($request, $sede)) {
            return $response;
        }

        $sede->load('company:id,nombre,codigo');

        return response()->json([
            'data' => $this->sedePayload($sede),
        ]);
    }

    public function update(Request $request, Sede $sede): JsonResponse
    {
        if ($response = $this->authorizeSede($request, $sede)) {
            return $response;
        }

        $user = $request->user();

        $rules = [
            'nombre' => ['required', 'string', 'max:255'],
            'codigo' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('sedes', 'codigo')->ignore($sede->id),
            ],
            'conectada' => ['required', 'boolean'],
        ];

        if ($user->isAdmin()) {
            $rules['company_id'] = ['required', 'integer', 'exists:companies,id'];
        }

        $validated = $request->validate($rules);

        $payload = [
            'nombre' => $validated['nombre'],
            'codigo' => strtolower($validated['codigo']),
            'conectada' => $validated['conectada'],
        ];

        if ($user->isAdmin()) {
            $payload['company_id'] = $validated['company_id'];
        }

        $sede->update($payload);
        $sede->load('company:id,nombre,codigo');

        return response()->json([
            'message' => 'Sede actualizada correctamente.',
            'data' => $this->sedePayload($sede->fresh()->load('company:id,nombre,codigo')),
        ]);
    }

    public function destroy(Request $request, Sede $sede): JsonResponse
    {
        if ($response = $this->authorizeSede($request, $sede)) {
            return $response;
        }

        $sede->delete();

        return response()->json([
            'message' => 'Sede eliminada correctamente.',
        ]);
    }

    private function authorizeSede(Request $request, Sede $sede): ?JsonResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return null;
        }

        if ($user->company_id && $sede->company_id === $user->company_id) {
            return null;
        }

        return response()->json([
            'message' => 'No tienes acceso a esta sede.',
        ], 403);
    }

    /**
     * @return array{
     *   id: int,
     *   nombre: string,
     *   codigo: string,
     *   conectada: bool,
     *   company_id: int|null,
     *   company: array{id: int, nombre: string, codigo: string}|null
     * }
     */
    private function sedePayload(Sede $sede): array
    {
        return [
            'id' => $sede->id,
            'nombre' => $sede->nombre,
            'codigo' => $sede->codigo,
            'conectada' => $sede->conectada,
            'company_id' => $sede->company_id,
            'company' => $sede->company
                ? [
                    'id' => $sede->company->id,
                    'nombre' => $sede->company->nombre,
                    'codigo' => $sede->company->codigo,
                ]
                : null,
        ];
    }
}
