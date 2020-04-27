<?php

namespace Selene\Modules\PagesModule\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Selene\Modules\DashboardModule\ZdrojowaTable;
use Selene\Modules\LanguageModule\Models\Language;
use Selene\Modules\PagesModule\Models\Section;
use Selene\Modules\SettingsModule\Models\Setting;

class SectionsController extends Controller {

    public function index(Request $request)
    {
        $sections = Section::query()->orderByDesc('_id');

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

        $sections->where('lang', '=', $lang);

        $name = $request->get('name', '');
        if (!empty($name)) {
            $sections->where('name', 'LIKE', '%' . $name . '%');
        }

        return view('PagesModule::sections.index', [
            'sections' => $sections->paginate(50, ['*'], 'page', $request->get('page') ?? 1),
            'langs'    => Language::all(),
            'lang'     => $lang,
            'name'     => $name
        ]);
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

        if ($request->has('lang')) {
            $sections->where('lang', '=', $request->get('lang'));
        }

        return response()->json($sections->get());
    }

    public function add() {
        return view('PagesModule::sections.edit');
    }

    public function edit(Section $section) {
        return view('PagesModule::sections.edit', [
            'section' => $section,
            'lang'    => $section->lang
        ]);
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

        if ($request->has('translation')) {
            $translationFrom = $request->get('translation');

            if ($translationFrom !== $section->_id) {
                $from = Section::where('_id', '=', $translationFrom)->first();
                if ($from) {
                    $translations = json_decode($from->translations, true);
                    if (empty($translations)) {
                        $translations = [];
                    }
                    $translations[] = $section->_id;
                    $from->translations = json_encode($translations);
                    $from->save();

                    $translations[] = $from->_id;
                    foreach ($translations as $tr) {
                        $trs = Section::where('_id', '=', $tr)->first();
                        if ($trs) {
                            $trs->translations = json_encode(array_values(array_diff($translations, [$tr])));
                            $trs->save();
                        }
                    }
                }
            }
        }

        return $section;
    }

    public function destroy(Section $section, Request $request)
    {
        try {
            if (!empty($section->translations)) {
                $translations = array_diff(json_decode($section->translations, true), [$section->_id]);

                foreach ($translations as $id) {
                    $translation = Section::where('_id', '=', $id)->first();
                    if ($translation) {
                        $translation->translations = json_encode(array_values(array_diff($translations, [$id])));
                        $translation->save();
                    }
                }
            }

            $section->delete();

            $request->session()->flash('alert-success', 'Sekcja została usunęta');
        } catch (\Exception $e) {
            $request->session()->flash('alert-error', 'Error: ' . $e->getMessage());
        }
    }

    public function addTranslation(Section $section, $lang) {
        return view('PagesModule::sections.edit', ['section' => $section, 'lang' => $lang]);
    }
}
