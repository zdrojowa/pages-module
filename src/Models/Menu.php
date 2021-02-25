<?php

namespace Selene\Modules\PagesModule\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property mixed lang
 * @property mixed translations
 * @property mixed id
 */
class Menu extends Model
{
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';

    protected $connection = 'mongodb';
    protected $collection = 'menu';

    protected $appends = ['id'];
    protected $hidden  = ['_id'];

    protected $primaryKey = '_id';

    protected $fillable = [
        'lang',
        'name',
        'translations',
        'structure'
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

    public function remove(): bool
    {
        try {
            DB::beginTransaction();

            if ($this->translations) {
                foreach ($this->translations as $id) {
                    /** @var Menu $translation */
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

            $this->delete();

            Revision::query()
                ->create([
                    'table' => 'menu',
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

    public static function getByLang(string $lang) {
        $menus = [];
        foreach (self::query()->where('lang', '=', $lang)->get() as $menu) {
            $menus[$menu->name] = $menu->structure;
        }
        return $menus;
    }

    public static function getByName(string $lang, string $name) {
        $menu = self::query()->where('lang', '=', $lang)
            ->where('name', '=', $name)
            ->first();

        return $menu ? $menu->structure : [];
    }

    public static function getByPage($id) {
        return self::query()
            ->where('structure.page', '=', $id)
            ->orWhere('structure.items.page', '=', $id)
            ->orWhere('structure.items.items.page', '=', $id)
            ->orWhere('structure.items.items.items.page', '=', $id)
            ->get();
    }

    public static function changeMenu(Page $page, $userId) {
        foreach (self::getByPage($page->id) as $menu) {
            $menu->structure = self::changeStructure($menu->structure, $page);
            if ($menu->save()) {
                Revision::query()->create([
                    'table'      => 'menu',
                    'action'     => 'auto',
                    'content_id' => $menu->id,
                    'content'    => json_encode($menu),
                    'created_at' => now(),
                    'user_id'    => $userId
                ]);
            }
        }
    }

    protected static function changeStructure($structure, Page $page) {
        foreach ($structure as $i => $item) {
            if (isset($item['page']) && $item['page'] === $page->id) {
                unset($structure[$i]);
            }
            if (isset($item['items'])) {
                $structure[$i]['items'] = self::changeStructure($item['items'], $page);
            }
        }
        return $structure;
    }
}
