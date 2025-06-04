@extends('layouts.vendor.app')
@section('title',translate('messages.Create_Role'))
@push('css_or_js')

@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Heading -->
    <div class="page-header">
        <h1 class="page-header-title">
            <span class="page-header-icon">
                <img src="{{asset('public/assets/admin/img/role.png')}}" class="w--26" alt="">
            </span>
            <span>
                {{translate('messages.custom_role')}}
            </span>
        </h1>
    </div>
    <!-- Page Heading -->
    @php($language=\App\Models\BusinessSetting::where('key','language')->first())
    @php($language = $language->value ?? null)
    @php($defaultLang = str_replace('_', '-', app()->getLocale()))
    <!-- Content Row -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">
                <span class="card-header-icon">
                    <i class="tio-document-text-outlined"></i>
                </span>
                <span>{{translate('messages.role_form')}}</span>
            </h5>
        </div>
        <div class="card-body">
            <form action="{{route('vendor.custom-role.create')}}" method="post">
                @csrf
                @if ($language)
                        <ul class="nav nav-tabs mb-4">
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
                                <label class="input-label" for="name">{{translate('messages.role_name')}} ({{ translate('messages.default') }})</label>
                                <input type="text" id="name" name="name[]" class="form-control" placeholder="{{translate('role_name_example')}}" maxlength="191"  >
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                                @foreach(json_decode($language) as $lang)
                                    <div class="form-group d-none lang_form" id="{{$lang}}-form">
                                        <label class="input-label" for="name{{$lang}}">{{translate('messages.role_name')}} ({{strtoupper($lang)}})</label>
                                        <input type="text" id="name{{$lang}}" name="name[]" class="form-control" placeholder="{{translate('role_name_example')}}" maxlength="191"  >
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                @endforeach
                            @else
                                <div class="form-group">
                                    <label class="input-label" for="name">{{translate('messages.role_name')}}</label>
                                    <input type="text" id="name" name="name" class="form-control" placeholder="{{translate('role_name_example')}}" value="{{old('name')}}" required maxlength="191">
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                            @endif

                <h5 class="text-capitalize">{{translate('messages.module_permission')}} : </h5>
                <hr>
                <div class="check--item-wrapper mx-0">
                    <div class="check-item">
                        <div class="form-group form-check form--check">
                            <input type="checkbox" name="modules[]" value="item" class="form-check-input"
                                    id="item">
                            <label class="form-check-label input-label " for="item">{{translate('messages.items')}}</label>
                        </div>
                    </div>
                    <div class="check-item">
                        <div class="form-group form-check form--check">
                            <input type="checkbox" name="modules[]" value="order" class="form-check-input"
                                    id="order">
                            <label class="form-check-label input-label " for="order">{{translate('messages.orders')}}</label>
                        </div>
                    </div>
                    <div class="check-item">
                        <div class="form-group form-check form--check">
                            <input type="checkbox" name="modules[]" value="store_setup" class="form-check-input" id="store_setup">
                            <label class="form-check-label input-label " for="store_setup">{{translate('messages.store_setup')}}</label>
                        </div>
                    </div>
                    @if (config('module.'.\App\CentralLogics\Helpers::get_store_data()->module->module_type)['add_on'])
                    <div class="check-item">
                        <div class="form-group form-check form--check">
                            <input type="checkbox" name="modules[]" value="addon" class="form-check-input"
                                    id="addon">
                            <label class="form-check-label input-label " for="addon">{{translate('messages.addons')}}</label>
                        </div>
                    </div>
                    @endif
                    <div class="check-item">
                        <div class="form-group form-check form--check">
                            <input type="checkbox" name="modules[]" value="wallet" class="form-check-input"
                                    id="wallet">
                            <label class="form-check-label input-label " for="wallet">{{translate('messages.my_wallet')}}</label>
                        </div>
                    </div>
                    <div class="check-item">
                        <div class="form-group form-check form--check">
                            <input type="checkbox" name="modules[]" value="bank_info" class="form-check-input"
                                    id="bank_info">
                            <label class="form-check-label input-label " for="bank_info">{{translate('messages.profile')}}</label>
                        </div>
                    </div>
                    <div class="check-item">
                        <div class="form-group form-check form--check">
                            <input type="checkbox" name="modules[]" value="employee" class="form-check-input"
                                    id="employee">
                            <label class="form-check-label input-label " for="employee">{{translate('messages.Employees')}}</label>
                        </div>
                    </div>
                    <div class="check-item">
                        <div class="form-group form-check form--check">
                            <input type="checkbox" name="modules[]" value="my_shop" class="form-check-input"
                                    id="my_shop">
                            <label class="form-check-label input-label " for="my_shop">{{translate('messages.my_shop')}}</label>
                        </div>
                    </div>

                    <div class="check-item">
                        <div class="form-group form-check form--check">
                            <input type="checkbox" name="modules[]" value="campaign" class="form-check-input"
                                    id="campaign">
                            <label class="form-check-label input-label " for="campaign">{{translate('messages.campaigns')}}</label>
                        </div>
                    </div>
                    <div class="check-item">
                        <div class="form-group form-check form--check">
                            <input type="checkbox" name="modules[]" value="reviews" class="form-check-input"
                                    id="reviews">
                            <label class="form-check-label input-label " for="reviews">{{translate('messages.reviews')}}</label>
                        </div>
                    </div>
                    <div class="check-item">
                        <div class="form-group form-check form--check">
                            <input type="checkbox" name="modules[]" value="pos" class="form-check-input"
                                    id="pos">
                            <label class="form-check-label input-label  text-uppercase" for="pos">{{translate('messages.pos')}}</label>
                        </div>
                    </div>
                    <div class="check-item">
                        <div class="form-group form-check form--check">
                            <input type="checkbox" name="modules[]" value="chat" class="form-check-input"
                                    id="chat">
                            <label class="form-check-label input-label " for="chat">{{translate('messages.chat')}}</label>
                        </div>
                    </div>
                </div>
                <div class="btn--container justify-content-end mt-4">
                    <button type="reset" class="btn btn--reset">{{translate('messages.reset')}}</button>
                    <button type="submit" class="btn btn--primary">{{translate('messages.submit')}}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header border-0">
            <div class="search--button-wrapper">
                <h5 class="card-title">
                    <span class="card-header-icon">
                        <i class="tio-document-text-outlined"></i>
                    </span>
                    <span>
                        {{translate('messages.roles_table')}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$rl->total()}}</span>
                    </span>
                </h5>
                <form  class="search-form min--250">
                    <!-- Search -->
                    <div class="input-group input--group">
                        <input  value="{{request()?->search ?? ''}}" type="search" name="search" class="form-control" placeholder="{{translate('messages.search_role')}}" aria-label="{{translate('messages.search')}}">
                        <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                    </div>
                    <!-- End Search -->
                </form>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive datatable-custom">
                <table id="columnSearchDatatable"
                        class="table table-borderless table-thead-bordered table-align-middle card-table"
                        data-hs-datatables-options='{
                            "order": [],
                            "orderCellsTop": true,
                            "paging":false
                        }'>
                    <thead class="thead-light">
                        <tr>
                            <th class="border-0 w-50px">{{translate('messages.sl#')}}</th>
                            <th class="border-0 w-50px">{{translate('messages.role_name')}}</th>
                            <th class="border-0 w-100px">{{translate('messages.modules')}}</th>
                            <th class="border-0 w-50px">{{translate('messages.created_at')}}</th>
                            <th class="border-0 w-50px text-center">{{translate('messages.action')}}</th>
                        </tr>
                    </thead>
                    <tbody  id="set-rows">
                    @foreach($rl as $k=>$r)
                        <tr>
                            <td >{{$k+$rl->firstItem()}}</td>
                            <td>{{Str::limit($r['name'],20,'...')}}</td>
                            <td class="text-capitalize">
                                @if($r['modules']!=null)
                                    @foreach((array)json_decode($r['modules']) as $key=>$m)

                                    @if ($m == 'bank_info')
                                    {{translate('messages.profile')}}
                                    @else
                                    {{translate(str_replace('_',' ',$m))}}
                                    @endif


                                    {{  !$loop->last ? ',' : '.'}}
                                    @endforeach
                                @endif
                            </td>
                            <td>{{date('d-M-y',strtotime($r['created_at']))}}</td>
                            <td>
                                <div class="btn--container justify-content-center">
                                    <a class="btn action-btn btn--primary btn-outline-primary"
                                        href="{{route('vendor.custom-role.edit',[$r['id']])}}" title="{{translate('messages.edit_role')}}"><i class="tio-edit"></i>
                                    </a>
                                    <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:"
                                       data-id="role-{{$r['id']}}" data-message="{{translate('messages.Want_to_delete_this_role')}}"
                                         title="{{translate('messages.delete_role')}}"><i class="tio-delete-outlined"></i>
                                    </a>
                                </div>
                                <form action="{{route('vendor.custom-role.delete',[$r['id']])}}"
                                        method="post" id="role-{{$r['id']}}">
                                    @csrf @method('delete')
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if(count($rl) !== 0)
                <hr>
                @endif
                <div class="page-area">
                    <table>
                        <tfoot>
                        {!! $rl->links() !!}
                        </tfoot>
                    </table>
                </div>
                @if(count($rl) === 0)
                <div class="empty--data">
                    <img src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                    <h5>
                        {{translate('no_data_found')}}
                    </h5>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection


