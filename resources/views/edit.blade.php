@extends('DashboardModule::dashboard.index', ['title' => (isset($page) ? 'Edytowanie' : 'Dodawanie') . ' strony'])

@section('navbar-links')
    <b-nav-item href="{{ route('PagesModule::index') }}">Strony</b-nav-item>
    <b-nav-item href="{{ route('PagesModule::types') }}">Typy</b-nav-item>
    <b-nav-item href="{{ route('PagesModule::menu') }}">Menu</b-nav-item>
@endsection

@section('navbar-actions')
    @isset($page)
        <b-nav-item href="{{ $page->permalink }}" target="_blank">
            <b-button variant="info">
                <b-icon-arrow-up-right-square></b-icon-arrow-up-right-square> Podgląd
            </b-button>
        </b-nav-item>
    @endisset
@endsection

@section('content')
    <b-container fluid>
        <page-tab
            :page="{{ isset($page) ? json_encode($page) : json_encode(null) }}"
            :statuses="{{ json_encode($statuses) }}"
            :types="{{ json_encode($types) }}"
            :languages="{{ json_encode($langs) }}"
            csrf="{{ csrf_token() }}"
            route="{{ isset($page) ? route('PagesModule::api.update', ['page' => $page]) : route('PagesModule::api.store') }}"
            media-search-route="{{ route('MediaModule::api.files') }}"
            media-route='/media/'
            check-route="{{ route('PagesModule::api.check') }}"
            page-route="{{ route('PagesModule::api.pages') }}"
            object-route="{{ route('PagesModule::api.objects') }}"
            icon-route="{{ route('IconModule::api.icons') }}"
            revision-route="{{ route('PagesModule::api.revisions') }}"
            revision-update-route="{{ route('PagesModule::api.revisions.update', ['revision' => 'id']) }}"
            revision-remove-route="{{ route('PagesModule::api.revisions.remove', ['revision' => 'id']) }}"
        >
        </page-tab>
    </b-container>
@endsection

@section('stylesheets')
    @parent
    <link rel="stylesheet" href="{{ mix('vendor/css/MediaModule.css') }}">
    <link rel="stylesheet" href="{{ mix('vendor/css/PagesModule.css') }}">
@endsection

@section('javascripts')
    <script src="{{ mix("vendor/js/PagesModule.js") }}"></script>
@endsection
