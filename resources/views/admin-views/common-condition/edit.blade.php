@extends('layouts.admin.app')

@section('title',translate('messages.Update condition'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/edit.png')}}" class="w--20" alt="">
                </span>
                <span>
                    {{translate('messages.Common_Condition_Update')}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.common-condition.update',[$condition['id']])}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-12">
                            @if($language)
                                <ul class="nav nav-tabs mb-4">
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
                                    @foreach ($language as $lang)
                                        <li class="nav-item">
                                            <a class="nav-link lang_link"
                                                href="#"
                                                id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <div class="col-12">
                            @if($language)
                                <div class="form-group lang_form" id="default-form">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.name')}} ({{ translate('messages.default') }})</label>
                                    <input type="text" name="name[]" class="form-control" placeholder="{{translate('messages.new_condition')}}" maxlength="191" value="{{$condition?->getRawOriginal('name')}}">
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                                @foreach($language as $lang)
                                    <?php
                                        if(count($condition['translations'])){
            // TRANSLATION FIX: Removed $translate = []; from inside foreach loop
            // This was causing Kurdish/Arabic translations to be lost on each iteration
                                            foreach($condition['translations'] as $t)
                                            {
                                                if($t->locale == $lang && $t->key=="name"){
                                                    $translate[$lang]['name'] = $t->value;
                                                }
                                            }
                                        }
                                    ?>
                                    <div class="form-group d-none lang_form" id="{{$lang}}-form">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.name')}} ({{strtoupper($lang)}})</label>
                                        <input type="text" name="name[]" class="form-control" placeholder="{{translate('messages.new_condition')}}" maxlength="191" value="{{$translate[$lang]['name']??''}}">
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                @endforeach
                            @else
                                <div class="form-group">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.name')}}</label>
                                    <input type="text" name="name" class="form-control" placeholder="{{translate('messages.new_condition')}}" value="{{$condition['name']}}" maxlength="191">
                                </div>
                                <input type="hidden" name="lang[]" value="{{$lang}}">
                            @endif
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-3">
                        <button type="reset" id="reset_btn" class="btn btn--reset">{{translate('messages.reset')}}</button>
                        <button type="submit" class="btn btn--primary">{{translate('messages.update')}}</button>
                    </div>
                </form>
            </div>
            <!-- End Table -->
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{asset('assets/admin')}}/js/view-pages/common-condition-index.js"></script>
    <script>
        "use strict";
        $('#reset_btn').click(function(){
            $('#module_id').val("{{ $condition->module_id }}").trigger('change');
            $('#viewer').attr('src', "{{asset('storage/app/public/condition')}}/{{$condition['image']}}");
        })
    </script>
@endpush
