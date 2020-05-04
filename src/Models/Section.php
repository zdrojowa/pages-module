<?php

namespace Selene\Modules\PagesModule\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Section extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'sections';

    protected $appends = ['id'];
    protected $hidden  = ['_id'];

    protected $primaryKey = '_id';

    protected $fillable = ['name', 'template', 'type', 'count'];

    public function getType() {
        return Type::query()->where('template', '=', $this->type)->first();
    }
}