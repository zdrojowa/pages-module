<?php

namespace Selene\Modules\PagesModule\Support;

use MyCLabs\Enum\Enum;

class Action extends Enum
{
    protected const CREATED = 'created';
    protected const UPDATED = 'updated';
    protected const DELETED  = 'deleted';
    protected const AUTO    = 'auto';
}
