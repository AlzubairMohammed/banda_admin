@extends('layouts.vendor.app')

@section('title',translate('messages.add_new_addon'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/addon.png')}}" class="w--26" alt="">
                </span>
                <span>{{translate('messages.add_new_addon')}}</span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="row g-3">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('vendor.addon.store')}}" method="post">
                            @csrf
                            @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                            @php($language = $language->value ?? null)
                            @php($defaultLang = str_replace('_', '-', app()->getLocale()))
                            @if($language)
                                <ul class="nav nav-tabs mb-4 border-0">
                                    <li class="nav-item">
                                        <a class="nav-link lang_link active"
                                        href="#"
                                        id="default-link">{{translate('messages.default')}}</a>
                                    </li>
                                    @foreach (json_decode($language) as $lang)
                                        <li class="nav-item">
                                            <a class="nav-link lang_link"
                                                href="#"
                                                id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="form-group lang_form" id="default-form">
                                    <label class="input-label" for="name">{{translate('messages.name')}} ({{translate('messages.default')}})</label>
                                    <input id="name" type="text" name="name[]" class="form-control" placeholder="{{translate('messages.new_addon')}}" maxlength="191"  >
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                                @foreach(json_decode($language) as $lang)
                                    <div class="form-group d-none lang_form" id="{{$lang}}-form">
                                        <label class="input-label" for="name{{$lang}}">{{translate('messages.name')}} ({{strtoupper($lang)}})</label>
                                        <input type="text" id="name{{$lang}}" name="name[]" class="form-control" placeholder="{{translate('messages.new_addon')}}" maxlength="191"  >
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                @endforeach
                            @else
                                <div class="form-group">
                                    <label  class="input-label" for="name">{{translate('messages.name')}}</label>
                                    <input id="name" type="text" name="name" class="form-control" placeholder="{{translate('messages.new_addon')}}" value="{{old('name')}}" required maxlength="191">
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                            @endif

                            <div class="form-group">
                                <label class="input-label" for="price">{{translate('messages.price')}}</label>
                                <input  id="price" type="number" min="0" max="999999999999.99" name="price" step="0.01" class="form-control" placeholder="100.00" value="{{old('price')}}" required>
                            </div>
                            <div class="btn--container justify-content-end">
                                <button type="reset" class="btn btn--reset">{{translate('messages.reset')}}</button>
                                <button type="submit" class="btn btn--primary">{{translate('messages.submit')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header py-2 border-0">
                        <div class="search--button-wrapper">
                            <h5 class="card-title">
                                {{translate('messages.addon_list')}}
                                <span class="badge badge-soft-dark ml-2" id="itemCount">{{$addons->total()}}</span>
                            </h5>
                            <form id="search-form" class="search-form">
                                <div class="input-group input--group">
                                    <input type="text" id="column1_search" class="form-control" placeholder="{{translate('messages.ex_search_name')}}">
                                    <button type="button" class="btn btn--secondary">
                                        <i class="tio-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table id="columnSearchDatatable"
                               class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                               data-hs-datatables-options='{
                                 "order": [],
                                 "orderCellsTop": true,
                                 "paging":false
                               }'>
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-0 w-10p">{{translate('messages.#')}}</th>
                                    <th class="border-0 w-50p">{{translate('messages.name')}}</th>
                                    <th class="border-0 w-40p">{{translate('messages.price')}}</th>
                                    <th class="border-0 w-20p text-center">{{translate('messages.action')}}</th>
                                </tr>
                            </thead>

                            <tbody>
                            @foreach($addons as $key=>$addon)
                                <tr>
                                    <td>{{$key+1}}</td>
                                    <td>
                                    <span class="d-block font-size-sm text-body">
                                        {{Str::limit($addon['name'], 20, '...')}}
                                    </span>
                                    </td>
                                    <td>{{\App\CentralLogics\Helpers::format_currency($addon['price'])}}</td>
                                    <td>
                                        <div class="btn--container">
                                            <a class="btn action-btn btn--primary btn-outline-primary"
                                                    href="{{route('vendor.addon.edit',[$addon['id']])}}" title="{{translate('messages.edit_addon')}}"><i class="tio-edit"></i></a>
                                            <a class="btn action-btn btn--danger btn-outline-danger form-alert"     href="javascript:"
                                            data-id="addon-{{$addon['id']}}"
                                            data-message="{{ translate('Want_to_delete_this_addon_?') }}"
                                               title="{{translate('messages.delete_addon')}}"><i class="tio-delete-outlined"></i></a>
                                        </div>
                                        <form action="{{route('vendor.addon.delete',[$addon['id']])}}"
                                                    method="post" id="addon-{{$addon['id']}}">
                                            @csrf @method('delete')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        </div>

                    @if(count($addons) !== 0)
                    <hr>
                    @endif
                    <div class="page-area">
                        {!! $addons->links() !!}
                    </div>
                    @if(count($addons) === 0)
                    <div class="empty--data">
                        <img src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                        <h5>
                            {{translate('messages.no_data_found')}}
                        </h5>
                    </div>
                    @endif
                </div>
            </div>
            <!-- End Table -->
        </div>
    </div>
@endsection

@push('script_2')
    <script src="{{asset('public/assets/admin/js/view-pages/datatable-search.js')}}"></script>
@endpush
