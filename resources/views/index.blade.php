@extends('DashboardModule::dashboard.index', ['title' => 'Strony'])

@section('navbar-links')
    <b-nav-item href="{{ route('PagesModule::types') }}">Typy</b-nav-item>
    <b-nav-item href="{{ route('PagesModule::menu') }}">Menu</b-nav-item>
@endsection

@section('navbar-actions')
    <b-nav-form>
        <b-button size="sm" class="my-2 my-sm-0" type="button" variant="success" to="{{ route('PagesModule::create') }}">
            <b-icon-plus></b-icon-plus> Dodaj
        </b-button>
    </b-nav-form>
@endsection

@section('content')
    <b-container fluid>
        <page-index
                route="{{ route('PagesModule::api.pages') }}"
                edit-route="{{ route('PagesModule::edit', ['page' => 'id']) }}"
                remove-route="{{ route('PagesModule::api.remove', ['page' => 'id']) }}"
                translation-route="{{ route('PagesModule::api.addTranslation', ['page' => 'id', 'lang' => 'lang']) }}"
                csrf="{{ csrf_token() }}"
                :langs="{{ json_encode($langs) }}"
                :parents="{{ json_encode($parents) }}"
        >
        </page-index>
    </b-container>
@endsection

@section('stylesheets')
    @parent
    <link rel="stylesheet" href="{{ mix('vendor/css/PagesModule.css') }}">
@endsection

@section('javascripts')
    <script src="{{ mix("vendor/js/PagesModule.js") }}"></script>
@endsection