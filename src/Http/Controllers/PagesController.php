<?php

namespace Selene\Modules\PagesModule\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Selene\Modules\DashboardModule\ZdrojowaTable;
use Selene\Modules\PagesModule\Models\Page;
use Selene\Modules\PagesModule\Support\Status;
use Selene\Modules\PagesModule\Support\Type;
use Selene\Modules\SettingsModule\Models\Setting;

class PagesController extends Controller {

    public function index()
    {
        return view('PagesModule::index');
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
            $pages->where('name', 'like', '%' . $request->get('query', '') . '%')
                ->orWhere('_id', '=', $request->get('query', '') )
                ->where('_id', '!=', $request->get('qid', 0));
        }

        return response()->json($pages->get());
    }

    public function add() {
        return view('PagesModule::edit');
    }

    public function edit(Page $page) {
        return view('PagesModule::edit', ['page' => $page]);
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
            $request->session()->flash('alert-success', 'Strona została zaktualizowana');
        } else {
            $request->session()->flash('alert-error', 'Ooops. Try again.');
        }

        return ['redirect' => route('PagesModule::edit', ['page' => $page])];
    }

    private function save(Request $request, Page $page = null) :? Page
    {
        $obj = json_decode($request->get('obj'), true, 512, JSON_THROW_ON_ERROR);

        $obj['status'] = $obj['status']['id'];
        $obj['parent'] = $obj['parent']['id'];
        $obj['lang']   = $obj['lang']['key'];
        $obj['type']   = $obj['type']['id'];

        $request->merge($obj);

        if ($page === null) {
            return Page::create($request->all());
        }
        if ($page->update($request->all())) {
            return $page;
        }
        return null;
    }

    public function destroy(Page $page, Request $request): void
    {
        try {
            $page->delete();
            $request->session()->flash('alert-success', 'Strona została usunęta');
        } catch (\Exception $e) {
            $request->session()->flash('alert-error', 'Error: ' . $e->getMessage());
        }
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
            'page' => $page,
            'settings' => Setting::getAllByKey()
        ]);
    }

    public function statuses(): JsonResponse
    {
        $statuses = [];
        foreach (Status::toArray() as $key => $value) {
            $statuses[] = ['id' => $value, 'name' => $key];
        }
        return response()->json($statuses);
    }

    public function types(): JsonResponse
    {
        $types = [];
        foreach (Type::toArray() as $key => $value) {
            $types[] = ['id' => $value, 'name' => $key];
        }
        return response()->json($types);
    }


    public function check($id, Request $request): JsonResponse
    {
        $pages = Page::query()->where('_id', '!=', $id);

        if ($request->has('permalink')) {
            $pages->where('permalink', '=', $request->get('permalink'));
        }
        return response()->json(!$pages->exists());
    }
}