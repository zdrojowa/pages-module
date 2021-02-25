<?php

namespace Selene\Modules\PagesModule\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Selene\Modules\PagesModule\Models\Type;

class TypesController extends Controller {

    public function index(Request $request)
    {
        return view('PagesModule::types.index');
    }

    public function create() {
        return view('PagesModule::types.edit');
    }

    public function edit(Type $type) {
        return view('PagesModule::types.edit', [
            'type' => $type
        ]);
    }
}
