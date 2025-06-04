@extends('layouts.admin.app')

@section('title',translate('messages.react_landing_page'))

@section('content')
<div class="content container-fluid">
    <div class="page-header pb-0">
        <div class="d-flex flex-wrap justify-content-between">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/landing.png')}}" class="w--20" alt="">
                </span>
                <span>
                    {{ translate('messages.react_landing_page') }}
                </span>
            </h1>
            <div class="text--primary-2 py-1 d-flex flex-wrap align-items-center" type="button" data-toggle="modal" data-target="#how-it-works">
                <strong class="mr-2">{{translate('See_how_it_works!')}}</strong>
                <div>
                    <i class="tio-info-outined"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="mb-4 mt-2">
        <div class="js-nav-scroller hs-nav-scroller-horizontal">
            @include('admin-views.business-settings.landing-page-settings.top-menu-links.react-landing-page-links')
        </div>
    </div>
    <div class="tab-content">
        <div class="tab-pane fade show active">
            <form action="{{ route('admin.business-settings.react-landing-page-settings', 'promotion-banner') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <h5 class="card-title mb-3 mt-3">
                    <span class="card-header-icon mr-2"><i class="tio-settings-outlined"></i></span> <span>{{translate('Banner Section')}}</span>
                </h5>
                <div class="card mb-3">
                    <div class="card-body">

                        <div class="row g-3">
                            <div class="col-12">
                                <div>
                                    <label class="form-label d-block mb-3">
                                        {{translate('Banner')}}  <span class="text--primary">{{translate('(size: 2:1)')}}</span><span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('If_you_want_to_upload_one_banner_then_you_have_to_upload_it_in_2:1_ratio_otherwise_the_ratio_will_be_same_as_before.') }}">
                                            <img src="{{asset('public/assets/admin/img/info-circle.svg')}}" alt="">
                                        </span>
                                    </label>

                                    <label class="upload-img-3 d-block max-w-640">
                                        <div class="img">
                                            <img src="{{asset("/public/assets/admin/img/upload-4.png")}}" data-onerror-image="{{asset("/public/assets/admin/img/upload-4.png")}}" class="vertical-img w-100 mw-100 onerror-image" alt="">
                                        </div>
                                        <input type="file"  name="image" hidden="">
                                    </label>
                                </div>

                            </div>
                        </div>
                        <div class="btn--container justify-content-end mt-3">
                            <button type="reset" class="btn btn--reset mb-2">{{translate('Reset')}}</button>
                            <button type="submit"   class="btn btn--primary mb-2">{{translate('Add')}}</button>
                        </div>

                    </div>
                    </div>
                </form>
                @php($banner_data = \App\Models\DataSetting::where(['key'=>'promotion_banner','type'=>'react_landing_page'])->first())
                @php($banners = isset($banner_data->value)?json_decode($banner_data->value, true):[])
                    <div class="card-body p-0">
                        <!-- Table -->
                        <div class="table-responsive datatable-custom">
                            <table class="table table-borderless table-thead-bordered table-align-middle table-nowrap card-table m-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="border-top-0">{{translate('SL')}}</th>
                                        <th class="border-top-0">{{translate('banner Image')}}</th>
                                        <th class="text-center border-top-0">{{translate('Action')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($banners as $key=>$banner)
                                    <tr>
                                        <td>{{ $key+1 }}</td>
                                        <td>
                                            <img
                                            src="{{ \App\CentralLogics\Helpers::get_full_url('promotional_banner',$banner['img'] ?? '',$banner['storage'] ??'public') }}"
                                            data-onerror-image="{{asset('/public/assets/admin/img/upload-3.png')}}" class="__size-105 mw-100 onerror-image" alt="">
                                        </td>
                                        <td>
                                            <div class="btn--container justify-content-center">
                                                <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:"
                                                   data-id="promotion-{{$key}}"
                                                   data-message="{{translate('messages.Want_to_delete_this_banner')}}"
                                                  title="{{translate('messages.delete')}}"><i class="tio-delete-outlined"></i>
                                                </a>
                                            </div>
                                                <form action="{{route('admin.business-settings.react-landing-page-settings-delete',['tab'=>'promotion_banner', 'key'=>$key])}}"
                                                        method="post" id="promotion-{{$key}}">
                                                    @csrf @method('delete')
                                                </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </div>
                        <!-- End Table -->
                    </div>
                    @if(count($banners) === 0)
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

    <!-- How it Works -->
    @include('admin-views.business-settings.landing-page-settings.partial.how-it-work-react')
@endsection

