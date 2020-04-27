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

    protected $fillable = ['name', 'lang', 'template', 'type', 'count', 'translations'];

    public function getTranslations() {
        $translations = [];
        if (!empty($this->translations)) {
            foreach (json_decode($this->translations) as $id) {
                $item = self::query()->where('_id', '=', $id)->first();
                if ($item) {
                    $translations[$item->lang] = $item->id;
                }
            }
        }
        return $translations;
    }

    public function getType() {
        return Type::query()->where('template', '=', $this->type)->first();
    }
}
