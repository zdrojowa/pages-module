@extends('DashboardModule::dashboard.index', ['title' => (isset($type) ? 'Edytowanie' : 'Dodawanie') . ' typu'])

@section('navbar-links')
    <b-nav-item href="{{ route('PagesModule::index') }}">Strony</b-nav-item>
    <b-nav-item href="{{ route('PagesModule::types') }}">Typy</b-nav-item>
    <b-nav-item href="{{ route('PagesModule::menu') }}">Menu</b-nav-item>
@endsection

@section('content')
    <b-container fluid>
        <type
            :type="{{ isset($type) ? json_encode($type) : json_encode(null) }}"
            csrf="{{ csrf_token() }}"
            route="{{ isset($type) ? route('PagesModule::api.types.update', ['type' => $type]) : route('PagesModule::api.types.store') }}"
            check-route="{{ route('PagesModule::api.types.check') }}"
        >
        </type>
    </b-container>
@endsection

@section('javascripts')
    <script src="{{ mix("vendor/js/PagesModule.js") }}"></script>
@endsection
