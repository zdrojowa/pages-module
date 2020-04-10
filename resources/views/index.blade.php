@extends('DashboardModule::base')

@section('title','Dashboard')

@section('stylesheets')
    @parent
    <link rel="stylesheet" href="{{ mix('vendor/css/LanguageModule.css','') }}">
    <link rel="stylesheet" href="{{ mix('vendor/css/PagesModule.css','') }}">
@endsection

@section('sidebar')
    @include('DashboardModule::sidebar.index', ['menu' => Selene\Support\Facades\MenuRepository::getPresences()])
@endsection

@section('content')
    <div class="content-wrapper" id="app">
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
    <script src="{{ mix('vendor/js/PagesModule.js') }}"></script>

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
                    name: 'Język',
                    type: 'text',
                    ajax: 'lang',
                    orderable: true
                },
                {
                    name: 'Tłumacznia',
                    ajax: 'id',
                    type: 'actions',
                    buttons: []
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



        let pages = [];
        let langs = [];

        setTimeout(function() {
            getLangs();
        }, 500);

        function getLangs() {
            axios.get('/dashboard/languages/get')
                .then(res => {
                    langs = res.data;
                    getTransltions();
                }).catch(err => {
                console.log(err)
            })
        };

        function getTransltions() {
            pages = [];
            $('.ZdrojowaTable--table tbody tr').each(function(index, tr){
                pages.push($(tr).attr('id'));
            });
            if (pages.length > 0) {
                pages.forEach(id => {
                    getPage(id);
                });
            }
            // setTimeout(function(){
            //     getTransltions();
            // }, 5000);
        }

        function getPage(id) {
            axios.get('/dashboard/pages/get?id=' + id)
                .then(res => {
                    let page = res.data;
                    let availableLangs = '';

                    //TODO:
                    let key = page.lang === 'en' ? 'gb' : page.lang;
                    availableLangs += `<span class="flag-icon flag-icon-${key}"></span>`;
                    // if (typeof page.translation === 'undefined') {
                    //     langs.forEach(lang => {
                    //         if (lang.key !== page.lang) {
                    //             let key = lang.key === 'en' ? 'gb' : lang.key;
                    //             availableLangs += `<a href="/dashboard/pages/add?id=${page.id}&lang=${lang.key}" title="Dodaj">
                    //                     <span class="flag-icon flag-icon-${key}" style="opacity: 0.5"></span>
                    //                 </a>`;
                    //         }
                    //     });
                    // }
                    $('#' + page.id).find('td:eq(3)').html(availableLangs);
                }).catch(err => {
                console.log(err)
            })
        };
    </script>
@endsection

