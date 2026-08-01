<?php

namespace App\Livewire\Admin\Usuarios;

use App\Actions\User\CreateUserAction;
use App\Actions\User\ResetUserPasswordAction;
use App\Actions\User\UpdateUserAction;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Usuarios · AviCore')]
class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $busqueda = '';

    public string $filtroRol = '';

    public string $filtroEstado = 'activos';

    public bool $dialogFormularioAbierto = false;

    public bool $dialogPasswordAbierto = false;

    public ?int $editingUserId = null;

    public string $name = '';

    public string $documento = '';

    public string $email = '';

    public string $rol = '';

    public string $empresa_id = '';

    public bool $activo = true;

    public string $plainPassword = '';

    public string $passwordUserName = '';

    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroRol(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function abrirCrear(): void
    {
        $this->authorize('create', User::class);
        $this->resetFormulario();
        $actor = auth()->user();
        $roles = $actor->rol->assignableRoles();
        $this->rol = $roles[0]->value ?? '';
        if (! $actor->isAdminAvicore() && $actor->empresa_id !== null) {
            $this->empresa_id = (string) $actor->empresa_id;
        }
        $this->dialogFormularioAbierto = true;
    }

    public function abrirEditar(int $userId): void
    {
        $target = $this->findScopedUser($userId);
        $this->authorize('update', $target);

        $this->editingUserId = $target->id;
        $this->name = $target->name;
        $this->documento = $target->documento;
        $this->email = (string) ($target->email ?? '');
        $this->rol = $target->rol->value;
        $this->empresa_id = $target->empresa_id !== null ? (string) $target->empresa_id : '';
        $this->activo = $target->activo;
        $this->dialogFormularioAbierto = true;
        $this->resetValidation();
    }

    public function cerrarFormulario(): void
    {
        $this->dialogFormularioAbierto = false;
        $this->resetFormulario();
    }

    public function guardar(CreateUserAction $createUser, UpdateUserAction $updateUser): void
    {
        if ($this->editingUserId !== null) {
            $target = $this->findScopedUser($this->editingUserId);
            $updateUser->execute(auth()->user(), $target, [
                'name' => $this->name,
                'documento' => $this->documento,
                'email' => $this->email !== '' ? $this->email : null,
                'rol' => $this->rol,
                'activo' => $this->activo,
            ]);

            $this->cerrarFormulario();
            $this->dispatch('snackbar-show', message: 'Usuario actualizado.', variant: 'success');

            return;
        }

        $result = $createUser->execute(auth()->user(), [
            'name' => $this->name,
            'documento' => $this->documento,
            'email' => $this->email !== '' ? $this->email : null,
            'rol' => $this->rol,
            'empresa_id' => $this->empresa_id !== '' ? (int) $this->empresa_id : null,
        ]);

        $this->cerrarFormulario();
        $this->mostrarPasswordTemporal($result['user']->name, $result['plainPassword']);
        $this->dispatch('snackbar-show', message: 'Usuario creado.', variant: 'success');
    }

    public function resetearPassword(int $userId, ResetUserPasswordAction $resetUserPassword): void
    {
        $target = $this->findScopedUser($userId);
        $result = $resetUserPassword->execute(auth()->user(), $target);
        $this->mostrarPasswordTemporal($result['user']->name, $result['plainPassword']);
        $this->dispatch('snackbar-show', message: 'Contraseña temporal generada.', variant: 'success');
    }

    public function toggleActivo(int $userId, UpdateUserAction $updateUser): void
    {
        $target = $this->findScopedUser($userId);
        $this->authorize('toggleActive', $target);

        $updateUser->execute(auth()->user(), $target, [
            'name' => $target->name,
            'documento' => $target->documento,
            'email' => $target->email,
            'rol' => $target->rol->value,
            'activo' => ! $target->activo,
        ]);

        $mensaje = $target->fresh()->activo ? 'Usuario activado.' : 'Usuario desactivado.';
        $this->dispatch('snackbar-show', message: $mensaje, variant: 'success');
    }

    public function cerrarPassword(): void
    {
        $this->dialogPasswordAbierto = false;
        $this->plainPassword = '';
        $this->passwordUserName = '';
    }

    public function render(): View
    {
        $actor = auth()->user();

        $query = User::query()
            ->with('empresa')
            ->orderBy('name');

        if (! $actor->isAdminAvicore()) {
            $query->where('empresa_id', $actor->empresa_id);
        }

        if ($this->busqueda !== '') {
            $term = '%'.$this->busqueda.'%';
            $query->where(function ($builder) use ($term): void {
                $builder
                    ->where('name', 'ilike', $term)
                    ->orWhere('documento', 'ilike', $term)
                    ->orWhere('email', 'ilike', $term);
            });
        }

        if ($this->filtroRol !== '') {
            $query->where('rol', $this->filtroRol);
        }

        if ($this->filtroEstado === 'activos') {
            $query->where('activo', true);
        } elseif ($this->filtroEstado === 'inactivos') {
            $query->where('activo', false);
        }

        $roleOptions = collect($actor->rol->assignableRoles())
            ->mapWithKeys(fn (UserRole $role): array => [$role->value => $role->label()])
            ->all();

        $filtroRolOptions = collect(UserRole::cases())
            ->filter(function (UserRole $role) use ($actor): bool {
                if ($actor->isAdminAvicore()) {
                    return true;
                }

                return $role !== UserRole::AdminAvicore;
            })
            ->mapWithKeys(fn (UserRole $role): array => [$role->value => $role->label()])
            ->all();

        $empresas = $actor->isAdminAvicore()
            ? Empresa::query()->orderBy('nombre')->get(['id', 'nombre'])
            : collect();

        return view('livewire.admin.usuarios.index', [
            'users' => $query->paginate(15),
            'actor' => $actor,
            'roleOptions' => $roleOptions,
            'filtroRolOptions' => $filtroRolOptions,
            'empresas' => $empresas,
            'canCreate' => Gate::forUser($actor)->allows('create', User::class),
        ]);
    }

    private function findScopedUser(int $userId): User
    {
        $actor = auth()->user();
        $query = User::query()->whereKey($userId);

        if (! $actor->isAdminAvicore()) {
            $query->where('empresa_id', $actor->empresa_id);
        }

        $user = $query->firstOrFail();
        $this->authorize('view', $user);

        return $user;
    }

    private function resetFormulario(): void
    {
        $this->editingUserId = null;
        $this->name = '';
        $this->documento = '';
        $this->email = '';
        $this->rol = '';
        $this->empresa_id = '';
        $this->activo = true;
        $this->resetValidation();
    }

    private function mostrarPasswordTemporal(string $userName, string $plainPassword): void
    {
        $this->passwordUserName = $userName;
        $this->plainPassword = $plainPassword;
        $this->dialogPasswordAbierto = true;
    }
}
