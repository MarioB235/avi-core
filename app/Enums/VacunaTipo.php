<?php

namespace App\Enums;

enum VacunaTipo: string
{
    case Newcastle = 'newcastle';
    case Bronquitis = 'bronquitis';
    case Gumboro = 'gumboro';
    case Encefalomielitis = 'encefalomielitis';
    case Pox = 'pox';

    public function label(): string
    {
        return match ($this) {
            self::Newcastle => 'Newcastle (La Sota)',
            self::Bronquitis => 'Bronquitis infecciosa',
            self::Gumboro => 'Gumboro (IBD)',
            self::Encefalomielitis => 'Encefalomielitis aviar',
            self::Pox => 'Pox aviar',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $tipo): array => [$tipo->value => $tipo->label()])
            ->all();
    }
}
