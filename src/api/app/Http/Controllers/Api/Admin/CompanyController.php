<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(): JsonResponse
    {
        $companies = Company::query()
            ->withCount(['users', 'sedes'])
            ->orderBy('nombre')
            ->get()
            ->map(fn (Company $company) => $this->companyPayload($company));

        return response()->json([
            'data' => $companies,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'codigo' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:companies,codigo'],
        ]);

        $company = Company::query()->create([
            'nombre' => $validated['nombre'],
            'codigo' => strtolower($validated['codigo']),
        ]);

        $company->loadCount(['users', 'sedes']);

        return response()->json([
            'message' => 'Empresa creada correctamente.',
            'data' => $this->companyPayload($company),
        ], 201);
    }

    public function destroy(Company $company): JsonResponse
    {
        $company->delete();

        return response()->json([
            'message' => 'Empresa eliminada correctamente.',
        ]);
    }

    /**
     * @return array{id: int, nombre: string, codigo: string, users_count: int, sedes_count: int}
     */
    private function companyPayload(Company $company): array
    {
        return [
            'id' => $company->id,
            'nombre' => $company->nombre,
            'codigo' => $company->codigo,
            'users_count' => (int) ($company->users_count ?? 0),
            'sedes_count' => (int) ($company->sedes_count ?? 0),
        ];
    }
}
