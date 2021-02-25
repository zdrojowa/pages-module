@extends('DashboardModule::dashboard.index', ['title' => 'Typy'])

@section('navbar-links')
    <b-nav-item href="{{ route('PagesModule::index') }}">Strony</b-nav-item>
    <b-nav-item href="{{ route('PagesModule::menu') }}">Menu</b-nav-item>
@endsection

@section('navbar-actions')
    <b-nav-form>
        <b-button size="sm" class="my-2 my-sm-0" type="button" variant="success" to="{{ route('PagesModule::types.create') }}">
            <b-icon-plus></b-icon-plus> Dodaj
        </b-button>
    </b-nav-form>
@endsection

@section('content')
    <b-container fluid>
        <types-index
                route="{{ route('PagesModule::api.types') }}"
                edit-route="{{ route('PagesModule::types.edit', ['type' => 'id']) }}"
                remove-route="{{ route('PagesModule::api.types.remove', ['type' => 'id']) }}"
                csrf="{{ csrf_token() }}"
        >
        </types-index>
    </b-container>
@endsection

@section('javascripts')
    <script src="{{ mix("vendor/js/PagesModule.js") }}"></script>
@endsection