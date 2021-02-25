<?php

namespace Selene\Modules\PagesModule\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Selene\Modules\LanguageModule\Models\Language;
use Selene\Modules\PagesModule\Models\Menu;
use Selene\Modules\PagesModule\Models\Page;
use Selene\Modules\PagesModule\Models\Type;
use Selene\Modules\PagesModule\Support\Status;
use Selene\Modules\SettingsModule\Models\Setting;

class PagesController extends Controller {

    public function index()
    {
        $langs   = Language::getAllSelect();
        $parents = [];
        foreach ($langs as $lang) {
            $parents[$lang['value']] = [];
        }

        $parentsIds = Page::query()->whereNotNull('parent')->pluck('parent');
        foreach (Page::query()->whereIn('_id', $parentsIds)->orderBy('name')->get() as $page) {
            $parents[$page->lang][] = ['value' => $page->_id, 'text' => $page->name];
        }

        return view('PagesModule::index', [
            'langs'   => $langs,
            'parents' => $parents
        ]);
    }

    public function create() {
        return view('PagesModule::edit', [
            'langs'    => Language::getAllSelect(),
            'statuses' => Status::getSelect(),
            'types'    => Type::all()
        ]);
    }

    public function edit(Page $page) {
        return view('PagesModule::edit', [
            'page'     => $page,
            'langs'    => Language::getAllSelect(),
            'statuses' => Status::getSelect(),
            'types'    => Type::all()
        ]);
    }

    public function page($permalink = '/') {
        $pageBuilder = Page::query()
            ->where('permalink', '=', $permalink)
            ->orWhere('permalink', '=', '/' . $permalink);

        if (!Auth::check()) {
            $pageBuilder->where('status', '=', 'public');
        }

        /** @var Page $page */
        $page = $pageBuilder->first();

        if (!$page) {
            abort(404);
        }

        session()->put('locale', $page->lang);
        App::setLocale($page->lang);

        return view($page->type, [
            'page'      => $page,
            'settings'  => Setting::getAllByKey(),
            'menu'      => Menu::getByLang($page->lang)
        ]);
    }
}