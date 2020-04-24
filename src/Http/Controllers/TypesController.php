<?php

namespace Selene\Modules\PagesModule\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Selene\Modules\DashboardModule\ZdrojowaTable;
use Selene\Modules\PagesModule\Models\Type;

class TypesController extends Controller {

    public function index(Request $request)
    {
        return view('PagesModule::types.index');
    }

    public function get(Request $request) {
        $types = Type::query();

        if ($request->isMethod('POST')) {
            return ZdrojowaTable::response($types, $request);
        }

        if ($request->has('id')) {
            $types->where('_id', '=', $request->get('id'));

            return response()->json($types->first());
        }

        return response()->json($types->get());
    }

    public function add() {
        return view('PagesModule::types.edit');
    }

    public function edit(Type $type) {
        return view('PagesModule::types.edit', [
            'type' => $type
        ]);
    }

    public function store(Request $request) {
        $type = $this->save($request);
        if ($type) {
            $request->session()->flash('alert-success', 'Typ został utworzony');
        } else {
            $request->session()->flash('alert-error', 'Ooops. Try again.');
        }

        return ['redirect' => route('PagesModule::editType', ['type' => $type])];
    }

    public function update(Request $request, Type $type) {

        if ($this->save($request, $type)) {
            $request->session()->flash('alert-success', 'Typ został zaktualizowany');
        } else {
            $request->session()->flash('alert-error', 'Ooops. Try again.');
        }

        return ['redirect' => route('PagesModule::editType', ['type' => $type])];
    }

    private function save(Request $request, Type $type = null) :? Type
    {
        if ($type === null) {
            $type = Type::create($request->all());
        }
        if (!$type->update($request->all())) {
            return null;
        }

        return $type;
    }

    public function destroy(Type $type, Request $request)
    {
        try {
            $type->delete();

            $request->session()->flash('alert-success', 'Typ został usunęty');
        } catch (\Exception $e) {
            $request->session()->flash('alert-error', 'Error: ' . $e->getMessage());
        }
    }

    public function check($id, Request $request): JsonResponse
    {
        if ($request->has('table')) {
            return response()->json(Schema::connection('mongodb')->hasCollection($request->get('table')));
        }

        $types = Type::query()->where('_id', '!=', $id);

        if ($request->has('name')) {
            $types->where('name', '=', $request->get('name'));
        }

        if ($request->has('template')) {
            $types->where('template', '=', $request->get('template'));
        }

        return response()->json(!$types->exists());
    }
}
