@extends('DashboardModule::dashboard.index', ['title' => 'Menu'])

@section('navbar-links')
    <b-nav-item href="{{ route('PagesModule::index') }}">Strony</b-nav-item>
    <b-nav-item href="{{ route('PagesModule::types') }}">Typy</b-nav-item>
@endsection

@section('navbar-actions')
    <b-nav-form>
        <b-button size="sm" class="my-2 my-sm-0" type="button" variant="success" to="{{ route('PagesModule::menu.create') }}">
            <b-icon-plus></b-icon-plus> Dodaj
        </b-button>
    </b-nav-form>
@endsection

@section('content')
    <b-container fluid>
        <menu-index
            route="{{ route('PagesModule::api.menu') }}"
            edit-route="{{ route('PagesModule::menu.edit', ['menu' => 'id']) }}"
            remove-route="{{ route('PagesModule::api.menu.remove', ['menu' => 'id']) }}"
            translation-route="{{ route('PagesModule::api.menu.addTranslation', ['menu' => 'id', 'lang' => 'lang']) }}"
            csrf="{{ csrf_token() }}"
            :langs="{{ json_encode($langs) }}"
        >
        </menu-index>
    </b-container>
@endsection

@section('stylesheets')
    @parent
    <link rel="stylesheet" href="{{ mix('vendor/css/PagesModule.css') }}">
@endsection

@section('javascripts')
    <script src="{{ mix("vendor/js/PagesModule.js") }}"></script>
@endsection