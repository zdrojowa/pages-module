<?php

namespace Selene\Modules\PagesModule\Http\Controllers;

use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Selene\Modules\PagesModule\Models\Menu;
use Selene\Modules\PagesModule\Models\Page;
use Selene\Modules\PagesModule\Models\Revision;

class ApiController extends Controller
{
    public function get(Request $request): JsonResponse
    {
        $pages = Page::query()->orderBy('updated_at');

        if ($request->has('status')) {
            $pages->where('status', '=', $request->get('status'));
        }

        if ($request->has('select')) {
            $select = explode(',', $request->get('select'));
            $pages->select($select);
        }

        if ($request->has('id')) {
            $pages->where('_id', '=', $request->get('id'));
            return response()->json($pages->first());
        }

        if ($request->has('qid')) {
            $pages->where('_id', '!=', $request->get('qid'));
        }

        if ($request->has('lang')) {
            $pages->where('lang', '=', $request->get('lang'));
        }

        if ($request->has('type')) {
            $pages->where('type', '=', $request->get('type'));
        }

        if ($request->has('object')) {
            $pages->where('object', '=', $request->get('object'));
            return response()->json($pages->first());
        }

        if ($request->has('query')) {
            $pages->where('name', 'LIKE', '%' . $request->get('query') . '%');
        }

        if ($request->has('parent')) {
            $pages->where('parent', '=', $request->get('parent'));
        }

        if ($request->has('per_page')) {
            return response()->json(
                $pages->paginate(
                    $request->get('per_page') >> 0,
                    ['*'],
                    'page',
                    $request->get('page', 1)
                )
            );
        }

        if ($request->has('with_translations')) {
            $pagesWithTranslations = [];
            /** @var Page $page */
            foreach ($pages->get() as $page) {
                $pageWithTranslation = $page;
                $pageWithTranslation['translations'] = $page->getTranslations();
                $pagesWithTranslations[] = $pageWithTranslation;
            }
            response()->json($pagesWithTranslations);
        }

        return response()->json($pages->get());
    }

    public function check(Request $request): JsonResponse
    {
        $lang = $request->get('lang');
        if (empty($lang)) {
            return response()->json([
                'message' => 'Language is required'
            ],JsonResponse::HTTP_BAD_REQUEST);
        }

        $slug      = $request->get('slug');
        $parent    = $request->get('parent');
        $permalink = Page::generatePermalink($lang, $slug, $parent);

        $exists = Page::query()->where('permalink', '=', $permalink);
        if ($request->has('id')) {
            $exists->where('_id', '!=', $request->get('id'));
        }
        return response()->json(!$exists->exists());
    }

    public function store(Request $request): JsonResponse
    {
        $page = $this->save($request);
        if ($page) {
            return response()->json([
                'redirect' => route('PagesModule::edit', ['page' => $page])
            ]);
        }

        return response()->json([
            'message' => 'Oops. Try again'
        ]);
    }

    public function update(Request $request, Page $page): JsonResponse
    {
        if ($this->save($request, $page)) {
            Menu::changeMenu($page, Menu::ACTION_UPDATE, $request->user()->id);
            return response()->json([
                'redirect' => route('PagesModule::edit', ['page' => $page])
            ]);
        }

        return response()->json([
            'message' => 'Oops. Try again'
        ]);
    }

    public function addTranslation(Page $page, $lang, Request $request): JsonResponse
    {
        if (!$page) {
            return response()->json([
                'message' => 'Oops. Try again'
            ]);
        }

        $translations = $page->translations ?? [];

        $data = $page->toArray();
        unset($data['id']);
        $data['lang'] = $lang;
        $data['parent'] = null;
        $data['permalink'] = Page::generatePermalink($lang, Page::getSlug($lang, $page->permalink));
        $data['status'] = 'draft';
        $data['translations'] = $translations;
        $data['translations'][$page->lang] = $page->id;

        $newPage = Page::query()->create($data);

        $newPage->refresh();

        Revision::createRevision('pages', 'created', $newPage, $request->user()->id);

        foreach ($translations as $translationLang => $translation) {
            $tr = Page::query()->find($translation);
            if ($tr) {
                $pageTranslations = $tr->translations ?? [];
                $pageTranslations[$lang] = $newPage->id;
                $tr->translations = $pageTranslations;
                $tr->save();
            }
        }

        $translations[$lang] = $newPage->id;

        $page->translations = $translations;
        $page->save();

        return response()->json([
            'message' => 'Ok',
            'redirect' => route('PagesModule::edit', ['page' => $newPage])
        ]);
    }

    private function save(Request $request, Page $page = null) :? Page
    {
        foreach ($request->all() as $key => $val) {
            if ($val === 'null' || $val == '') {
                $request->merge([$key => null]);
            }
        }

        if ($request->has('parent')) {
            $parent = $request->get('parent');
            if (empty($parent)) {
                $request->merge(['parent' => null]);
            }
            $request->merge([
                'permalink' => Page::generatePermalink(
                    $request->get('lang'),
                    $request->get('slug'),
                    $request->get('parent')
                )
            ]);
        }

        if ($request->has('video') || $request->has('images')) {
            $hiro = ['video' => null, 'images' => []];
            $video = $request->get('video');
            if (!empty($video)) {
                $hiro['video'] = $video;
            }
            $images = $request->get('images');
            if (!empty($images)) {
                $images = json_decode($images, false, 512, JSON_THROW_ON_ERROR);
            }
            foreach ($images as $image) {
                if (isset($image->type, $image->file->_id)) {
                    $hiro['images'][] = ['id' => $image->file->_id, 'type' => $image->type];
                }
            }
            $request->merge(['hiro' => $hiro]);
        }

        $fields = ['image', 'tags', 'gallery', 'highlights'];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $value = $request->get($field);
                if (!empty($value)) {
                    $value = json_decode($value, false, 512, JSON_THROW_ON_ERROR);
                }
                if (empty($value)) {
                    $request->merge([$field => null]);
                } else {
                    $request->merge([$field => $value]);
                }
            }
        }

        $action = 'updated';
        if ($page === null) {
            $page = Page::query()->create($request->all());
            $action = 'created';
        }
        if (!$page->update($request->all())) {
            return null;
        }

        $page->refresh();

        Revision::createRevision('pages', $action, $page, $request->user()->id);

        return $page;
    }

    public function objects(Request $request): JsonResponse
    {
        if (!$request->has('table')) {
            return response()->json([
                'message' => 'Table is required'
            ],JsonResponse::HTTP_BAD_REQUEST);
        }

        $objects = DB::connection('mongodb')
            ->table($request->get('table'))
            ->orderBy('name')
            ->select(['_id', 'name']);

        if ($request->has('query')) {
            $query = $request->get('query', '');

            if (!empty($query)) {
                $objects->where('name', 'like', '%' . $query . '%')
                    ->orWhere('_id', '=', $query);
            }
        }

        return response()->json($objects->get());
    }

    public function remove(Page $page): JsonResponse
    {
        try {
            if ($page->translations) {
                foreach ($page->translations as $id) {
                    /** @var Page $translation */
                    $translation = Page::query()->find($id);
                    if ($translation) {
                        $translations = $translation->translations;
                        if (isset($translations[$page->lang])) {
                            unset($translations[$page->lang]);
                        }
                        $translation->translations = $translations;
                        $translation->save();
                    }
                }
            }

            $userId = Auth::id();

            Menu::changeMenu($page, Menu::ACTION_DELETE, $userId);

            $page->delete();

            Revision::query()
                ->create([
                'table' => 'pages',
                'action' => 'deleted',
                'content_id' => $page->_id,
                'content' => null,
                'created_at' => now(),
                'user_id' => $userId
            ]);
            return response()->json(['message' => 'Strona usunięta']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()],JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    public function revisions(Request $request): JsonResponse
    {
        return response()->json([
            'revisions' => Revision::getByContent(
                $request->get('table'),
                $request->get('contentId')),
            'users' => User::all(['id', 'name'])->pluck('name', 'id')
        ]);
    }

    public function updateRevision(Revision $revision): JsonResponse
    {
        try {
            $content = DB::connection('mongodb')
                ->table($revision->table)
                ->where('_id', '=', $revision->content_id);

            if ($content->first()) {
                $content->update(json_decode($revision->content, true, 512, JSON_THROW_ON_ERROR));
            } else {
                $content->insert(json_decode($revision->content, true, 512, JSON_THROW_ON_ERROR));
            }
            return response()->json([
                'message' => 'Wersja zaktualizowana',
                'redirect' => route('PagesModule::edit', ['page' => $revision->content_id])
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()],JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    public function removeRevision(Revision $revision): JsonResponse
    {
        try {
            $revision->delete();
            return response()->json(['message' => 'Wersja usunięta']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()],JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}
