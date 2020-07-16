<?php

namespace Selene\Modules\PagesModule\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Selene\Modules\DashboardModule\ZdrojowaTable;
use Selene\Modules\PagesModule\Models\Page;
use Selene\Modules\PagesModule\Models\Icon;

class IconsController extends Controller {

    public function index(Request $request)
    {
        return view('PagesModule::icons.index');
    }

    public function get(Request $request) {
        $icons = Icon::query();

        if ($request->isMethod('POST')) {
            return ZdrojowaTable::response($icons, $request);
        }

        if ($request->has('id')) {
            $icons->where('_id', '=', $request->get('id'));

            return response()->json($icons->first());
        }

        return response()->json($icons->get());
    }

    public function add() {
        return view('PagesModule::icons.edit');
    }

    public function edit(Icon $icon) {
        return view('PagesModule::icons.edit', [
            'icon' => $icon
        ]);
    }

    public function store(Request $request) {
        $icon = $this->save($request);
        if ($icon) {
            $request->session()->flash('alert-success', 'Ikonka została utworzona');
        } else {
            $request->session()->flash('alert-error', 'Ooops. Try again.');
        }

        return ['redirect' => route('PagesModule::editIcon', ['icon' => $icon])];
    }

    public function update(Request $request, Icon $icon) {

        if ($this->save($request, $icon)) {
            $request->session()->flash('alert-success', 'Ikonka została zaktualizowana');
        } else {
            $request->session()->flash('alert-error', 'Ooops. Try again.');
        }

        return ['redirect' => route('PagesModule::editIcon', ['icon' => $icon])];
    }

    private function save(Request $request, Icon $icon = null) :? Icon
    {
        if ($icon === null) {
            $icon = Icon::create($request->all());
        } else {
            if (!$icon->update($request->all())) {
                return null;
            }
        }

        return $icon;
    }

    public function destroy(Icon $icon, Request $request)
    {
        try {
            $icon->delete();

            $request->session()->flash('alert-success', 'Ikonka została usunęta');
        } catch (\Exception $e) {
            $request->session()->flash('alert-error', 'Error: ' . $e->getMessage());
        }
    }
}
