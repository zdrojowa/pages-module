<?php

namespace Selene\Modules\PagesModule\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Selene\Modules\LanguageModule\Models\Language;
use Selene\Modules\PagesModule\Models\Menu;
use Selene\Modules\PagesModule\Support\MenuType;

class MenuController extends Controller {

    public function index(Request $request)
    {
        return view('PagesModule::menu.index', [
            'langs' => Language::getAllSelect()
        ]);
    }

    public function create() {
        return view('PagesModule::menu.edit', [
            'langs' => Language::getAllSelect(),
            'types' => MenuType::getSelect()
        ]);
    }

    public function edit(Menu $menu) {
        return view('PagesModule::menu.edit', [
            'menu'  => $menu,
            'langs' => Language::getAllSelect(),
            'types' => MenuType::getSelect()
        ]);
    }
}
