<?php

namespace Selene\Modules\PagesModule\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Selene\Modules\HotelModule\Models\Hotels;
use Selene\Modules\PagesModule\Models\Icon;
use Selene\Modules\PagesModule\Models\Page;

class ApiController extends Controller
{
    public function page(Request $request): JsonResponse
    {
        $pages = Page::query()
            ->where('status', '=', 'public')
            ->orderBy('updated_at');

        if ($request->has('id')) {
            $pages->where('_id', '=', $request->get('id'));
            return response()->json($pages->first());
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

        return response()->json($pages->get());
    }

    public function getIcons(Request $request): JsonResponse
    {
        $icons = Icon::query()->orderBy('updated_at');

        if ($request->has('id')) {
            $icons->where('_id', '=', $request->get('id'));
            return response()->json($icons->first());
        }

        if ($request->has('per_page')) {
            return response()->json(
                $icons->paginate(
                    $request->get('per_page') >> 0,
                    ['*'],
                    'page',
                    $request->get('page', 1)
                )
            );
        }

        return response()->json($icons->get());
    }
}
