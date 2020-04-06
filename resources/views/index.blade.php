@extends('DashboardModule::base')

@section('title','Dashboard')

@section('stylesheets')
    @parent
@endsection

@section('sidebar')
    @include('DashboardModule::sidebar.index', ['menu' => Selene\Support\Facades\MenuRepository::getPresences()])
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header clearfix">
                        <h4 class="card-title float-left">Dostępne strony</h4>
                        <a href="{{route('PagesModule::add')}}" class="text-success float-right">
                            <i class="mdi mdi-plus-circle"></i> Dodaj
                        </a>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped"></table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascripts')
    @parent

    <script>

        $('.table').zdrojowaTable({
            ajax: {
                url: "{{route('PagesModule::pages')}}",
                method: "POST",
                data: {
                    "_token": "{{csrf_token()}}"
                },
            },
            headers: [
                {
                    name: 'Tytuł',
                    type: 'text',
                    ajax: 'name',
                    orderable: true,
                },
                {
                    name: 'Status',
                    type: 'select',
                    ajax: 'status',
                    orderable: false,
                    select: [
                        ['draft', 'draft'],
                        ['public', 'public']
                    ]
                },
                {
                    name: 'Akcje',
                    ajax: 'id',
                    type: 'actions',
                    buttons: [
                    @permission('PagesModule.pages')
                        {
                            color: 'primary',
                            icon: 'mdi mdi-pencil',
                            class: 'remove',
                            url: "{{route('PagesModule::edit', ['page' => '%%id%%'])}}"
                        },
                    @endpermission
                    @permission('PagesModule.pages')
                        {
                            color: 'danger',
                            icon: 'mdi mdi-delete',
                            class: 'ZdrojowaTable--remove-action',
                            url: "{{route('PagesModule::destroy', ['page' => '%%id%%'])}}"
                        },
                    @endpermission
                    ]
                }
            ]
        });
    </script>
@endsection

