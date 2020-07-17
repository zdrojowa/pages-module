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
            <b-card no-body>
                <b-tabs card>
                    <b-tab active>
                        <template v-slot:title>
                            <b-icon-card-text></b-icon-card-text> Strona
                        </template>
                        @if (isset($page))
                            <editor :_id=`{{ $page->_id }}` :lang=`{{ $lang }}`>
                                {{ csrf_field() }}
                            </editor>
                        @else
                            <editor :_id="0">
                                {{ csrf_field() }}
                            </editor>
                        @endif
                    </b-tab>
                    @if(!$new)
                        <b-tab>
                            <template v-slot:title>
                                <b-icon-image></b-icon-image> Hiro
                            </template>
                            <hiro :_id=`{{ $page->_id }}`>
                                {{ csrf_field() }}
                            </hiro>
                        </b-tab>
                        <b-tab>
                            <template v-slot:title>
                                <b-icon-images></b-icon-images> Galeria
                            </template>
                            <gallery :id=`{{ $page->_id }}`>
                                {{ csrf_field() }}
                            </gallery>
                        </b-tab>
                        <b-tab>
                            <template v-slot:title>
                                <b-icon-brightness-high></b-icon-brightness-high> Highlights
                            </template>
                            <highlights :id=`{{ $page->_id }}` :lang=`{{ $lang }}`>
                                {{ csrf_field() }}
                            </highlights>
                        </b-tab>
                        <b-tab>
                            <template v-slot:title>
                                <b-icon-columns></b-icon-columns> Sekcje
                            </template>
                            <page-section :id=`{{ $page->_id }}`>
                                {{ csrf_field() }}
                            </page-section>
                        </b-tab>
                        <b-tab>
                            <template v-slot:title>
                                <b-icon-clock-history></b-icon-clock-history> History
                            </template>
                            @include('RevisionModule::revisions', [
                                    'table'      => 'pages',
                                    'content_id' => $page->id
                                ])
                        </b-tab>
                    @endif
                </b-tabs>
            </b-card>
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
