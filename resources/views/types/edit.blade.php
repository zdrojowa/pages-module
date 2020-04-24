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
            @if (isset($type))
                <type :_id=`{{ $type->_id }}`>
                    {{ csrf_field() }}
                </type>
            @else
                <type :_id="0">
                    {{ csrf_field() }}
                </type>
            @endif
        </div>
    </div>
@endsection

@section('javascripts')
    @parent
    @javascript('csrf', csrf_token())
    <script src="{{ mix('vendor/js/PagesModule.js') }}"></script>
@endsection
