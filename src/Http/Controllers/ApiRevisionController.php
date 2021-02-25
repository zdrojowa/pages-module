<?php

namespace Selene\Modules\PagesModule\Http\Controllers;

use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Selene\Modules\PagesModule\Models\Revision;

class ApiRevisionController extends Controller
{
    public function get(Request $request): JsonResponse
    {
        return response()->json([
            'revisions' => Revision::getByContent(
                $request->get('table'),
                $request->get('contentId')),
            'users' => User::all(['id', 'name'])->pluck('name', 'id')
        ]);
    }

    public function update(Revision $revision): JsonResponse
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

    public function remove(Revision $revision): JsonResponse
    {
        try {
            $revision->delete();
            return response()->json(['message' => 'Wersja usunięta']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()],JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}
