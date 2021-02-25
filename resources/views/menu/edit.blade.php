@extends('DashboardModule::dashboard.index', ['title' => (isset($menu) ? 'Edytowanie' : 'Dodawanie') . ' menu'])

@section('navbar-links')
    <b-nav-item href="{{ route('PagesModule::index') }}">Strony</b-nav-item>
    <b-nav-item href="{{ route('PagesModule::types') }}">Typy</b-nav-item>
    <b-nav-item href="{{ route('PagesModule::menu') }}">Menu</b-nav-item>
@endsection

@section('content')
    <b-container fluid>
        <menu-tab
            :menu="{{ isset($menu) ? json_encode($menu) : json_encode(null) }}"
            :languages="{{ json_encode($langs) }}"
            :types="{{ json_encode($types) }}"
            csrf="{{ csrf_token() }}"
            route="{{ isset($menu) ? route('PagesModule::api.menu.update', ['menu' => $menu]) : route('PagesModule::api.menu.store') }}"
            check-route="{{ route('PagesModule::api.menu.check') }}"
            page-route="{{ route('PagesModule::api.pages') }}"
            revision-route="{{ route('PagesModule::api.revisions') }}"
            revision-update-route="{{ route('PagesModule::api.revisions.update', ['revision' => 'id']) }}"
            revision-remove-route="{{ route('PagesModule::api.revisions.remove', ['revision' => 'id']) }}"
        >
        </menu-tab>
    </b-container>
@endsection

@section('stylesheets')
    @parent
    <link rel="stylesheet" href="{{ mix('vendor/css/PagesModule.css') }}">
@endsection

@section('javascripts')
    <script src="{{ mix("vendor/js/PagesModule.js") }}"></script>
@endsection
