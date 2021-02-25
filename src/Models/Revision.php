<?php

namespace Selene\Modules\PagesModule\Models;

use App\User;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string table
 * @property mixed content_id
 * @property mixed content
 */
class Revision extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'revisions';

    protected $appends = ['id'];
    protected $hidden  = ['_id'];

    protected $primaryKey = '_id';

    protected $fillable = [
        'table',
        'action',
        'content_id',
        'content',
        'created_at',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getLastAuto($table, $contentId) {
        return self::query()->where('table', '=', $table)
            ->where('content_id', '=', $contentId)
            ->where('action', '=', 'auto')
            ->orderByDesc('_id')
            ->first();
    }

    public static function getByContent($table = null, $contentId = null) {
        $revisions = self::query();

        if (!empty($table)) {
            $revisions->where('table', '=', $table);
        }

        if (!empty($contentId)) {
            $revisions->where('content_id', '=', $contentId);
        }

        if (!empty($table) && !empty($contentId)) {
            $auto = self::getLastAuto($table, $contentId);
            if ($auto) {
                $revisions->where('created_at', '>', $auto->created_at);
            }
        }

        $revisions->orderByDesc('created_at');

        return $revisions->get();
    }

    public static function add($table, $action, $content, $userId) {
        unset($content->translations, $content->parent, $content->object, $content->type);

        return self::query()->create([
            'table' => $table,
            'action' => $action,
            'content_id' => $content->id,
            'content' => json_encode($content),
            'created_at' => now(),
            'user_id' => $userId
        ]);
    }
}
