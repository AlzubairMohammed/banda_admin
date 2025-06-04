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
            <form action="{{ route('admin.business-settings.review-react-update',[$review->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <h5 class="card-title mb-3 mt-3">
                    <span class="card-header-icon mr-2"><i class="tio-settings-outlined"></i></span> <span>{{translate('Testimonial List Section')}}</span>
                </h5>
                <div class="card mb-3">
                    <div class="card-body">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">{{translate('Reviewer Name')}}</label>
                                        <input id="name" type="text" name="name" value="{{ $review->name }}" class="form-control" placeholder="{{translate('Ex:  John Doe')}}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="designation" class="form-label">{{translate('Designation')}}</label>
                                        <input id="designation" type="text" name="designation" value="{{ $review->designation }}" class="form-control" placeholder="{{translate('Ex:  CTO')}}">
                                    </div>
                                    <div class="col-md-12">
                                        <label for="review" class="form-label">{{translate('messages.review')}}<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_title_within_140_characters') }}">
                                            <img src="{{asset('public/assets/admin/img/info-circle.svg')}}" alt="">
                                        </span></label>
                                        <textarea id="review" name="review"  maxlength="140" placeholder="{{translate('Very Good Company')}}" class="form-control h-92px">{{ $review->review }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex gap-40px">
                                    <div>
                                        <label class="form-label d-block mb-2">
                                            {{translate('Reviewer Image *')}}  <span class="text--primary">{{ translate('(1:1)') }}</span>
                                        </label>
                                        <label class="upload-img-3 m-0 d-block">
                                            <div class="position-relative">
                                            <div class="img">
                                                <img  src="{{ $review->reviewer_image_full_url ?? asset('/public/assets/admin/img/aspect-1.png') }}" data-onerror-image="{{asset("/public/assets/admin/img/aspect-1.png")}}" class="img__aspect-1 min-w-187px max-w-187px onerror-image" alt="">
                                            </div>
                                            <input type="file"  name="reviewer_image" hidden="">
                                            @if (isset($review->reviewer_image))
                                                    <span id="reviewer_image" class="remove_image_button remove-image"
                                                          data-id="reviewer_image"
                                                          data-title="{{translate('Warning!')}}"
                                                          data-text="<p>{{translate('Are_you_sure_you_want_to_remove_this_image_?')}}</p>"
                                                    > <i class="tio-clear"></i></span>
                                            @endif
                                        </label>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                        <div class="btn--container justify-content-end mt-3">
                            <button type="reset" class="btn btn--reset mb-2">{{translate('Reset')}}</button>
                            <button type="submit"   class="btn btn--primary mb-2">{{translate('messages.Update')}}</button>
                        </div>
                </div>
            </form>
                        <form  id="reviewer_image_form" action="{{ route('admin.remove_image') }}" method="post">
                @csrf
                <input type="hidden" name="id" value="{{  $review?->id}}" >
                {{-- <input type="hidden" name="json" value="1" > --}}
                <input type="hidden" name="model_name" value="ReactTestimonial" >
                <input type="hidden" name="image_path" value="reviewer_image" >
                <input type="hidden" name="field_name" value="reviewer_image" >
            </form>

        </div>
    </div>
</div>
    <!-- How it Works -->
    @include('admin-views.business-settings.landing-page-settings.partial.how-it-work-react')
@endsection
