<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Teams\CreateTeam;
use App\Concerns\PasswordValidationRules;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    use PasswordValidationRules;

    public function index(Request $request): Response
    {
        return Inertia::render('admin/users/Index', [
            'currentUserId' => $request->user()?->id,
            'users' => User::query()
                ->orderBy('name')
                ->get()
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'name_dv' => $user->name_dv,
                    'avatar' => $user->avatar,
                    'email' => $user->email,
                    'role' => $user->role->value,
                    'roleLabel' => $user->role->label(),
                    'verified' => $user->email_verified_at !== null,
                    'created_at' => $user->created_at?->format('Y-m-d'),
                ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/users/Form', ['user' => null, 'roles' => $this->roleOptions()]);
    }

    public function store(Request $request, CreateTeam $createTeam): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_dv' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'string', 'max:2048'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'password' => $this->passwordRules(),
        ]);

        DB::transaction(function () use ($validated, $createTeam) {
            $user = User::create([
                'name' => $validated['name'],
                'name_dv' => $validated['name_dv'] ?? null,
                'avatar' => $validated['avatar'] ?? null,
                'email' => $validated['email'],
                'role' => $validated['role'],
                'password' => $validated['password'],
            ]);

            $createTeam->handle($user, $user->name."'s Team", isPersonal: true);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'User created.']);

        return redirect()->route('admin.users.index');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('admin/users/Form', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'name_dv' => $user->name_dv,
                'avatar' => $user->avatar,
                'email' => $user->email,
                'role' => $user->role->value,
            ],
            'roles' => $this->roleOptions(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'name_dv' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'string', 'max:2048'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::enum(UserRole::class)],
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['string', 'confirmed', Password::default()];
        }

        $validated = $request->validate($rules);

        // Prevent an admin from removing their own admin access (lock-out guard).
        if ($request->user()->is($user) && $validated['role'] !== UserRole::Admin->value) {
            return back()->withErrors(['role' => 'You cannot change your own role.']);
        }

        $user->name = $validated['name'];
        $user->name_dv = $validated['name_dv'] ?? null;
        $user->avatar = $validated['avatar'] ?? null;
        $user->email = $validated['email'];
        $user->role = UserRole::from($validated['role']);

        if ($request->filled('password')) {
            $user->password = $validated['password'];
        }

        $user->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'User updated.']);

        return redirect()->route('admin.users.index');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            Inertia::flash('toast', ['type' => 'error', 'message' => "You can't delete your own account."]);

            return back();
        }

        DB::transaction(function () use ($user) {
            // Personal teams that this user owns/belongs to are removed with them;
            // shared teams are left intact (only the user's membership is dropped).
            $personalTeamIds = Team::query()
                ->where('is_personal', true)
                ->whereIn('id', Membership::query()->where('user_id', $user->id)->pluck('team_id'))
                ->pluck('id');

            Membership::query()->where('user_id', $user->id)->delete();
            Membership::query()->whereIn('team_id', $personalTeamIds)->delete();
            Team::query()->whereIn('id', $personalTeamIds)->delete();

            $user->delete();
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'User deleted.']);

        return back();
    }

    /**
     * @return array<int, array{value: string, label: string, description: string}>
     */
    private function roleOptions(): array
    {
        return array_map(fn (UserRole $role) => [
            'value' => $role->value,
            'label' => $role->label(),
            'description' => $role->description(),
        ], UserRole::cases());
    }
}
