<?php

namespace App\Http\View\Composers;

use App\Support\AdminNav;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminLayoutComposer
{
    public function compose(View $view): void
    {
        $user = Auth::user();

        $view->with([
            'adminHeaderTitle' => AdminNav::headerTitle($user),
            'adminRoleBadge' => AdminNav::roleBadge($user),
            'adminIsHeroPage' => AdminNav::isHeroPage(),
            'adminSidebarSubtitle' => AdminNav::sidebarSubtitle($user),
        ]);
    }
}
