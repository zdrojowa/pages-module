<?php

namespace Selene\Modules\PagesModule\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use Selene\Modules\PagesModule\Http\PagesSectionsOrders;
use Selene\Modules\PagesModule\Http\Section;

class Page extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'pages';

    protected $appends = ['id'];
    protected $hidden  = ['_id'];

    protected $primaryKey = '_id';

    protected $fillable = [
        'status',
        'permalink',
        'lang',
        'type',
        'image',
        'name',
        'content',
        'translation',
        'parent',
        'hiro_video',
        'hiro_images'
    ];

    //
    //public function pageSections() {
    //    return $this->belongsToMany(Section::class, null, 'id', 'sections');
    //    //return $this->hasMany(Section::class, 'id', 'sections');
    //}

//    public function sections() {
//        return $this->belongsToMany(Section::class, null, 'pageIds', 'sectionIds');
//        //return $this->belongsToMany(Section::class, null, 'pageIds', 'sectionIds');
//    }
//
//    public function getSection($sectionId)
//    {
//        return $this->where('pageId', 1);
//    }

    //public function pagesSectionsOrders()
    //{
    //    return $this->hasOne(PagesSectionsOrders::class, 'sectionId', 'pageSections');
    //}
}
