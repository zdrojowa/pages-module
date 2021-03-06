<?php

namespace Selene\Modules\PagesModule\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jenssegers\Mongodb\Eloquent\Model;
use Selene\Modules\SettingsModule\Models\Setting;

/**
 * @property mixed type
 * @property mixed object
 * @property mixed lang
 * @property mixed permalink
 * @property mixed id
 * @property mixed _id
 * @property mixed translations
 */
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
        'title',
        'lead',
        'content',
        'parent',
        'hiro',
        'translations',
        'object',
        'gallery',
        'highlights',
        'meta_description',
        'tags',
        'priority',
    ];

    public function getTranslations() {
        $translations = [];
        if (!empty($this->translations)) {
            foreach ($this->translations as $id) {
                /** @var Page $page */
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
            foreach ($this->translations as $id) {
                /** @var Page $page */
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
        if ($type && $type->table_name) {
            return DB::connection('mongodb')
                ->table($type->table_name)
                ->where('_id', '=', $this->object)
                ->first();
        }
        return null;
    }

    public function remove(): bool
    {
        try {
            DB::beginTransaction();

            if ($this->translations) {
                foreach ($this->translations as $id) {
                    /** @var Page $translation */
                    $translation = self::query()->find($id);
                    if ($translation) {
                        $translations = $translation->translations;
                        if (isset($translations[$this->lang])) {
                            unset($translations[$this->lang]);
                        }
                        $translation->translations = $translations;
                        $translation->save();
                    }
                }
            }

            $userId = Auth::id();

            Menu::changeMenu($this, $userId);

            $this->delete();

            Revision::query()
                ->create([
                    'table' => 'pages',
                    'action' => 'deleted',
                    'content_id' => $this->_id,
                    'content' => null,
                    'created_at' => now(),
                    'user_id' => $userId
                ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function generatePermalink($lang, $slug, $parent = null): string
    {
        $permalink = '/';
        $mainLang = Setting::query()->where('key', '=', 'lang')->first()->value ?? 'pl';
        if ($mainLang !== $lang) {
            $permalink .= $lang . '/';
        }
        if ($parent) {
            /** @var Page $page */
            $page = self::query()->find($parent);
            if ($page) {
                $permalink = $page->permalink . '/';
            }
        }
        if ($slug) {
            $permalink .= $slug;
        }
        $permalink = str_replace('//', '/', $permalink);
        if ($permalink === '/') {
            return $permalink;
        }
        return rtrim($permalink, '/');
    }

    public static function getSlug($lang, $permalink): string
    {
        $index = strrpos($permalink, '/');
        if ($index === FALSE) {
            return '';
        }
        $slug = substr($permalink, $index + 1);
        if ($slug === $lang) {
            return '';
        }
        return $slug;
    }
}
