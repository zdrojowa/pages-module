<?php

namespace Selene\Modules\PagesModule\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Type extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'types';

    protected $appends = ['id'];
    protected $hidden  = ['_id'];

    protected $primaryKey = '_id';

    protected $fillable = ['name', 'template', 'table', 'text'];
}
