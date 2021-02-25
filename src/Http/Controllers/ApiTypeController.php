<?php

namespace Selene\Modules\PagesModule\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use Selene\Modules\PagesModule\Models\Page;
use Selene\Modules\PagesModule\Models\Type;

class ApiTypeController extends Controller
{
    public function get(Request $request): JsonResponse
    {
        $types = Type::query()->orderBy('updated_at');

        if ($request->has('select')) {
            $select = explode(',', $request->get('select'));
            $types->select($select);
        }

        if ($request->has('id')) {
            $types->where('_id', '=', $request->get('id'));
            return response()->json($types->first());
        }

        if ($request->has('qid')) {
            $types->where('_id', '!=', $request->get('qid'));
        }

        if ($request->has('query')) {
            $types->where('name', 'LIKE', '%' . $request->get('query') . '%');
        }

        if ($request->has('per_page')) {
            return response()->json(
                $types->paginate(
                    $request->get('per_page') >> 0,
                    ['*'],
                    'page',
                    $request->get('page', 1)
                )
            );
        }

        return response()->json($types->get());
    }

    public function check(Request $request): JsonResponse
    {
        if ($request->has('table')) {
            return response()->json(Schema::connection('mongodb')->hasCollection($request->get('table')));
        }

        $types = Type::query();

        if ($request->has('id')) {
            $types->where('_id', '!=', $request->get('id'));
        }
        if ($request->has('name')) {
            $types->where('name', '=', $request->get('name'));
        }
        if ($request->has('template')) {
            $types->where('template', '=', $request->get('template'));
        }

        return response()->json(!$types->exists());
    }

    public function store(Request $request): JsonResponse
    {
        $type = $this->save($request);
        if ($type) {
            return response()->json([
                'redirect' => route('PagesModule::types.edit', ['type' => $type])
            ]);
        }

        return response()->json([
            'message' => 'Oops. Try again'
        ]);
    }

    public function update(Request $request, Type $type): JsonResponse
    {
        if ($this->save($request, $type)) {
            return response()->json([
                'redirect' => route('PagesModule::types.edit', ['type' => $type])
            ]);
        }

        return response()->json([
            'message' => 'Oops. Try again'
        ]);
    }

    private function save(Request $request, Type $type = null) :? Type
    {
        foreach ($request->all() as $key => $val) {
            if ($val === 'null' || $val == '') {
                $request->merge([$key => null]);
            }
        }

        if ($type === null) {
            $type = Type::query()->create($request->all());
        } else {
            $template = $type->template;

            if (!$type->update($request->all())) {
                return null;
            }

            if ($type->wasChanged('template')) {
                Page::query()
                    ->where('type', '=', $template)
                    ->update(['type' => $request->get('template')]);
            }
        }

        return $type;
    }

    public function remove(Type $type): JsonResponse
    {
        try {
            if (!$type->remove()) {
                throw new \Exception('Cannot remove type');
            }
            return response()->json(['message' => 'Typ usunięty']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()],JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}
