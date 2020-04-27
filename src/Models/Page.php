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
        'object',
        'sections'
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

    public function getObject() {
        $type = $this->getType();
        if ($type->table) {
            return \DB::connection('mongodb')
                ->collection($type->table)
                ->where('_id', '=', $this->object)
                ->first();
        }
        return null;
    }

    public function getSections() {
        $sections = [];
        if (!empty($this->sections)) {
            foreach (json_decode($this->sections) as $item) {
                $section = Section::query()->where('_id', '=', $item->id)->first();
                if ($section) {
                    $sections[] = [
                        'section' => $section,
                        'name'    => $item->name,
                        'label'   => $item->label,
                    ];
                }
            }
        }
        return $sections;
    }
}
