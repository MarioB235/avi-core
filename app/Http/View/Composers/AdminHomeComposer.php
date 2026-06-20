<?php

namespace App\Http\View\Composers;

use App\Services\AdminHomeService;
use Illuminate\View\View;

class AdminHomeComposer
{
    public function __construct(private AdminHomeService $adminHome) {}

    public function compose(View $view): void
    {
        $user = auth()->user();

        if ($user === null) {
            return;
        }

        $view->with('home', $this->adminHome->for($user));
    }
}
