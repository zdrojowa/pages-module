<?php

namespace Selene\Modules\PagesModule\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Selene\Modules\DashboardModule\ZdrojowaTable;
use Selene\Modules\LanguageModule\Models\Language;
use Selene\Modules\MenuModule\Models\Menu;
use Selene\Modules\PagesModule\Models\Page;
use Selene\Modules\PagesModule\Models\Translation;
use Selene\Modules\PagesModule\Support\Status;
use Selene\Modules\RevisionModule\Models\Revision;
use Selene\Modules\RevisionModule\Support\Action;
use Selene\Modules\SettingsModule\Models\Setting;

class PagesController extends Controller {

    public function index(Request $request)
    {
        $pages = Page::query()->orderByDesc('_id');

        if ($request->has('lang')) {
            $lang = $request->get('lang', 'pl');
        } else {
            $setting = Setting::query()
                ->where('key', '=', 'lang')
                ->first();
            if ($setting) {
                $lang = $setting->value;
            } else {
                $lang = 'pl';
            }
        }

        $pages->where('lang', '=', $lang);

        $name = $request->get('name', '');
        if (!empty($name)) {
            $pages->where('name', 'LIKE', '%' . $name . '%');
        }

        return view('PagesModule::index', [
            'pages' => $pages->paginate(50, ['*'], 'page', $request->get('page') ?? 1),
            'langs' => Language::all(),
            'lang'  => $lang,
            'name'  => $name
        ]);
    }

    public function get(Request $request) {
        $pages = Page::query();

        if ($request->isMethod('POST')) {
            return ZdrojowaTable::response($pages, $request);
        }

        if ($request->has('id')) {
            $pages->where('_id', '=', $request->get('id'));
            return response()->json($pages->first());
        }

        if ($request->has('query')) {
            $query = $request->get('query', '');

            if (!empty($query)) {
                $pages->where('name', 'like', '%' . $query . '%')
                    ->orWhere('_id', '=', $query);
            }
            $pages->where('_id', '!=', $request->get('qid', 0));
        }

        if ($request->has('lang')) {
            $pages->where('lang', '=', $request->get('lang'));
        }

        if ($request->has('type')) {
            $pages->where('type', '=', $request->get('type'));
        }

        if ($request->has('status')) {
            $pages->where('status', '=', $request->get('status'));
        }

        if ($request->has('per_page')) {
            return response()->json(
                $pages->paginate(
                    $request->get('per_page') >> 0,
                    ['*'],
                    'page',
                    $request->get('page') ?? 1
                )
            );
        }

        return response()->json($pages->get());
    }

    public function add() {
        return view('PagesModule::edit', ['new' => true]);
    }

    public function edit(Page $page) {
        return view('PagesModule::edit', [
            'page' => $page,
            'lang' => $page->lang,
            'new'  => false
        ]);
    }

    public function store(Request $request) {
        $page = $this->save($request);
        if ($page) {
            $request->session()->flash('alert-success', 'Strona została utworzona');
        } else {
            $request->session()->flash('alert-error', 'Ooops. Try again.');
        }

        return ['redirect' => route('PagesModule::edit', ['page' => $page])];
    }

    public function update(Request $request, Page $page) {

        if ($this->save($request, $page)) {
            Menu::changeMenu($page, Menu::ACTION_UPDATE, $request->user()->id);
            $request->session()->flash('alert-success', 'Strona została zaktualizowana');
        } else {
            $request->session()->flash('alert-error', 'Ooops. Try again.');
        }

        return ['redirect' => route('PagesModule::edit', ['page' => $page])];
    }

    private function save(Request $request, Page $page = null) :? Page
    {
        if ($request->has('obj')) {
            $obj = json_decode($request->get('obj'), true, 512, JSON_THROW_ON_ERROR);

            $obj['status'] = $obj['status']['id'];
            $obj['parent'] = $obj['parent']['id'];
            $obj['lang']   = $obj['lang']['key'];
            $obj['type']   = isset($obj['type']['template']) ? $obj['type']['template'] : 'main';
            $obj['object'] = $obj['object']['id'] ?? null;

            if ($page !== null) {
                unset($obj['hiro_video'], $obj['hiro_images'], $obj['sections'], $obj['gallery']);
            }

            $request->merge($obj);
        }

        if ($request->has('hiro_video')) {
            if($request->get('hiro_video') === 'null') {
                $request->merge(['hiro_video' => null]);
            }
        }

        if ($request->has('sections')) {
            $request->merge([
                'sections' => json_decode($request->get('sections'), true, 512, JSON_THROW_ON_ERROR)
            ]);
        }

        if ($request->has('gallery')) {
            $request->merge([
                'gallery' => json_decode($request->get('gallery'), true, 512, JSON_THROW_ON_ERROR)
            ]);
        }

        $action = 'updated';
        if ($page === null) {
            $page = Page::create($request->all());
            $action = 'created';
        }
        if (!$page->update($request->all())) {
            return null;
        }

        if ($request->has('translation')) {
            $translationFrom = $request->get('translation');

            if ($translationFrom !== $page->_id) {
                $from = Page::where('_id', '=', $translationFrom)->first();
                if ($from) {
                    $translations = json_decode($from->translations, true);
                    if (empty($translations)) {
                        $translations = [];
                    }
                    $translations[] = $page->_id;
                    $from->translations = json_encode($translations);
                    $from->save();

                    $translations[] = $from->_id;
                    foreach ($translations as $tr) {
                        $trPage = Page::where('_id', '=', $tr)->first();
                        if ($trPage) {
                            $trPage->translations = json_encode(array_values(array_diff($translations, [$tr])));
                            $trPage->save();
                        }
                    }
                }
            }
        }

        $page->refresh();

        Revision::create([
            'table' => 'pages',
            'action' => $action,
            'content_id' => $page->id,
            'content' => json_encode($page),
            'created_at' => now(),
            'user_id' => $request->user()->id
        ]);
        return $page;
    }

    public function destroy(Page $page, Request $request)
    {
        try {

            $id = $page->_id;

            if (!empty($page->translations)) {
                $translations = array_diff(json_decode($page->translations, true), [$page->_id]);

                foreach ($translations as $id) {
                    $translation = Page::where('_id', '=', $id)->first();
                    if ($translation) {
                        $translation->translations = json_encode(array_values(array_diff($translations, [$id])));
                        $translation->save();
                    }
                }
            }

            $userId = $request->user()->id;

            Menu::changeMenu($page, Menu::ACTION_DELETE, $userId);

            $page->delete();

            Revision::create([
                'table' => 'pages',
                'action' => 'deleted',
                'content_id' => $id,
                'content' => null,
                'created_at' => now(),
                'user_id' => $userId
            ]);

            $request->session()->flash('alert-success', 'Strona została usunęta');
        } catch (\Exception $e) {
            $request->session()->flash('alert-error', 'Error: ' . $e->getMessage());
        }
    }

    public function statuses(): JsonResponse
    {
        $statuses = [];
        foreach (Status::toArray() as $key => $value) {
            $statuses[] = ['id' => $value, 'name' => $key];
        }
        return response()->json($statuses);
    }

    public function check($id, Request $request): JsonResponse
    {
        $pages = Page::query()->where('_id', '!=', $id);

        if ($request->has('permalink')) {
            $pages->where('permalink', '=', $request->get('permalink'));
        }
        return response()->json(!$pages->exists());
    }

    public function addTranslation(Page $page, $lang) {
        return view('PagesModule::edit', ['page' => $page, 'lang' => $lang, 'new' => true]);
    }

    public function getObjects(Request $request) {

        if (!$request->has('table')) {
            return response()->json([]);
        }

        $objects = \DB::connection('mongodb')
            ->collection($request->get('table'))
            ->limit(10)
            ->orderBy('_id');

        if ($request->has('query')) {
            $query = $request->get('query', '');

            if (!empty($query)) {
                $objects->where('name', 'like', '%' . $query . '%')
                    ->orWhere('_id', '=', $query);
            }
        }

        $items = [];
        foreach ($objects->get() as $object) {
            $items[] = [
                'id'   => (string) $object['_id'],
                'name' => $object['name']
            ];
        }

        return response()->json($items);
    }

    public function page(Request $request, $permalink = '/') {
        $isAdmin = false;
        if (Auth::check() && Auth::user()->admin) {
            $isAdmin = true;
        }

        $pageBuilder = Page::query()
            ->where('permalink', '=', $permalink)
            ->orWhere('permalink', '=', '/' . $permalink);

        if (!$isAdmin) {
            $pageBuilder->where('status', '=', 'public');
        }

        $page = $pageBuilder->first();

        if (!$page) {
            abort(404);
        }

        return view($page->type, [
            'page'      => $page,
            'settings'  => Setting::getAllByKey(),
            'menu'      => Menu::getByLang($page->lang)
        ]);
    }
}
