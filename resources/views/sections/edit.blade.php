@extends('DashboardModule::base')

@section('title','Dashboard')

@section('stylesheets')
    <link rel="stylesheet" href="{{ mix('vendor/css/PagesModule.css','') }}">
@endsection

@section('sidebar')
    @include('DashboardModule::sidebar.index', ['menu' => Selene\Support\Facades\MenuRepository::getPresences()])
@endsection

@section('content')
    <div class="content-wrapper">
        <div id="app">
            @if (isset($section))
                <edit-section :_id=`{{ $section->_id }}` :_lang=`{{ $lang }}`>
                    {{ csrf_field() }}
                </edit-section>
            @else
                <edit-section :_id="0">
                    {{ csrf_field() }}
                </edit-section>
            @endif
        </div>
    </div>
@endsection

@section('javascripts')
    @parent
    @javascript('csrf', csrf_token())
    <script src="{{ mix('vendor/js/PagesModule.js') }}"></script>
@endsection
