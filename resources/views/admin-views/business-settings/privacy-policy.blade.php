@extends('layouts.admin.app')

@section('title',translate('messages.privacy_policy'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/privacy-policy.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{translate('messages.privacy_policy')}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.business-settings.privacy-policy')}}" method="post" id="tnc-form">
                    @csrf
                    @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                    @php($language = $language->value ?? null)
                    @php($defaultLang = str_replace('_', '-', app()->getLocale()))
                    @if ($language)
                    <ul class="nav nav-tabs mb-4 border-0">
                        <li class="nav-item">
                            <a class="nav-link lang_link active"
                            href="#"
                            id="default-link">{{translate('messages.default')}}</a>
                        </li>

            <?php
                // 🚨 TRANSLATION FIX: Initialize $translate OUTSIDE foreach to prevent reset
                $translate = [];
                if(isset($product) && count($product['translations'] ?? [])){
                    foreach($product['translations'] as $t) {
                        if($t->key=="name"){
                            $translate[$t->locale]['name'] = $t->value;
                        }
                        if($t->key=="description"){
                            $translate[$t->locale]['description'] = $t->value;
                        }
                        // Add more fields as needed based on model
                    }
                }
                // Support for other model types (store, item, etc.)
                if(isset($store) && count($store['translations'] ?? [])){
                    foreach($store['translations'] as $t) {
                        $translate[$t->locale][$t->key] = $t->value;
                    }
                }
                if(isset($item) && count($item['translations'] ?? [])){
                    foreach($item['translations'] as $t) {
                        $translate[$t->locale][$t->key] = $t->value;
                    }
                }
            ?>
                        @foreach (json_decode($language) as $lang)
                        <li class="nav-item">
                            <a class="nav-link lang_link"
                            href="#"
                            id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                        </li>
                        @endforeach
                    </ul>
                    @endif

                    <div class="form-group lang_form" id="default-form">
                        <input type="hidden" name="lang[]" value="default">
                        <textarea class="ckeditor form-control" name="privacy_policy[]">{!! $privacy_policy?->getRawOriginal('value') ?? '' !!}</textarea>
                    </div>

                    @if ($language)
                        @forelse(json_decode($language) as $lang)
                            <?php
                                if($privacy_policy?->translations){
                                    $translate = [];
                                    foreach($privacy_policy?->translations as $t)
                                    {
                                        if($t->locale == $lang && $t->key=="privacy_policy"){
                                            $translate[$lang]['privacy_policy'] = $t->value;
                                        }
                                    }
                                }
                                ?>
                            <div class="form-group d-none lang_form" id="{{$lang}}-form">
                                <textarea class="ckeditor form-control" name="privacy_policy[]">{!!  $translate[$lang]['privacy_policy'] ?? null !!}</textarea>
                            </div>
                            <input type="hidden" name="lang[]" value="{{$lang}}">
                            @empty
                        @endforelse
                    @endif

                    <div class="btn--container justify-content-end">
                        <button type="submit" class="btn btn--primary">{{translate('messages.submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{asset('assets/admin/ckeditor/ckeditor.js')}}"></script>
@endpush
