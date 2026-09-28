<?php

namespace App\Actions\User;

use App\Actions\Auditoria\RegistrarAuditoriaAction;
use App\Enums\AuditoriaCategoria;
use App\Models\User;
use App\Services\Auth\UserSessionService;
use App\Services\TemporaryPasswordGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ResetUserPasswordAction
{
    public function __construct(
        private TemporaryPasswordGenerator $passwords,
        private UserSessionService $sessions,
        private RegistrarAuditoriaAction $auditoria,
    ) {}

    /**
     * @return array{user: User, plainPassword: string}
     */
    public function execute(User $actor, User $target): array
    {
        Gate::forUser($actor)->authorize('resetPassword', $target);

        $plainPassword = $this->passwords->generate();

        DB::transaction(function () use ($actor, $target, $plainPassword): void {
            $target->forceFill([
                'password' => $plainPassword,
                'must_change_password' => true,
            ])->save();

            $this->auditoria->execute(
                $actor,
                AuditoriaCategoria::Usuario,
                'password_reseteado',
                User::class,
                $target->id,
                $target->empresa_id,
                metadata: [
                    'documento' => $target->documento,
                    'plainPassword' => $plainPassword,
                ],
            );

            $this->sessions->invalidateAllForUser($target);
        });

        return [
            'user' => $target->refresh(),
            'plainPassword' => $plainPassword,
        ];
    }
}
