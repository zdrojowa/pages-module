<?php

namespace Selene\Modules\PagesModule\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Icon extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'icons';

    protected $appends = ['id'];
    protected $hidden  = ['_id'];

    protected $primaryKey = '_id';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'url',
    ];
}
