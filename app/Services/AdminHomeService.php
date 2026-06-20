<?php

namespace App\Services;

use App\Models\User;

class AdminHomeService
{
    public function for(User $user): AdminHomeViewData
    {
        return new AdminHomeViewData(
            user: $user,
            contextLabel: $this->contextLabel($user),
            activeUsersCount: $this->activeUsersCount($user),
            setupItems: $this->setupItems(),
        );
    }

    public function contextLabel(User $user): string
    {
        $empresa = $user->empresa?->nombre ?? 'AviCore';

        return "{$empresa} · {$user->rol->label()}";
    }

    public function activeUsersCount(User $user): int
    {
        $query = User::query()->where('activo', true);

        if ($user->isAdminAvicore()) {
            return $query->count();
        }

        return $query
            ->where('empresa_id', $user->empresa_id)
            ->count();
    }

    /**
     * @return list<array{label: string, description: string, icon: string}>
     */
    public function setupItems(): array
    {
        return [
            [
                'label' => 'Granjas',
                'description' => 'Registrá las unidades productivas de tu empresa.',
                'icon' => 'building',
            ],
            [
                'label' => 'Galpones',
                'description' => 'Definí los galpones donde se registra la operación.',
                'icon' => 'warehouse',
            ],
            [
                'label' => 'Usuarios',
                'description' => 'Invitá al equipo y asigná roles de acceso.',
                'icon' => 'users',
            ],
        ];
    }
}

readonly class AdminHomeViewData
{
    /**
     * @param  list<array{label: string, description: string, icon: string}>  $setupItems
     */
    public function __construct(
        public User $user,
        public string $contextLabel,
        public int $activeUsersCount,
        public array $setupItems,
    ) {}
}
