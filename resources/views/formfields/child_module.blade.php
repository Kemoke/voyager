@php
    // Instantiate a new model which we can use in the rest of the component...
    $model = app()->make($options->model); 
    // Retrieve the datatype of the relationship and preset the id of parent...
    $relationshipDataType = \TCG\Voyager\Models\DataType::where('model_name', $options->model)->first();
    $relationshipDataTypeContent = $model;
    $relationshipDataTypeContent->{$options->column} = $dataTypeContent->id;
@endphp

@if (str_contains(request()->path(), 'create'))
    <p>You can add more {{ $row->display_name }} when you create an album and go to edit.</p>
@else
    <p>
        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target=".form-modal-{{ $relationshipDataType->name }}">Add a new {{ str_singular($relationshipDataType->name) }}</button> <br/>
    </p>

    <div class="table-responsive">
        <table id="dataTable" class="table table-hover">
            <thead>
            <tr>
                @foreach($relationshipDataType->browseRows as $row)
                    <th>{{ $row->display_name }}</th>
                @endforeach
                <th class="actions">{{ __('voyager.generic.actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($model->where($options->column, $dataTypeContent->id)->get() as $data)
                <tr>
                    @foreach($relationshipDataType->browseRows as $row)
                        <?php $rowOptions = json_decode($row->details); ?>
                        <td>
                            @if($row->type === 'image')
                                <img src="@if( !filter_var($data->{$row->field}, FILTER_VALIDATE_URL)){{ Voyager::image( $data->{$row->field} ) }}@else{{ $data->{$row->field} }}@endif" style="width:100px">
                            @elseif($row->type === 'text')
                                @include('voyager::multilingual.input-hidden-bread-browse')
                                <div class="readmore">{{ mb_strlen( $data->{$row->field} ) > 200 ? mb_substr($data->{$row->field}, 0, 200) . ' ...' : $data->{$row->field} }}</div>
                            @else
                                @include('voyager::multilingual.input-hidden-bread-browse')
                                <span>{{ $data->{$row->field} }}</span>
                            @endif
                        </td>
                    @endforeach
                    <td class="no-sort no-click" id="bread-actions">
                        @can('delete', $data)
                            <a href="javascript:;" title="{{ __('voyager.generic.delete') }}" class="btn btn-sm btn-danger pull-right delete" data-id="{{ $data->{$data->getKeyName()} }}" id="delete-{{ $data->{$data->getKeyName()} }}">
                                <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">{{ __('voyager.generic.delete') }}</span>
                            </a>
                        @endcan
                        @can('edit', $data)
                            <a href="javascript:;" onclick="$('.edit-modal-{{ $data->{$data->getKeyName()} }}').modal('show');" data-id="{{ $data->{$data->getKeyName()} }}" title="{{ __('voyager.generic.edit') }}" class="btn btn-sm btn-primary pull-right edit">
                                <i class="voyager-edit"></i> <span class="hidden-xs hidden-sm">{{ __('voyager.generic.edit') }}</span>
                            </a>
                        @endcan
                        @can('read', $data)
                            <a href="javascript:;" data-id="{{ $data->{$data->getKeyName()} }}" title="{{ __('voyager.generic.view') }}" class="btn btn-sm btn-warning pull-right view">
                                <i class="voyager-eye"></i> <span class="hidden-xs hidden-sm">{{ __('voyager.generic.view') }}</span>
                            </a>
                        @endcan
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <hr/>

    @push('modals')
        {{-- Create modal... --}}
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

                            {{-- Generate the form that the user can use to create a new child... --}}
                            @foreach ($dataTypeRows as $row)
                                <div class="form-group">
                                    <label>{{ $row->display_name }}</label>
                                    {!! app('voyager')->formField($row, $relationshipDataType, $relationshipDataTypeContent) !!}
                                </div>
                            @endforeach

                            <input type="hidden" name="{{ $options->column }}" value="{{ $dataTypeContent->id }}">
                            <input type="hidden" name="child_module[parent]" value="{{ $dataType->name }}">
                            <input type="hidden" name="child_module[parent_id]" value="{{ $dataTypeContent->id }}">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Edit modal... --}}
        @foreach ($model->where($options->column, $dataTypeContent->id)->get() as $data)
            <div class="modal fade edit-modal-{{ $data->id }}" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form role="form"
                              class="form-edit-add"
                              action="{{ route('voyager.'.$relationshipDataType->slug.'.update', ['id' => $data->id]) }}"
                              method="POST" enctype="multipart/form-data" id="edit_form">
                            <div class="modal-header card blue" >
                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                <h4 class="modal-title">Edit {{ str_singular($relationshipDataType->name) }}</h4>
                            </div>
                            <div class="modal-body" id="form">
                                {{ csrf_field() }}
                                {{ method_field("PUT") }}

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
                                        {!! app('voyager')->formField($row, $relationshipDataType, $data) !!}
                                    </div>
                                @endforeach

                                <input type="hidden" name="{{ $options->column }}" value="{{ $dataTypeContent->id }}">
                                <input type="hidden" name="child_module[parent]" value="{{ $dataType->name }}">
                                <input type="hidden" name="child_module[parent_id]" value="{{ $dataTypeContent->id }}">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Delete modal --}}
        <div class="modal modal-danger fade" tabindex="-1" id="delete_modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('voyager.generic.close') }}"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="voyager-trash"></i> {{ __('voyager.generic.delete_question') }} {{ strtolower($relationshipDataType->display_name_singular) }}?</h4>
                    </div>
                    <div class="modal-footer">
                        <form action="#" id="delete_form" method="POST">
                            {{ method_field("DELETE") }}
                            {{ csrf_field() }}
                            <input type="submit" class="btn btn-danger pull-right delete-confirm"
                                   value="{{ __('voyager.generic.delete_confirm') }} {{ strtolower($relationshipDataType->display_name_singular) }}">

                            <input type="hidden" name="child_module[parent]" value="{{ $dataType->name }}">
                            <input type="hidden" name="child_module[parent_id]" value="{{ $dataTypeContent->id }}">
                        </form>
                        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">{{ __('voyager.generic.cancel') }}</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
    @endpush
@endif

@section('javascript')
    <!-- DataTables -->
    @if(!$relationshipDataType->server_side && config('dashboard.data_tables.responsive'))
        <script src="{{ voyager_asset('lib/js/dataTables.responsive.min.js') }}"></script>
    @endif
    <script>
        $(document).ready(function () {
                    @if (!$relationshipDataType->server_side)
            var table = $('#dataTable').DataTable({!! json_encode(
                        array_merge([
                            "order" => [],
                            "language" => __('voyager.datatable'),
                        ],
                        config('voyager.dashboard.data_tables', []))
                    , true) !!});
            @else
            $('#search-input select').select2({
                minimumResultsForSearch: Infinity
            });
            @endif
        });

        // User clicks on the edit button...
        $('td').on('click', '.edit', function (e) {
            $('#edit_form')[0].action = '{{ route('voyager.'.$relationshipDataType->slug.'.update', ['id' => '__id']) }}'.replace('__id', $(this).data('id'));
        });

        var deleteFormAction;
        $('td').on('click', '.delete', function (e) {
            $('#delete_form')[0].action = '{{ route('voyager.'.$relationshipDataType->slug.'.destroy', ['id' => '__id']) }}'.replace('__id', $(this).data('id'));
            $('#delete_modal').modal('show');
        });
    </script>
@endsection






