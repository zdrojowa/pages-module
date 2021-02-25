<?php

namespace Selene\Modules\PagesModule\Models;

use Illuminate\Support\Facades\DB;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property mixed template
 */
class Type extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'types';

    protected $appends = ['id'];
    protected $hidden  = ['_id'];

    protected $primaryKey = '_id';

    protected $fillable = ['name', 'template', 'table', 'text'];

    public function remove(): bool
    {
        try {
            DB::beginTransaction();

            DB::connection('mongodb')
                ->table('pages')
                ->where('type', '=', $this->template)
                ->update(['type' => 'main', 'object' => null]);

            if (!$this->delete()) {
                throw new \Exception('Cannot delete type');
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}