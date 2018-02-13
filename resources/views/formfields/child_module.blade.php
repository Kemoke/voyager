{{--
    TODO:
        - Fix overview.
        - Fix add.
        - Fix adding relationsíhip.
--}}

@php
    // Instantiate a new model which we can use in the rest of the component...
    $model = new $options->model; 

    // Retrieve the datatype of the relationship and preset the id of parent...
    $relationshipDataType = \TCG\Voyager\Models\DataType::where('model_name', $options->model)->first();
    $relationshipDataTypeContent = $model;
    $relationshipDataTypeContent->{$options->column} = $dataTypeContent->id;
@endphp

{{-- {"model":"App\\Picture","input_type":"picture_child_module","table":"pictures","type":"hasMany","column":"album_id","key":"album_id","label":"title","pivot_table":"albums","pivot":"0"} --}}

<hr/>

<p>
    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target=".form-modal-{{ $relationshipDataType->name }}">Add a new {{ str_singular($relationshipDataType->name) }}</button> <br/>
</p>
    
{{-- 
    The table below should be replaced with the tables that Voyager uses, the implementation below is only temporary.
--}}
<table class="table">
    <thead>
        <tr>
            @foreach(collect($model->first()->toArray())->except(['id', $options->column])->toArray() as $key => $value)
            <th>{{ ucfirst($key) }}</th>
            @endforeach
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
            @foreach ($model->where($options->column, $dataTypeContent->id)->get() as $item)
            <tr>
                @foreach(collect($item->toArray())->except(['id', $options->column])->toArray() as $value)
                <td>{{ $value }}</td>
                @endforeach
                <td><a href="#">Edit</a> or <a href="#">Delete</a></td>
            </tr>
            @endforeach
    </tbody>
</table>

<hr/>

@push('modals')
<div class="modal fade form-modal-{{ $relationshipDataType->name }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form role="form"
                class="form-edit-add"
                action="{{ route('voyager.' . $relationshipDataType->slug . '.store') }}"
                method="POST" enctype="multipart/form-data">
                <div class="modal-header card blue" >
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title">Add a new {{ str_singular($relationshipDataType->name) }}</h4>
                </div>
                <div class="modal-body" id="form">
                    {{ csrf_field() }}

                    @php 
                        $dataTypeRows = $relationshipDataType->{(isset($relationshipDataTypeContent->id) ? 'editRows' : 'addRows' )};

                        // Don't include the relationship fields and the parent id field...
                        $dataTypeRows = $dataTypeRows->filter(function ($dataType) use ($options) {
                            if ($dataType->type === 'relationship') return false;
                            if ($dataType->field === $options->column) return false;

                            return true;
                        })->all();
                    @endphp

                    <input type="hidden" name="{{ $options->column }}" value="{{ $dataTypeContent->id }}">
                    
                    {{-- Generate the form that the user can use to create a new child... --}}
                    @foreach ($dataTypeRows as $row)
                        <div class="form-group">
                            <label>{{ $row->display_name }}</label>
                            {!! app('voyager')->formField($row, $relationshipDataType, $relationshipDataTypeContent) !!}
                        </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush