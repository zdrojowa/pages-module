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
        'gallery',
        'highlights',
        'sections'
    ];

    public function getHeroImages() {
        if (!empty($this->hiro_images)) {
            if (is_string($this->hiro_images)) {
                return json_decode($this->hiro_images);
            }

            if (is_array($this->hiro_images)) {
                return $this->hiro_images;
            }
        }

        return [];
    }

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

    public function getPublicTranslations() {
        $translations = [];
        if (!empty($this->translations)) {
            foreach (json_decode($this->translations) as $id) {
                $page = self::query()
                    ->where('_id', '=', $id)
                    ->where('status', '=', 'public')
                    ->first();
                if ($page) {
                    $translations[$page->lang] = $page;
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
        if ($type->table_name) {
            return \DB::connection('mongodb')
                ->collection($type->table_name)
                ->where('_id', '=', $this->object)
                ->first();
        }
        return null;
    }

    public function getSections() {
        $sections = [];
        if (!empty($this->sections)) {
            foreach ($this->sections as $item) {
                $section = Section::query()->where('_id', '=', $item['id'])->first();
                if ($section) {
                    $items = [];
                    $count = $section->count >> 0;
                    if ($count > 0) {
                        $items = self::query()
                            ->where('type', '=', $section->type)
                            ->where('lang', '=', $this->lang)
                            ->where('status', '=', 'public')
                            ->orderByDesc('updated_at')
                            ->limit($count)
                            ->get();
                    }
                    $sections[] = [
                        'section' => $section,
                        'name'    => $item['name'],
                        'label'   => $item['label'],
                        'link'    => $item['link'],
                        'items'   => $items
                    ];
                }
            }
        }
        return $sections;
    }

    public static function getBySection($id) {
        return self::query()->where('sections.id', '=', $id)->get();
    }
}
