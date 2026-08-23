<?php

namespace App\Support\Concerns;

trait MapsNavTabsToTabBar
{
    /**
     * @return list<array{href: string, label: string, icon: string, active: bool}>
     */
    public static function tabBarItems(): array
    {
        return array_map(
            fn (array $tab): array => [
                'href' => route($tab['route']),
                'label' => $tab['label'],
                'icon' => $tab['icon'],
                'active' => static::tabIsActive($tab),
            ],
            static::tabs(),
        );
    }
}
