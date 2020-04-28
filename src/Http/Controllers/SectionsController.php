<?php

namespace Selene\Modules\PagesModule\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Selene\Modules\DashboardModule\ZdrojowaTable;
use Selene\Modules\PagesModule\Models\Page;
use Selene\Modules\PagesModule\Models\Section;

class SectionsController extends Controller {

    public function index(Request $request)
    {
        return view('PagesModule::sections.index');
    }

    public function get(Request $request) {
        $sections = Section::query();

        if ($request->isMethod('POST')) {
            return ZdrojowaTable::response($sections, $request);
        }

        if ($request->has('id')) {
            $sections->where('_id', '=', $request->get('id'));

            return response()->json($sections->first());
        }

        if ($request->has('query')) {
            $query = $request->get('query', '');

            if (!empty($query)) {
                $sections->where('name', 'like', '%' . $query . '%')
                    ->orWhere('_id', '=', $query);
            }
            $sections->where('_id', '!=', $request->get('qid', 0));
        }

        return response()->json($sections->get());
    }

    public function add() {
        return view('PagesModule::sections.edit');
    }

    public function edit(Section $section) {
        return view('PagesModule::sections.edit', ['section' => $section]);
    }

    public function store(Request $request) {
        $section = $this->save($request);
        if ($section) {
            $request->session()->flash('alert-success', 'Sekcja została utworzona');
        } else {
            $request->session()->flash('alert-error', 'Ooops. Try again.');
        }

        return ['redirect' => route('PagesModule::editSection', ['section' => $section])];
    }

    public function update(Request $request, Section $section) {

        if ($this->save($request, $section)) {
            $request->session()->flash('alert-success', 'Sekcja została zaktualizowana');
        } else {
            $request->session()->flash('alert-error', 'Ooops. Try again.');
        }

        return ['redirect' => route('PagesModule::editSection', ['section' => $section])];
    }

    private function save(Request $request, Section $section = null) :? Section
    {
        if ($section === null) {
            $section = Section::create($request->all());
        }
        if (!$section->update($request->all())) {
            return null;
        }

        return $section;
    }

    public function destroy(Section $section, Request $request)
    {
        try {

            foreach (Page::getBySection($section->_id) as $page) {
                $sections = [];
                foreach ($page->sections as $pageSection) {
                    if ($pageSection['id'] !== $section->_id) {
                        $sections[] = $pageSection;
                    }
                }
                $page->sections = $sections;
                $page->save();
            }

            $section->delete();

            $request->session()->flash('alert-success', 'Sekcja została usunęta');
        } catch (\Exception $e) {
            $request->session()->flash('alert-error', 'Error: ' . $e->getMessage());
        }
    }
}
