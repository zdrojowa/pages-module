<?php

namespace Selene\Modules\PagesModule\Support;

use MyCLabs\Enum\Enum;

class Status extends Enum
{
    protected const DRAFT  = 'draft';
    protected const PUBLIC = 'public';
}
