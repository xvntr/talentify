@php
    $addProjectCategoryPermission = user()->permission('manage_project_category');
    $addEmployeePermission = user()->permission('add_employees');
    $addProjectFilePermission = user()->permission('add_project_files');
    $addPublicProjectPermission = user()->permission('create_public_project');
    $addProjectMemberPermission = user()->permission('add_project_members');
    $addProjectNotePermission = user()->permission('add_project_note');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-project-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('app.project') @lang('app.details')</h4>
                <input type="hidden" name="template_id" value="{{ $projectTemplate->id ?? '' }}">
                <div class="row p-20">
                    <div class="col-lg-6 col-md-6">
                        <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.taskShortCode')"
                                      fieldName="project_code" fieldRequired="true" fieldId="project_code"
                                      :fieldPlaceholder="__('placeholders.writeshortcode')"/>
                    </div>

                    <div class="col-lg-6 col-md-6">
                        <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.projects.projectName')"
                                      fieldName="project_name" fieldRequired="true" fieldId="project_name"
                                      :fieldPlaceholder="__('placeholders.project')"
                                      :fieldValue="$projectTemplate->project_name ?? ''"/>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <x-forms.datepicker fieldId="start_date" fieldRequired="true"
                                            :fieldLabel="__('modules.projects.startDate')" fieldName="start_date"
                                            :fieldPlaceholder="__('placeholders.date')"/>
                    </div>

                    <div class="col-md-6 col-lg-4" id="deadlineBox">
                        <x-forms.datepicker fieldId="deadline" fieldRequired="true"
                                            :fieldLabel="__('modules.projects.deadline')" fieldName="deadline"
                                            :fieldPlaceholder="__('placeholders.date')"/>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox fieldId="without_deadline"
                                                  :fieldLabel="__('modules.projects.withoutDeadline')"
                                                  fieldName="without_deadline"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <x-forms.label class="my-3" fieldId="category_id"
                                       :fieldLabel="__('modules.projects.projectCategory')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="category_id" id="project_category_id"
                                    data-live-search="true">
                                <option value="">--</option>
                                @foreach ($categories as $category)
                                    <option
                                        @if ($projectTemplate && $projectTemplate->category_id == $category->id) selected
                                        @endif
                                        value="{{ $category->id }}">
                                        {{ mb_ucwords($category->category_name) }}
                                    </option>
                                @endforeach
                            </select>

                            @if ($addProjectCategoryPermission == 'all' || $addProjectCategoryPermission == 'added')
                                <x-slot name="append">
                                    <button id="addProjectCategory" type="button"
                                            class="btn btn-outline-secondary border-grey"
                                            data-toggle="tooltip" data-original-title="{{__('modules.projectCategory.addProjectCategory') }}">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    @if (!in_array('client', user_roles()))
                        <div class="col-md-4">
                            <x-forms.label class="my-3" fieldId="department" :fieldLabel="__('app.department')">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control select-picker" name="team_id" id="employee_department"
                                        data-live-search="true">
                                    <option value="">--</option>
                                    @foreach ($teams as $team)
                                        <option value="{{ $team->id }}">{{ mb_ucfirst($team->team_name) }}</option>
                                    @endforeach
                                </select>
                            </x-forms.input-group>
                        </div>
                    @endif

                    <div class="col-md-4 @if (!isset($client) && is_null($client)) py-3 @endif">
                        @if (isset($client) && !is_null($client))
                            <x-forms.label class="my-3" fieldId="client_id" :fieldLabel="__('app.client')">
                            </x-forms.label>

                            <input type="hidden" name="client_id" id="client_id" value="{{ $client->id }}">
                            <input type="text" value="{{ ucfirst($client->name) }}"
                                   class="form-control height-35 f-15 readonly-background" readonly>
                        @else
                            <x-client-selection-dropdown :clients="$clients" fieldRequired="false"
                                                         :selected="request('default_client') ?? null"/>
                        @endif
                    </div>

                    @if ($addProjectNotePermission == 'all' || $addProjectNotePermission == 'added')
                        <div class="col-md-12 col-lg-6">
                            <div class="form-group my-3">
                                <x-forms.label class="my-3" fieldId="project_summary"
                                               :fieldLabel="__('modules.projects.projectSummary')">
                                </x-forms.label>
                                <div id="project_summary">{!! $projectTemplate->project_summary ?? '' !!}</div>
                                <textarea name="project_summary" id="project_summary-text"
                                          class="d-none">{!! $projectTemplate->project_summary ?? '' !!}</textarea>
                            </div>
                        </div>
                    @else
                        <div class="col-md-12 col-lg-12">
                            <div class="form-group my-3">
                                <x-forms.label class="my-3" fieldId="project_summary"
                                               :fieldLabel="__('modules.projects.projectSummary')">
                                </x-forms.label>
                                <div id="project_summary">{!! $projectTemplate->project_summary ?? '' !!}</div>
                                <textarea name="project_summary" id="project_summary-text"
                                          class="d-none">{!! $projectTemplate->project_summary ?? '' !!}</textarea>
                            </div>
                        </div>
                    @endif

                    @if ($addProjectNotePermission == 'all' || $addProjectNotePermission == 'added')
                        <div class="col-md-12 col-lg-6">
                            <div class="form-group my-3">
                                <x-forms.label class="my-3" fieldId="notes"
                                               :fieldLabel="__('modules.projects.note')">
                                </x-forms.label>
                                <div id="notes">{!! $projectTemplate->notes ?? '' !!}</div>
                                <textarea name="notes" id="notes-text"
                                          class="d-none">{!! $projectTemplate->notes ?? '' !!}</textarea>
                            </div>
                        </div>
                    @endif

                    @if ($addPublicProjectPermission == 'all')
                        <div class="col-sm-12">
                            <div class="form-group">
                                <div class="d-flex mt-2">
                                    <x-forms.checkbox fieldId="is_public"
                                                      :fieldLabel="__('modules.projects.createPublicProject')"
                                                      fieldName="public"/>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($addProjectMemberPermission == 'all' || $addProjectMemberPermission == 'added')
                        <div class="col-md-12" id="add_members">
                            <div class="form-group my-3">
                                <x-forms.label class="my-3" fieldId="selectEmployee" fieldRequired="true"
                                               :fieldLabel="__('modules.projects.addMemberTitle')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control multiple-users" multiple name="user_id[]"
                                            id="selectEmployee" data-live-search="true" data-size="8">
                                        @foreach ($employees as $item)
                                            <x-user-option
                                                :user="$item"
                                                :pill="true"
                                                :selected="(request()->has('default_assign') && request('default_assign') == $item->id) ||(isset($projectTemplateMembers) && in_array($item->id, $projectTemplateMembers))"
                                            />
                                        @endforeach
                                    </select>

                                    @if ($addEmployeePermission == 'all' || $addEmployeePermission == 'added')
                                        <x-slot name="append">
                                            <button id="add-employee" type="button"
                                                    class="btn btn-outline-secondary border-grey"
                                                    data-toggle="tooltip" data-original-title="{{ __('modules.projects.addMemberTitle') }}">@lang('app.add')</button>
                                        </x-slot>
                                    @endif
                                </x-forms.input-group>
                            </div>
                        </div>
                    @elseif(in_array('employee', user_roles()))
                        <input type="hidden" name="user_id[]" value="{{ user()->id }}">
                    @endif

                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-top-grey">
                    <a href="javascript:;" class="text-dark toggle-project-other-details"><i
                            class="fa fa-chevron-down"></i>
                        @lang('modules.client.clientOtherDetails')</a>
                </h4>

                <div class="row p-20 d-none" id="other-project-details">
                    @if ($addProjectFilePermission == 'all' || $addProjectFilePermission == 'added')
                        <div class="col-lg-12">
                            <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2"
                                                   :fieldLabel="__('app.add') . ' ' . __('app.file')" fieldName="file"
                                                   fieldId="file-upload-dropzone"/>
                            <input type="hidden" name="projectID" id="projectID">
                        </div>
                    @endif

                    <div class="col-lg-4">
                        <x-forms.select fieldId="currency_id" :fieldLabel="__('modules.invoices.currency')"
                                        fieldName="currency_id" search="true">
                            @foreach ($currencies as $currency)
                                <option @if (company()->currency_id == $currency->id) selected @endif
                                value="{{ $currency->id }}">
                                    {{ $currency->currency_symbol . ' (' . $currency->currency_code . ')' }}
                                </option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.number class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.projects.projectBudget')"
                                        fieldName="project_budget" fieldId="project_budget"
                                        :fieldPlaceholder="__('placeholders.price')"/>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.number class="mr-0 mr-lg-2 mr-md-2"
                                        :fieldLabel="__('modules.projects.hours_allocated')" fieldName="hours_allocated"
                                        fieldId="hours_allocated" :fieldPlaceholder="__('placeholders.hourEstimate')"/>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox fieldId="manual_timelog"
                                                  :fieldLabel="__('modules.projects.manualTimelog')"
                                                  fieldName="manual_timelog"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox fieldId="miroboard_checkbox"
                                                  :fieldLabel="__('modules.projects.enableMiroboard')"
                                                  fieldName="miroboard_checkbox"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-6 d-none" id="miroboard_detail">
                        <div class="form-group my-3">
                            <div class="row">
                                <div class="col-md-6 mt-6">
                                    <x-forms.text class="mr-0 mr-lg-2 mr-md-2"
                                                  :fieldLabel="__('modules.projects.miroBoardId')"
                                                  fieldName="miro_board_id" fieldRequired="true"
                                                  fieldId="miro_board_id"/>
                                </div>
                                <div class="col-md-6 col-lg-6">
                                    <div class="form-group">
                                        <div class="d-flex mt-5">
                                            <x-forms.checkbox fieldId="client_access"
                                                              :fieldLabel="__('modules.projects.clientMiroAccess')"
                                                              fieldName="client_access"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-6 col-lg-4" id="clientNotification">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox fieldId="client_task_notification"
                                                  :fieldLabel="__('modules.projects.clientTaskNotification')"
                                                  fieldName="client_task_notification"/>
                            </div>
                        </div>
                    </div>
                        <x-forms.custom-field :fields="$fields" class="col-md-12"></x-forms.custom-field>
                </div>


                <x-form-actions>
                    <x-forms.button-primary id="save-project-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('projects.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>


<script src="{{ asset('vendor/jquery/dropzone.min.js') }}"></script>
<script>
    var add_project_files = "{{ $addProjectFilePermission }}";
    var add_project_note_permission = "{{ $addProjectNotePermission }}";

    $(document).ready(function () {

        if ($('.custom-date-picker').length > 0) {
            datepicker('.custom-date-picker', {
                position: 'bl',
                ...datepickerConfig
            });
        }

        if (add_project_files == "all") {

            Dropzone.autoDiscover = false;
            //Dropzone class
            myDropzone = new Dropzone("div#file-upload-dropzone", {
                dictDefaultMessage: "{{ __('app.dragDrop') }}",
                url: "{{ route('files.multiple_upload') }}",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                paramName: "file",
                maxFilesize: DROPZONE_MAX_FILESIZE,
                maxFiles: 10,
                autoProcessQueue: false,
                uploadMultiple: true,
                addRemoveLinks: true,
                parallelUploads: 10,
                acceptedFiles: DROPZONE_FILE_ALLOW,
                init: function () {
                    myDropzone = this;
                }
            });
            myDropzone.on('sending', function (file, xhr, formData) {
                var ids = $('#projectID').val();
                formData.append('project_id', ids);
            });
            myDropzone.on('uploadprogress', function () {
                $.easyBlockUI();
            });
            myDropzone.on('completemultiple', function () {
                var msgs = "@lang('modules.projects.projectUpdated')";
                var redirect_url = $('#redirect_url').val();
                if (redirect_url != '') {
                    window.location.href = decodeURIComponent(redirect_url);
                }
                window.location.href = "{{ route('projects.index') }}"
            });
        }


        $("#selectEmployee").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: function (selected, total) {
                return selected + " {{ __('app.membersSelected') }} ";
            }
        });

        quillImageLoad('#project_summary');

        if (add_project_note_permission == 'all' || add_project_note_permission == 'added') {

            quillImageLoad('#notes');
        }


        const dp1 = datepicker('#start_date', {
            position: 'bl',
            onSelect: (instance, date) => {
                dp2.setMin(date);
            },
            ...datepickerConfig
        });

        const dp2 = datepicker('#deadline', {
            position: 'bl',
            onSelect: (instance, date) => {
                dp1.setMax(date);
            },
            ...datepickerConfig
        });

        $('#without_deadline').click(function () {
            const check = $('#without_deadline').is(":checked") ? true : false;
            if (check == true) {
                $('#deadlineBox').hide();
            } else {
                $('#deadlineBox').show();
            }
        });

        $('#save-project-form').click(function () {
            let note = document.getElementById('project_summary').children[0].innerHTML;
            document.getElementById('project_summary-text').value = note;

            if (add_project_note_permission == 'all' || add_project_note_permission == 'added') {

                note = document.getElementById('notes').children[0].innerHTML;
                document.getElementById('notes-text').value = note;
            }

            const url = "{{ route('projects.store') }}";
            const data = $('#save-project-data-form').serialize();

            $.easyAjax({
                url: url,
                container: '#save-project-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-project-form",
                data: data,
                success: function (response) {
                    if ((add_project_files === "all") &&
                        myDropzone.getQueuedFiles().length > 0) {
                        $('#projectID').val(response.projectID);
                        myDropzone.processQueue();
                    } else if (typeof response.redirectUrl !== 'undefined') {
                        window.location.href = response.redirectUrl;
                    }
                }
            });
        });

        $('#addProjectCategory').click(function () {
            const url = "{{ route('projectCategory.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#department-setting').click(function () {
            const url = "{{ route('departments.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#client_view_task').change(function () {
            $('#clientNotification').toggleClass('d-none');
        });

        $('.toggle-project-other-details').click(function () {
            $(this).find('svg').toggleClass('fa-chevron-down fa-chevron-up');
            $('#other-project-details').toggleClass('d-none');
        });

        $('#is_public').change(function () {
            $('#add_members').toggleClass('d-none');
        });

        $('#miroboard_checkbox').change(function () {
            $('#miroboard_detail').toggleClass('d-none');
        });

        $('#add-employee').click(function () {
            $(MODAL_XL).modal('show');

            const url = "{{ route('employees.create') }}";

            $.easyAjax({
                url: url,
                blockUI: true,
                container: MODAL_XL,
                success: function (response) {
                    if (response.status === "success") {
                        $(MODAL_XL + ' .modal-body').html(response.html);
                        $(MODAL_XL + ' .modal-title').html(response.title);
                        init(MODAL_XL);
                    }
                }
            });
        });

        init(RIGHT_MODAL);
    });

    function checkboxChange(parentClass, id) {
        let checkedData = '';
        $('.' + parentClass).find("input[type= 'checkbox']:checked").each(function () {
            checkedData = (checkedData !== '') ? checkedData + ', ' + $(this).val() : $(this).val();
        });
        $('#' + id).val(checkedData);
    }

    $('#save-project-data-form').on('change', '#employee_department', function () {
        let id = $(this).val();
        if (id === '') {
            id = 0;
        }
        let url = "{{ route('departments.members', ':id') }}";
        url = url.replace(':id', id);

        $.easyAjax({
            url: url,
            type: "GET",
            container: '#save-project-data-form',
            blockUI: true,
            redirect: true,
            success: function (data) {
                $('#selectEmployee').html(data.data);
                $('#selectEmployee').selectpicker('refresh');
            }
        })
    });
</script>
