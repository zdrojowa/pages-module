<?php

namespace Selene\Modules\PagesModule\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Page extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'pages';

    protected $appends = ['id'];
    protected $hidden  = ['_id'];

    protected $primaryKey = '_id';

    protected $fillable = [
        'status',
        'permalink',
        'lang',
        'type',
        'image',
        'name',
        'lead',
        'content',
        'parent',
        'hiro_video',
        'hiro_images',
        'translations',
        'object'
    ];

    public function getTranslations() {
        $translations = [];
        if (!empty($this->translations)) {
            foreach (json_decode($this->translations) as $id) {
                $page = self::query()->where('_id', '=', $id)->first();
                if ($page) {
                    $translations[$page->lang] = $page->id;
                }
            }
        }
        return $translations;
    }
    
    public function getType() {
        return Type::query()->where('template', '=', $this->type)->first();
    }
}
