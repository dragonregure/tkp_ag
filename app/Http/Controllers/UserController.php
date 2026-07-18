<?php

namespace App\Http\Controllers;

use App\Contracts\UserRepositoryInterface;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(private readonly UserRepositoryInterface $users)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('users.index');
    }

    public function data(): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        return $this->users->dataTableResponse();
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.form', [
            'user' => new User(),
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $this->users->create($request->validated());

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('users.form', [
            'user' => $user->load('roles'),
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $this->users->update($user, $request->validated());

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        if ($user->is(Auth::user())) {
            return back()->with('error', 'User yang sedang login tidak bisa dihapus.');
        }

        $this->users->delete($user);

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}
