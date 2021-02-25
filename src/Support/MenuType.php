<?php

namespace Selene\Modules\PagesModule\Support;

use MyCLabs\Enum\Enum;

class MenuType extends Enum
{
    protected const PAGE = 'page';
    protected const LINK = 'link';

    public static function getSelect(): array
    {
        $types = [];
        foreach (self::toArray() as $key => $value) {
            $types[] = ['value' => $value, 'text' => $key];
        }
        return $types;
    }
}
