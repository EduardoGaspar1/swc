<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::query()
            ->with('company:id,nombre,codigo')
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => $this->userPayload($user));

        return response()->json([
            'data' => $users,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'integer', Rule::in([User::ROLE_USER, User::ROLE_ADMIN])],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
        ]);

        $user = User::query()->create($validated);
        $user->load('company:id,nombre,codigo');

        return response()->json([
            'message' => 'Usuario creado correctamente.',
            'data' => $this->userPayload($user),
        ], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', 'integer', Rule::in([User::ROLE_USER, User::ROLE_ADMIN])],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->company_id = $validated['company_id'] ?? null;

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();
        $user->load('company:id,nombre,codigo');

        return response()->json([
            'message' => 'Usuario actualizado correctamente.',
            'data' => $this->userPayload($user),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($request->user()->id === $user->id) {
            return response()->json([
                'message' => 'No puedes eliminar tu propio usuario.',
            ], 422);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado correctamente.',
        ]);
    }

    /**
     * @return array{
     *   id: int,
     *   name: string,
     *   email: string,
     *   role: int,
     *   company_id: int|null,
     *   company: array{id: int, nombre: string, codigo: string}|null
     * }
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => (int) $user->role,
            'company_id' => $user->company_id,
            'company' => $user->company
                ? [
                    'id' => $user->company->id,
                    'nombre' => $user->company->nombre,
                    'codigo' => $user->company->codigo,
                ]
                : null,
        ];
    }
}
