@if (isset($fields) && count($fields) > 0)
    <div {{ $attributes->merge(['class' => 'row p-20']) }}>
        @foreach ($fields as $field)
            <div class="col-md-4">
                <div class="form-group">
                    @if ($field->type == 'text')
                        <x-forms.text
                            fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                            :fieldLabel="$field->label"
                            fieldName="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                            :fieldPlaceholder="$field->label"
                            :fieldRequired="($field->required == 'yes') ? 'true' : 'false'"
                            :fieldValue="$model->custom_fields_data['field_'.$field->id] ?? ''">
                        </x-forms.text>

                    @elseif($field->type == 'password')
                        <x-forms.password
                            fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                            :fieldLabel="$field->label"
                            fieldName="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                            :fieldPlaceholder="$field->label"
                            :fieldRequired="($field->required === 'yes') ? true : false"
                            :fieldValue="$model->custom_fields_data['field_'.$field->id] ?? ''">
                        </x-forms.password>

                    @elseif($field->type == 'number')
                        <x-forms.number
                            fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                            :fieldLabel="$field->label"
                            fieldName="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                            :fieldPlaceholder="$field->label"
                            :fieldRequired="($field->required === 'yes') ? true : false"
                            :fieldValue="$model->custom_fields_data['field_'.$field->id] ?? ''">
                        </x-forms.number>

                    @elseif($field->type == 'textarea')
                        <x-forms.textarea :fieldLabel="$field->label"
                                          fieldName="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                          fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                          :fieldRequired="($field->required === 'yes') ? true : false"
                                          :fieldPlaceholder="$field->label"
                                          :fieldValue="$model->custom_fields_data['field_'.$field->id] ?? ''">
                        </x-forms.textarea>

                    @elseif($field->type == 'radio')
                        <div class="form-group my-3">
                            <x-forms.label
                                fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                :fieldLabel="$field->label"
                                :fieldRequired="($field->required === 'yes') ? true : false">
                            </x-forms.label>
                            <div class="d-flex">
                                @foreach ($field->values as $key => $value)
                                    <x-forms.radio
                                        fieldId="optionsRadios{{ $key . $field->id }}"
                                        :fieldLabel="$value"
                                        fieldName="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                        :fieldValue="$value"
                                        :checked="($model && $model->custom_fields_data['field_'.$field->id] == $value) ? true : false"
                                    />
                                @endforeach
                            </div>
                        </div>



                    @elseif($field->type == 'select')
                        <div class="form-group my-3">
                            <x-forms.label
                                fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                :fieldLabel="$field->label"
                                :fieldRequired="($field->required === 'yes') ? true : false">
                            </x-forms.label>
                            {!! Form::select('custom_fields_data[' . $field->name . '_' . $field->id . ']',
 $field->values, $model ? $model->custom_fields_data['field_' . $field->id] : '',
  ['class' => 'form-control select-picker']) !!}
                        </div>
                    @elseif($field->type == 'date')
                        <x-forms.datepicker custom="true"
                                            fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                            :fieldRequired="($field->required === 'yes') ? true : false"
                                            :fieldLabel="$field->label"
                                            fieldName="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                            :fieldValue="($model && $model->custom_fields_data['field_'.$field->id] != '') ? \Carbon\Carbon::parse($model->custom_fields_data['field_'.$field->id])->format(company()->date_format) : now()->format(company()->date_format)"
                                            :fieldPlaceholder="$field->label"/>

                    @elseif($field->type == 'checkbox')
                        <div class="form-group my-3">
                            <x-forms.label
                                fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                :fieldLabel="$field->label"
                                :fieldRequired="($field->required === 'yes') ? true : false">
                            </x-forms.label>
                            <div class="d-flex checkbox-{{$field->id}}">

                                <input type="hidden" name="custom_fields_data[{{$field->name.'_'.$field->id}}]"
                                       id="{{$field->name.'_'.$field->id}}"
                                       value="{{ $model ? $model->custom_fields_data['field_'.$field->id]:''}}"
                                >

                                @foreach ($field->values as $key => $value)
                                    <x-forms.checkbox fieldId="optionsRadios{{ $key . $field->id }}"
                                                      :fieldLabel="$value"
                                                      :fieldName="$field->name.'_'.$field->id.'[]'"
                                                      :fieldValue="$value"
                                                      :fieldRequired="($field->required === 'yes') ? true : false"
                                                      onchange="checkboxChange('checkbox-{{$field->id}}', '{{$field->name.'_'.$field->id}}')"
                                                      :checked="$model && $model->custom_fields_data['field_'.$field->id] != '' && in_array($value ,explode(', ', $model->custom_fields_data['field_'.$field->id]))"
                                    />


                                @endforeach
                            </div>
                        </div>

                    @endif

                    <div class="form-control-focus"></div>
                    <span class="help-block"></span>
                </div>
            </div>
        @endforeach
    </div>
@endif
