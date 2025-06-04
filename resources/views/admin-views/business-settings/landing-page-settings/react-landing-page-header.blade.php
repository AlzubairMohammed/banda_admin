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

    @php($header_title=\App\Models\DataSetting::withoutGlobalScope('translate')->where('type','react_landing_page')->where('key','header_title')->first())
    @php($header_sub_title=\App\Models\DataSetting::withoutGlobalScope('translate')->where('type','react_landing_page')->where('key','header_sub_title')->first())
    @php($header_tag_line=\App\Models\DataSetting::withoutGlobalScope('translate')->where('type','react_landing_page')->where('key','header_tag_line')->first())
    @php($header_icon=\App\Models\DataSetting::withoutGlobalScope('translate')->where('type','react_landing_page')->where('key','header_icon')->first())
    @php($header_banner=\App\Models\DataSetting::withoutGlobalScope('translate')->where('type','react_landing_page')->where('key','header_banner')->first())
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
    @endif
    <div class="tab-content">
        <div class="tab-pane fade show active">
            <form action="{{ route('admin.business-settings.react-landing-page-settings', 'header-section') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <h5 class="card-title mb-3 mt-3">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <span>
                            <span class="card-header-icon mr-2"><i class="tio-settings-outlined"></i></span> <span>{{translate('Header Section')}}</span>
                        </span>
                    </div>
                </h5>
                <div class="card">
                    <div class="card-body">

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="row g-3">
                                    @if ($language)
                                    <div class="col-12 lang_form default-form">
                                        <div class="mb-2">
                                            <label for="header_title" class="form-label">{{translate('Title')}}({{ translate('messages.default') }})<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_title_within_20_characters') }}">
                                                <img src="{{asset('public/assets/admin/img/info-circle.svg')}}" alt="">
                                            </span> <span class="form-label-secondary text-danger"
                            data-toggle="tooltip" data-placement="right"
                            data-original-title="{{ translate('messages.Required.')}}"> *
                            </span>
                                </label>
                                    <input id="header_title" type="text"  maxlength="20" name="header_title[]" value="{{ $header_title?->getRawOriginal('value')??'' }}" class="form-control" placeholder="{{translate('messages.title_here...')}}">
                                        </div>
                                        <div class="mb-2">
                                            <label for="header_sub_title" class="form-label">{{translate('Sub Title')}}({{ translate('messages.default') }})<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_title_within_40_characters') }}">
                                                <img src="{{asset('public/assets/admin/img/info-circle.svg')}}" alt="">
                                            </span> <span class="form-label-secondary text-danger"
                            data-toggle="tooltip" data-placement="right"
                            data-original-title="{{ translate('messages.Required.')}}"> *
                            </span>
                                </label>
                                    <input id="header_sub_title" type="text"  maxlength="40" name="header_sub_title[]" value="{{ $header_sub_title?->getRawOriginal('value')??'' }}" class="form-control" placeholder="{{translate('messages.sub_title_here...')}}">
                                        </div>
                                        <div class="mb-2">
                                            <label for="header_tag_line" class="form-label">{{translate('Tag Line')}}({{ translate('messages.default') }})<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_title_within_120_characters') }}">
                                                <img src="{{asset('public/assets/admin/img/info-circle.svg')}}" alt="">
                                            </span></label>
                                    <input id="header_tag_line" type="text"  maxlength="120" name="header_tag_line[]" value="{{ $header_tag_line?->getRawOriginal('value')??'' }}" class="form-control" placeholder="{{translate('messages.tag_line...')}}">
                                        </div>
                                    </div>
                                <input type="hidden" name="lang[]" value="default">
                                    @foreach(json_decode($language) as $lang)
                                    <?php
                                    if(isset($header_title->translations)&&count($header_title->translations)){
                                            $header_title_translate = [];
                                            foreach($header_title->translations as $t)
                                            {
                                                if($t->locale == $lang && $t->key=='header_title'){
                                                    $header_title_translate[$lang]['value'] = $t->value;
                                                }
                                            }

                                        }
                                    if(isset($header_sub_title->translations)&&count($header_sub_title->translations)){
                                            $header_sub_title_translate = [];
                                            foreach($header_sub_title->translations as $t)
                                            {
                                                if($t->locale == $lang && $t->key=='header_sub_title'){
                                                    $header_sub_title_translate[$lang]['value'] = $t->value;
                                                }
                                            }

                                        }
                                    if(isset($header_tag_line->translations)&&count($header_tag_line->translations)){
                                            $header_tag_line_translate = [];
                                            foreach($header_tag_line->translations as $t)
                                            {
                                                if($t->locale == $lang && $t->key=='header_tag_line'){
                                                    $header_tag_line_translate[$lang]['value'] = $t->value;
                                                }
                                            }

                                        }

                                        ?>
                                        <div class="col-12 d-none lang_form" id="{{$lang}}-form">
                                            <div class="mb-2">
                                                <label for="header_title{{$lang}}" class="form-label">{{translate('Title')}}({{strtoupper($lang)}})<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_title_within_20_characters') }}">
                                                <img src="{{asset('public/assets/admin/img/info-circle.svg')}}" alt="">
                                            </span></label>
                                    <input id="header_title{{$lang}}" type="text"  maxlength="20" name="header_title[]" value="{{ $header_title_translate[$lang]['value']??'' }}" class="form-control" placeholder="{{translate('messages.title_here...')}}">
                                            </div>
                                            <div class="mb-2">
                                                <label for="header_sub_title{{$lang}}" class="form-label">{{translate('Sub Title')}}({{strtoupper($lang)}})<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_title_within_40_characters') }}">
                                                <img src="{{asset('public/assets/admin/img/info-circle.svg')}}" alt="">
                                            </span></label>
                                    <input id="header_sub_title{{$lang}}" type="text"  maxlength="40" name="header_sub_title[]" value="{{ $header_sub_title_translate[$lang]['value']??'' }}" class="form-control" placeholder="{{translate('messages.sub_title_here...')}}">
                                            </div>
                                            <div class="mb-2">
                                                <label for="header_tag_line{{$lang}}" class="form-label">{{translate('Tag Line')}}({{strtoupper($lang)}})<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_title_within_120_characters') }}">
                                                <img src="{{asset('public/assets/admin/img/info-circle.svg')}}" alt="">
                                            </span></label>
                                    <input id="header_tag_line{{$lang}}" type="text"  maxlength="120" name="header_tag_line[]" value="{{ $header_tag_line_translate[$lang]['value']??'' }}" class="form-control" placeholder="{{translate('messages.tag_line...')}}">
                                            </div>
                                        </div>
                                        <input type="hidden" name="lang[]" value="{{$lang}}">
                                    @endforeach
                                @else
                                <div class="col-12">
                                    <div class="mb-2">
                                        <label for="header_title" class="form-label">{{translate('Title')}}</label>
                                        <input id="header_title" type="text" name="header_title[]" class="form-control" placeholder="{{translate('messages.title_here...')}}">
                                    </div>
                                    <div class="mb-2">
                                        <label for="header_sub_title" class="form-label">{{translate('Sub Title')}}</label>
                                        <input id="header_sub_title" type="text" name="header_sub_title[]" class="form-control" placeholder="{{translate('messages.sub_title_here...')}}">
                                    </div>
                                    <div class="mb-2">
                                        <label for="header_tag_line" class="form-label">{{translate('Tag Line')}}</label>
                                        <input id="header_tag_line" type="text" name="header_tag_line[]" class="form-control" placeholder="{{translate('messages.tag_line...')}}">
                                    </div>
                                </div>
                                    <input type="hidden" name="lang[]" value="default">
                                @endif
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label d-block mb-2">
                                    {{ translate('messages.Icon') }} <span class="text--primary">{{ translate('(size: 1:1)') }}</span>
                                </label>
                                <label class="upload-img-3 m-0">
                                    <div class="position-relative">
                                    <div class="img">
                                        <img
                                        src="{{\App\CentralLogics\Helpers::get_full_url('header_icon', $header_icon?->value?? '', $header_icon?->storage[0]?->value ?? 'public','aspect_1')}}" data-onerror-image="{{asset('/public/assets/admin/img/aspect-1.png')}}" class="img__aspect-1 mw-100 min-w-135px onerror-image" alt="">
                                    </div>
                                    <input type="file"  name="image" hidden>
                                       @if (isset($header_icon['value']))
                                            <span id="header_icon" class="remove_image_button remove-image"
                                                  data-id="header_icon"
                                                  data-title="{{translate('Warning!')}}"
                                                  data-text="<p>{{translate('Are_you_sure_you_want_to_remove_this_image_?')}}</p>"
                                            > <i class="tio-clear"></i></span>
                                            @endif
                                        </div>
                                </label>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label d-block mb-2">
                                    {{translate('Banner')}}  <span class="text--primary">{{translate('(size: 1:1)')}} <span class="form-label-secondary text-danger"
                                        data-toggle="tooltip" data-placement="right"
                                        data-original-title="{{ translate('messages.Required.')}}"> *
                                        </span>
                                </span>
                                </label>
                                <label class="upload-img-3 m-0">
                                    <div class="position-relative">
                                    <div class="img">
                                        <img
                                            src="{{\App\CentralLogics\Helpers::get_full_url('header_banner', $header_banner?->value?? '', $header_banner?->storage[0]?->value ?? 'public','aspect_1')}}" data-onerror-image="{{asset('/public/assets/admin/img/aspect-1.png')}}"
                                            class="img__aspect-1 mw-100 min-w-135px onerror-image" alt="">
                                    </div>
                                        <input type="file" name="banner_image"  hidden>

                                        </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="btn--container justify-content-end mt-3">
                    <button type="reset" class="btn btn--reset mb-2">{{translate('Reset')}}</button>
                    <button type="submit"   class="btn btn--primary mb-2">{{translate('Save Information')}}</button>
                </div>
            </form>
            <form  id="header_icon_form" action="{{ route('admin.remove_image') }}" method="post">
                @csrf
                <input type="hidden" name="id" value="{{  $header_icon?->id}}" >
                <input type="hidden" name="model_name" value="DataSetting" >
                <input type="hidden" name="image_path" value="header_icon" >
                <input type="hidden" name="field_name" value="value" >
            </form>


        </div>
    </div>
</div>
<!-- How it Works -->
@include('admin-views.business-settings.landing-page-settings.partial.how-it-work-react')
@endsection
