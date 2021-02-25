<?php

namespace Selene\Modules\PagesModule\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Selene\Modules\PagesModule\Models\Menu;
use Selene\Modules\PagesModule\Models\Page;
use Selene\Modules\PagesModule\Models\Revision;

class ApiMenuController extends Controller {

    public function get(Request $request): JsonResponse
    {
        $menu = Menu::query();

        if ($request->has('id')) {
            $menu->where('_id', '=', $request->get('id'));
            return response()->json($menu->first());
        }

        if ($request->has('query')) {
            $query = $request->get('query', '');

            if (!empty($query)) {
                $menu->where('name', 'like', '%' . $query . '%')
                    ->orWhere('_id', '=', $query);
            }
        }

        if ($request->has('qid')) {
            $menu->where('_id', '!=', $request->get('qid'));
        }

        if ($request->has('lang')) {
            $menu->where('lang', '=', $request->get('lang'));
        }

        return response()->json($menu->get());
    }

    public function check(Request $request): JsonResponse
    {
        $menu = Menu::query();

        if ($request->has('lang')) {
            $menu->where('lang', '=', $request->get('lang'));
        }
        if ($request->has('name')) {
            $menu->where('name', '=', $request->get('name'));
        }
        if ($request->has('id')) {
            $menu->where('_id', '!=', $request->get('id'));
        }
        return response()->json(!$menu->exists());
    }

    public function store(Request $request): JsonResponse
    {
        $menu = $this->save($request);
        if ($menu) {
            return response()->json([
                'redirect' => route('PagesModule::menu.edit', ['menu' => $menu])
            ]);
        }

        return response()->json([
            'message' => 'Oops. Try again'
        ]);
    }

    public function update(Request $request, Menu $menu): JsonResponse
    {
        if ($this->save($request, $menu)) {
            return response()->json([
                'redirect' => route('PagesModule::menu.edit', ['menu' => $menu])
            ]);
        }

        return response()->json([
            'message' => 'Oops. Try again'
        ]);
    }

    private function save(Request $request, Menu $menu = null) :? Menu
    {
        foreach ($request->all() as $key => $val) {
            if ($val === 'null' || $val == '') {
                $request->merge([$key => null]);
            }
        }

        if ($request->has('structure')) {
            $value = $request->get('structure');
            if (!empty($value)) {
                $value = json_decode($value, false, 512, JSON_THROW_ON_ERROR);
            }
            if (empty($value)) {
                $request->merge(['structure' => []]);
            } else {
                $request->merge(['structure' => $value]);
            }
        }

        $action = 'updated';
        if ($menu === null) {
            $menu = Menu::query()->create($request->all());
            $action = 'created';
        }
        if (!$menu->update($request->all())) {
            return null;
        }

        $menu->refresh();

        Revision::add('menu', $action, $menu, $request->user()->id);

        return $menu;
    }

    public function remove(Menu $menu): JsonResponse
    {
        try {
            if (!$menu->remove()) {
                throw new \Exception('Cannot remove menu');
            }
            return response()->json(['message' => 'Menu jest usunięte']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()],JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    public function addTranslation(Menu $menu, $lang, Request $request): JsonResponse
    {
        if (!$menu) {
            return response()->json([
                'message' => 'Oops. Try again'
            ]);
        }

        $translations = $menu->translations ?? [];

        $newMenu = $menu
            ->replicate()
            ->fill([
                'lang' => $lang,
                'translations' => array_merge($translations, [$menu->lang => $menu->id]),
                'structure' => $this->changeMenu($menu->structure ?? [], $lang)
            ]);

        $newMenu->save();

        Revision::add('menu', 'created', $newMenu, $request->user()->id);

        foreach ($translations as $translationLang => $translation) {
            /** @var Menu $tr */
            $tr = Menu::query()->find($translation);
            if ($tr) {
                $pageTranslations = $tr->translations ?? [];
                $pageTranslations[$lang] = $newMenu->id;
                $tr->translations = $pageTranslations;
                $tr->save();
            }
        }

        $translations[$lang] = $newMenu->id;

        $menu->translations = $translations;
        $menu->save();

        return response()->json([
            'message' => 'Ok',
            'redirect' => route('PagesModule::menu.edit', ['menu' => $newMenu])
        ]);
    }

    private function changeMenu($structure, $lang) {
        foreach ($structure as $i => $item) {
            if ($item['type'] === 'page') {
                /** @var Page $page */
                $page = Page::query()->find($item['page']);
                if ($page && isset($page->translations[$lang])) {
                    $structure[$i]['page'] = $page->translations[$lang];
                } else {
                    unset($structure[$i]);
                    continue;
                }
            }
            $structure[$i]['items'] = $this->changeMenu($item['items'], $lang);
        }
        return $structure;
    }
}
