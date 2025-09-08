@php
    $choiceOptions = is_array($choice_options)
        ? $choice_options
        : (is_string($choice_options) ? json_decode($choice_options, true) : []);
    $choiceNos = is_array($choice_no)
        ? $choice_no
        : (is_string($choice_no) ? json_decode($choice_no, true) : []);
@endphp

@if(!empty($choiceOptions))
    @foreach($choiceOptions as $key=>$choice)
        @php($options = (isset($choice['options']) && is_array($choice['options'])) ? $choice['options'] : [])
        <div class="row">
            <div class="col-md-3">
                <input type="hidden" name="choice_no[]" value="{{$choiceNos[$key] ?? ''}}">
                <input type="text" class="form-control" name="choice[]" value="{{$choice['title'] ?? ''}}"
                       placeholder="{{translate('messages.choice_title')}}" readonly>
            </div>
            <div class="col-lg-9">
                <input type="text" class="form-control call-update-sku" name="choice_options_{{$choiceNos[$key] ?? ''}}[]" data-role="tagsinput"
                       value="@foreach($options as $c) {{$c.','}} @endforeach">
            </div>
        </div>
    @endforeach
@endif
