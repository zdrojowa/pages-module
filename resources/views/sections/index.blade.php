@extends('DashboardModule::dashboard.index')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header clearfix">
                        <h4 class="card-title float-left">Lista sekcji</h4>
                        <a href="{{route('PagesModule::addSection')}}" class="text-success float-right">
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
                url: "{{route('PagesModule::getSections')}}",
                method: "POST",
                data: {
                    "_token": "{{csrf_token()}}"
                },
            },
            headers: [
                {
                    name: 'Nazwa',
                    type: 'text',
                    ajax: 'name',
                    orderable: true,
                },
                {
                    name: 'Szablon',
                    type: 'text',
                    ajax: 'template',
                    orderable: true,
                },
                {
                    name: 'Typ',
                    type: 'text',
                    ajax: 'type',
                    orderable: true
                },
                {
                    name: 'Akcje',
                    ajax: 'key',
                    type: 'actions',
                    buttons: [
                    @permission('PagesModule.pages')
                        {
                            color: 'primary',
                            icon: 'mdi mdi-pencil',
                            class: 'remove',
                            url: "{{route('PagesModule::editSection', ['section' => '%%id%%'])}}"
                        },
                        {
                            color: 'danger',
                            icon: 'mdi mdi-delete',
                            class: 'ZdrojowaTable--remove-action',
                            url: "{{route('PagesModule::destroySection', ['section' => '%%id%%'])}}"
                        },
                    @endpermission
                    ]
                }
            ]
        });
    </script>
@endsection
