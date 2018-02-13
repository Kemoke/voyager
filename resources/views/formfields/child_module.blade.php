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
    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target=".bs-example-modal-lg">Add a new {{ str_singular($relationshipDataType->name) }}</button> <br/>
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

{{-- Child form modal... --}}
<div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">Add a new {{ str_singular($relationshipDataType->name) }}</h4>
            </div>
            <div class="modal-body" id="form">
                {{-- Generate the form that the user can use to generate a new child... --}}
                <form role="form"
                    class="form-edit-add"
                    action=""
                    method="POST" enctype="multipart/form-data">
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

                    @foreach ($dataTypeRows as $row)
                        <div class="form-group">
                            <label>{{ $row->display_name }}</label>
                            {!! app('voyager')->formField($row, $relationshipDataType, $relationshipDataTypeContent) !!}
                        </div>
                    @endforeach
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="save()">Save changes</button>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script type="text/javascript">
    function save() {
        var inputs = document.getElementById('form').getElementsByTagName('input');
        inputs = Array.from(inputs);

        var data = {};
        data['{{$options->column}}'] = {{ $dataTypeContent->id }};
        inputs.forEach((item) => {
            data[item.name] = item.value;
        });

        console.log(data);

        axios.post('{{ route('voyager.' . $relationshipDataType->slug . '.store') }}', data)
             .then(() => {
                window.location.reload()
            });
    }
</script>

<hr/>