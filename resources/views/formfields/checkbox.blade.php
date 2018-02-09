<br>

@php
    $model = app($options->model);
    $query = $model::all();
@endphp

@foreach($query as $relationshipData)
    {{ $relationshipData->{$options->label} }}
    <input type="checkbox" name="{{ $row->field }}" class="toggleswitch"><br>
@endforeach


@if(isset($options->on) && isset($options->off))
    <input type="checkbox" name="{{ $row->field }}" class="toggleswitch"
           data-on="{{ $options->on }}" {!! $checked ? 'checked="checked"' : '' !!}
           data-off="{{ $options->off }}">
@else
    <input type="checkbox" name="{{ $row->field }}" class="toggleswitch"
           @if($checked) checked @endif>
@endif

