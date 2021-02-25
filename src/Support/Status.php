<?php

namespace Selene\Modules\PagesModule\Support;

use MyCLabs\Enum\Enum;

class Status extends Enum
{
    protected const DRAFT  = 'draft';
    protected const PUBLIC = 'public';

    public static function getSelect(): array
    {
        $statuses = [];
        foreach (self::toArray() as $key => $value) {
            $statuses[] = ['value' => $value, 'text' => $key];
        }
        return $statuses;
    }
}
