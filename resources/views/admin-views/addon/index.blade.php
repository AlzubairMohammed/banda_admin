@extends('layouts.admin.app')

@section('title',translate('messages.add_new_addon'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/addon.png')}}" class="w--20" alt="">
                </span>
                <span>
                    {{translate('messages.add_new_addon')}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="card">
            <div class="card-body">
                <form action="{{isset($addon)?route('admin.addon.update',[$addon['id']]):route('admin.addon.store')}}"
                      method="post">
                    @csrf
                    @if($language)
                        <ul class="nav nav-tabs mb-4">
                            <li class="nav-item">
                                <a class="nav-link lang_link active"
                                   href="#"
                                   id="default-link">{{translate('messages.default')}}</a>
                            </li>
                            @foreach ($language as $lang)
                                <li class="nav-item">
                                    <a class="nav-link lang_link"
                                       href="#"
                                       id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <div class="row">
                        <div class="col-sm-6 col-lg-4">
                            @if ($language)
                                <div class="form-group lang_form" id="default-form">
                                    <label class="input-label"
                                           for="exampleFormControlInput1">{{translate('messages.name')}}
                                        ({{translate('messages.default')}})</label>
                                    <input type="text" name="name[]" class="form-control"
                                           placeholder="{{translate('messages.new_addon')}}" maxlength="191">
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                                @foreach($language as $lang)
                                    <div class="form-group d-none lang_form" id="{{$lang}}-form">
                                        <label class="input-label"
                                               for="exampleFormControlInput1">{{translate('messages.name')}}
                                            ({{strtoupper($lang)}})</label>
                                        <input type="text" name="name[]" class="form-control"
                                               placeholder="{{translate('messages.new_addon')}}" maxlength="191">
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                @endforeach
                            @else
                                <div class="form-group">
                                    <label class="input-label"
                                           for="exampleFormControlInput1">{{translate('messages.name')}}</label>
                                    <input type="text" name="name" class="form-control"
                                           placeholder="{{translate('messages.new_addon')}}" value="{{old('name')}}"
                                           maxlength="191">
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                            @endif
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group">
                                <label class="input-label"
                                       for="exampleFormControlSelect1">{{translate('messages.store')}}<span
                                        class="input-label-secondary"></span></label>
                                <select name="store_id" id="store_id" class="js-data-example-ajax form-control"
                                        data-placeholder="{{translate('messages.select_store')}}">

                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group">
                                <label class="input-label"
                                       for="exampleFormControlInput1">{{translate('messages.price')}}</label>
                                <input type="number" min="0" max="999999999999.99" name="price" step="0.01"
                                       value="{{old('price')}}" class="form-control" placeholder="100" required>
                            </div>
                        </div>
                    </div>

                    <div class="btn--container justify-content-end">
                        <button type="reset" id="reset_btn"
                                class="btn btn--reset">{{translate('messages.reset')}}</button>
                        <button type="submit"
                                class="btn btn--primary">{{isset($addon)?translate('messages.update'):translate('messages.add')}}</button>
                    </div>

                </form>
            </div>
        </div>

        <div class="card mt-1">
            <div class="card-header py-2 border-0">
                <div class="search--button-wrapper justify-content-end">
                    <h5 class="card-title"> {{translate('messages.addon_list')}}<span class="badge badge-soft-dark ml-2"
                                                                                      id="itemCount">{{$addons->total()}}</span>
                    </h5>
                    <div class="min--220">
                        <select name="store_id" id="store"
                                data-url="{{route('admin.addon.add-new')}}"
                                data-placeholder="{{translate('messages.select_store')}}"
                                class="js-data-example-ajax form-control store-filter" title="Select Restaurant">
                            @if(isset($store))
                                <option value="{{$store->id}}" selected>{{$store->name}}</option>
                            @else
                                <option value="all" selected>{{translate('messages.all_stores')}}</option>
                            @endif
                        </select>
                    </div>
                    <form class="search-form">
                        <!-- Search -->
                        <div class="input-group input--group">
                            <input type="search" name="search" value="{{ request()->search ?? null }}"
                                   class="form-control min-height-45"
                                   placeholder="{{translate('messages.ex_:_addons_name')}}" aria-label="Search addons">
                            <button type="submit" class="btn btn--secondary min-height-45"><i class="tio-search"></i>
                            </button>
                        </div>
                        <!-- End Search -->
                    </form>
                    <div class="hs-unfold">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle min-height-40"
                           href="javascript:;"
                           data-hs-unfold-options='{
                                    "target": "#usersExportDropdown",
                                    "type": "css-animation"
                                }'>
                            <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                        </a>
                    </div>
                    <div id="usersExportDropdown"
                         class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                        <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                        <a id="export-excel" class="dropdown-item" href="
                            {{ route('admin.addon.export', ['type' => 'excel', request()->getQueryString()]) }}
                            ">
                            <img class="avatar avatar-xss avatar-4by3 mr-2"
                                 src="{{ asset('public/assets/admin') }}/svg/components/excel.svg"
                                 alt="Image Description">
                            {{ translate('messages.excel') }}
                        </a>
                        <a id="export-csv" class="dropdown-item" href="
                        {{ route('admin.addon.export', ['type' => 'csv', request()->getQueryString()]) }}">
                            <img class="avatar avatar-xss avatar-4by3 mr-2"
                                 src="{{ asset('public/assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                 alt="Image Description">
                            .{{ translate('messages.csv') }}
                        </a>
                    </div>

                    <a class="js-hs-unfold-invoker btn btn-white min-height-45" href="javascript:;"
                       data-hs-unfold-options='{
                        "target": "#showHideDropdown",
                        "type": "css-animation"
                        }'>
                        <i class="tio-table mr-1"></i> {{translate('messages.columns')}} <span
                            class="badge badge-soft-dark rounded-circle ml-1">5</span>
                    </a>

                    <div id="showHideDropdown"
                         class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-right dropdown-card min--240">
                        <div class="card card-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="mr-2">{{translate('messages.name')}}</span>
                                    <!-- Checkbox Switch -->
                                    <label class="toggle-switch toggle-switch-sm" for="toggleColumn_name">
                                        <input type="checkbox" class="toggle-switch-input" id="toggleColumn_name"
                                               checked>
                                        <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                    <!-- End Checkbox Switch -->
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="mr-2">{{translate('messages.price')}}</span>
                                    <!-- Checkbox Switch -->
                                    <label class="toggle-switch toggle-switch-sm" for="toggleColumn_price">
                                        <input type="checkbox" class="toggle-switch-input" id="toggleColumn_price"
                                               checked>
                                        <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                    <!-- End Checkbox Switch -->
                                </div>


                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="mr-2">{{translate('messages.store')}}</span>

                                    <!-- Checkbox Switch -->
                                    <label class="toggle-switch toggle-switch-sm" for="toggleColumn_vendor">
                                        <input type="checkbox" class="toggle-switch-input" id="toggleColumn_vendor"
                                               checked>
                                        <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                    <!-- End Checkbox Switch -->
                                </div>


                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="mr-2">{{translate('messages.status')}}</span>

                                    <!-- Checkbox Switch -->
                                    <label class="toggle-switch toggle-switch-sm" for="toggleColumn_status">
                                        <input type="checkbox" class="toggle-switch-input" id="toggleColumn_status"
                                               checked>
                                        <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                    <!-- End Checkbox Switch -->
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="mr-2">{{translate('messages.action')}}</span>

                                    <!-- Checkbox Switch -->
                                    <label class="toggle-switch toggle-switch-sm" for="toggleColumn_action">
                                        <input type="checkbox" class="toggle-switch-input" id="toggleColumn_action"
                                               checked>
                                        <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                    <!-- End Checkbox Switch -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Unfold -->
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive datatable-custom">
                <table id="datatable"
                       class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                       data-hs-datatables-options='{
                        "search": "#datatableSearch",
                        "entries": "#datatableEntries",
                        "isResponsive": false,
                        "isShowPaging": false,
                        "paging":false
                            }'>
                    <thead class="thead-light">
                    <tr>
                        <th>{{translate('sl')}}</th>
                        <th>{{translate('messages.name')}}</th>
                        <th>{{translate('messages.price')}}</th>
                        <th>{{translate('messages.store')}}</th>
                        <th class="text-center">{{translate('messages.status')}}</th>
                        <th class="text-center">{{translate('messages.action')}}</th>
                    </tr>
                    </thead>

                    <tbody id="set-rows">
                    @foreach($addons as $key=>$addon)
                        <tr>
                            <td>{{$key+ $addons->firstItem()}}</td>
                            <td>
                                <span class="d-block font-size-sm text-body">
                                    {{Str::limit($addon['name'],20,'...')}}
                                </span>
                            </td>
                            <td>{{\App\CentralLogics\Helpers::format_currency($addon['price'])}}</td>
                            <td>{{Str::limit($addon->store?$addon->store->name:translate('messages.store_deleted'),25,'...')}}</td>
                            <td>
                                <label class="toggle-switch toggle-switch-sm" for="stausCheckbox{{$addon->id}}">
                                    <input type="checkbox"
                                           data-url="{{route('admin.addon.status',[$addon['id'],$addon->status?0:1])}}"
                                           class="toggle-switch-input redirect-url"
                                           id="stausCheckbox{{$addon->id}}" {{$addon->status?'checked':''}}>
                                    <span class="toggle-switch-label mx-auto">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                </label>
                            </td>
                            <td>
                                <div class="btn--container justify-content-center">
                                    <a class="btn action-btn btn--primary btn-outline-primary"
                                       href="{{route('admin.addon.edit',[$addon['id']])}}"
                                       title="{{translate('messages.edit_addon')}}"><i class="tio-edit"></i></a>
                                    <a class="btn action-btn btn--danger btn-outline-danger form-alert" data-id="addon-{{$addon['id']}}" data-message="{{ translate('Want to delete this addon ?') }}" href="javascript:"
                                       title="{{translate('messages.delete_addon')}}"><i
                                            class="tio-delete-outlined"></i></a>
                                    <form action="{{route('admin.addon.delete',[$addon['id']])}}"
                                          method="post" id="addon-{{$addon['id']}}">
                                        @csrf @method('delete')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
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
                    {{translate('no_data_found')}}
                </h5>
            </div>
        @endif
    </div>
    </div>

@endsection

@push('script_2')
    <script src="{{asset('public/assets/admin')}}/js/view-pages/addon-index.js"></script>
    <script>
        "use strict";

        $('#store').select2({
            ajax: {
                url: '{{url('/')}}/admin/store/get-stores',
                data: function (params) {
                    return {
                        q: params.term, // search term
                        all: true,
                        module_type: 'food',
                        module_id: {{Config::get('module.current_module_id')}},
                        page: params.page
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                __port: function (params, success, failure) {
                    var $request = $.ajax(params);

                    $request.then(success);
                    $request.fail(failure);

                    return $request;
                }
            }
        });

        $('#store_id').select2({
            ajax: {
                url: '{{url('/')}}/admin/store/get-stores',
                data: function (params) {
                    return {
                        q: params.term, // search term
                        module_type: 'food',
                        module_id: {{Config::get('module.current_module_id')}},
                        page: params.page
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                __port: function (params, success, failure) {
                    var $request = $.ajax(params);

                    $request.then(success);
                    $request.fail(failure);

                    return $request;
                }
            }
        });
    </script>
@endpush
