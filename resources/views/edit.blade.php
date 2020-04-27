@extends('DashboardModule::base')

@section('title','Dashboard')

@section('stylesheets')
    <link rel="stylesheet" href="{{ mix('vendor/css/MediaManager.css','') }}">
    <link rel="stylesheet" href="{{ mix('vendor/css/RevisionModule.css','') }}">
    <link rel="stylesheet" href="{{ mix('vendor/css/PagesModule.css','') }}">
@endsection

@section('sidebar')
    @include('DashboardModule::sidebar.index', ['menu' => Selene\Support\Facades\MenuRepository::getPresences()])
@endsection

@section('content')
    <div class="content-wrapper">
        <div id="app">
            @if (isset($page))
                <editor :_id=`{{ $page->_id }}` :lang=`{{ $lang }}`>
                    {{ csrf_field() }}
                </editor>

                @if(!isset($new))
                    <hiro :_id=`{{ $page->_id }}`>
                        {{ csrf_field() }}
                    </hiro>

                    <page-section :id=`{{ $page->_id }}` :lang=`{{ $lang }}`>
                        {{ csrf_field() }}
                    </page-section>

                    <div class="row">
                        <div class="col-12 mt-2">
                            @include('RevisionModule::revisions', [
                                'table'      => 'pages',
                                'content_id' => $page->id
                            ])
                        </div>
                    </div>
                @endif
            @else
                <editor :_id="0">
                    {{ csrf_field() }}
                </editor>
            @endif
        </div>
    </div>
@endsection

@section('javascripts')
    @parent
    @javascript('csrf', csrf_token())
    @javascript('ajaxUpload', route('MediaManager::media.upload.ajax'))
    @javascript('infoUrl', route('MediaManager::media.image.info', ['media' => '%%id%%']))
    <script src="https://cdn.jsdelivr.net/npm/vanilla-lazyload@13.0.1/dist/lazyload.min.js"></script>
    <script src="{{ mix('vendor/js/MediaManager.js') }}"></script>
    <script src="{{ mix('vendor/js/RevisionModule.js') }}"></script>
    <script src="{{ mix('vendor/js/PagesModule.js') }}"></script>
@endsection
