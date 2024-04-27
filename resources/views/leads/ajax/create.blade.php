@php
$viewLeadAgentPermission = user()->permission('view_lead_agents');
$viewLeadCategoryPermission = user()->permission('view_lead_category');
$viewLeadSourcesPermission = user()->permission('view_lead_sources');
$addLeadAgentPermission = user()->permission('add_lead_agent');
$addLeadSourcesPermission = user()->permission('add_lead_sources');
$addLeadCategoryPermission = user()->permission('add_lead_category');
$addLeadNotePermission = user()->permission('add_lead_note');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-lead-data-form" >
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.lead.leadDetails')</h4>
                <div class="row p-20">

                    <div class="col-lg-4 col-md-6">
                        <x-forms.select fieldId="salutation" :fieldLabel="__('modules.client.salutation')"
                            fieldName="salutation">
                            <option value="">--</option>
                            @foreach ($salutations as $salutation)
                                <option value="{{ $salutation }}">@lang('app.'.$salutation)</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.text :fieldLabel="__('modules.lead.clientName')" fieldName="client_name"
                            fieldId="client_name" :fieldPlaceholder="__('placeholders.name')" fieldRequired="true" />
                    </div>

                    <div class="col-lg-4 col-md-6">

                        <x-forms.email fieldId="client_email" :fieldLabel="__('modules.lead.clientEmail')"
                            fieldName="client_email" :fieldPlaceholder="__('placeholders.email')" :fieldHelp="__('modules.lead.leadEmailInfo')">
                        </x-forms.email>
                    </div>


                    @if ($viewLeadAgentPermission != 'none')
                        <div class="col-lg-4 col-md-6">
                            <x-forms.label class="my-3" fieldId="" :fieldLabel="__('modules.tickets.chooseAgents')">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control select-picker" name="agent_id" id="agent_id"
                                    data-live-search="true">
                                    <option value="">--</option>
                                    @foreach ($leadAgents as $emp)
                                        <x-user-option :user="$emp->user" :userID="$emp->id" />
                                    @endforeach
                                </select>

                                @if ($addLeadAgentPermission == 'all' || $addLeadAgentPermission == 'added')
                                    <x-slot name="append">
                                        <button type="button"
                                            class="btn btn-outline-secondary border-grey add-lead-agent"
                                            data-toggle="tooltip" data-original-title="{{ __('app.add').'  '.__('app.new').' '.__('modules.tickets.agents') }}">@lang('app.add')</button>
                                    </x-slot>
                                @endif
                            </x-forms.input-group>
                        </div>
                    @endif

                    @if ($viewLeadSourcesPermission != 'none')
                        <div class="col-lg-4 col-md-6">
                            <x-forms.label class="my-3" fieldId="source_id" :fieldLabel="__('modules.lead.leadSource')">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control select-picker" name="source_id" id="source_id"
                                    data-live-search="true">
                                    <option value="">--</option>
                                    @foreach ($sources as $source)
                                        <option value="{{ $source->id }}">{{ mb_ucwords($source->type) }}</option>
                                    @endforeach
                                </select>

                                @if ($addLeadSourcesPermission == 'all' || $addLeadSourcesPermission == 'added')
                                    <x-slot name="append">
                                        <button type="button"
                                            class="btn btn-outline-secondary border-grey add-lead-source"
                                            data-toggle="tooltip" data-original-title="{{ __('app.add').' '.__('modules.lead.leadSource') }}">
                                            @lang('app.add')</button>
                                    </x-slot>
                                @endif
                            </x-forms.input-group>
                        </div>
                    @endif

                    @if ($viewLeadCategoryPermission != 'none')
                        <div class="col-lg-4 col-md-6">
                            <x-forms.label class="my-3" fieldId="category_id" :fieldLabel="__('modules.lead.leadCategory')">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control select-picker" name="category_id" id="category_id"
                                    data-live-search="true">
                                    <option value="">--</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ mb_ucwords($category->category_name) }}
                                        </option>
                                    @endforeach
                                </select>

                                @if ($addLeadCategoryPermission == 'all' || $addLeadCategoryPermission == 'added')
                                    <x-slot name="append">
                                        <button type="button"
                                            class="btn btn-outline-secondary border-grey add-lead-category"
                                            data-toggle="tooltip" data-original-title="{{ __('app.add').' '.__('modules.lead.leadCategory') }}">
                                            @lang('app.add')</button>
                                    </x-slot>
                                @endif
                            </x-forms.input-group>
                        </div>
                    @endif

                    <div class="col-lg-4 mt-2">
                        <x-forms.number class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.lead') .' '. __('app.value')"
                            fieldName="value" fieldId="value" />
                    </div>

                    <div class="col-lg-4 mt-2">
                        <x-forms.select fieldId="next_follow_up" fieldName="next_follow_up" :fieldLabel="__('app.next_follow_up')">
                            <option value="yes"> @lang('app.yes')</option>
                            <option value="no"> @lang('app.no')</option>
                        </x-forms.select>
                    </div>

                    <div class="col-lg-4 mt-2">
                        <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status">
                            <option value="">--</option>
                            @foreach ($status as $sts)
                                <option @if ($columnId == $sts->id) selected @endif value="{{ $sts->id }}">
                                    {{ ucfirst($sts->type) }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    @if ($addLeadNotePermission == 'all' || $addLeadNotePermission == 'added' || $addLeadNotePermission == 'both')
                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="note" :fieldLabel="__('app.note')">
                            </x-forms.label>
                            <div id="note"></div>
                            <textarea name="note" id="note-text" class="d-none"></textarea>
                        </div>
                    </div>
                    @endif
                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-top-grey">
                    <a href="javascript:;" class="text-dark toggle-other-details"><i class="fa fa-chevron-down"></i>
                        @lang('modules.client.companyDetails')</a>
                </h4>


                <div class="row p-20 d-none" id="other-details">

                    <div class="col-lg-3 col-md-6">
                        <x-forms.text :fieldLabel="__('modules.lead.companyName')" fieldName="company_name"
                            fieldId="company_name" :fieldPlaceholder="__('placeholders.company')" />
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.text :fieldLabel="__('modules.lead.website')" fieldName="website" fieldId="website"
                            :fieldPlaceholder="__('placeholders.website')" />
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.tel fieldId="mobile" :fieldLabel="__('modules.lead.mobile')" fieldName="mobile"
                            fieldPlaceholder="e.g. 987654321"></x-forms.tel>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.text :fieldLabel="__('modules.client.officePhoneNumber')" fieldName="office"
                            fieldId="office" fieldPlaceholder="" />
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.select fieldId="country" :fieldLabel="__('app.country')" fieldName="country"
                            search="true">
                            <option value="">--</option>
                            @foreach ($countries as $item)
                                <option data-tokens="{{ $item->iso3 }}"
                                    data-content="<span class='flag-icon flag-icon-{{ strtolower($item->iso) }} flag-icon-squared'></span> {{ $item->nicename }}"
                                    value="{{ $item->nicename }}">{{ $item->nicename }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.text :fieldLabel="__('modules.stripeCustomerAddress.state')" fieldName="state"
                            fieldId="state" fieldPlaceholder="" />
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.text :fieldLabel="__('modules.stripeCustomerAddress.city')" fieldName="city"
                            fieldId="city" fieldPlaceholder="" />
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.text :fieldLabel="__('modules.stripeCustomerAddress.postalCode')"
                            fieldName="postal_code" fieldId="postal_code" fieldPlaceholder="" />
                    </div>

                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.address')"
                                fieldName="address" fieldId="address" fieldPlaceholder="e.g. Rocket Road">
                            </x-forms.textarea>
                        </div>
                    </div>

                    <x-forms.custom-field :fields="$fields" class="col-md-12"></x-forms.custom-field>

                </div>



                <x-form-actions>
                    <x-forms.button-primary id="save-lead-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-secondary class="mr-3" id="save-more-lead-form" icon="check-double">@lang('app.saveAddMore')
                    </x-forms.button-secondary>
                    <x-forms.button-cancel :link="route('tasks.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>


<script src="{{ asset('vendor/jquery/dropzone.min.js') }}"></script>
<script>

    var add_lead_note_permission = "{{ $addLeadNotePermission }}";

    $(document).ready(function() {

        if ($('.custom-date-picker').length > 0) {
            datepicker('.custom-date-picker', {
                position: 'bl',
                ...datepickerConfig
            });
        }

        if(add_lead_note_permission == 'all' || add_lead_note_permission == 'added' || add_lead_note_permission == 'both') {

            quillImageLoad('#note');

        }

        $('#save-more-lead-form').click(function () {

            if(add_lead_note_permission == 'all' || add_lead_note_permission == 'added' || add_lead_note_permission == 'both') {
            var note = document.getElementById('note').children[0].innerHTML;
            document.getElementById('note-text').value = note;
            }

            const url = "{{ route('leads.store') }}";
            var data = $('#save-lead-data-form').serialize() + '&add_more=true';

            saveLead(data, url, "#save-more-lead-form");

        });

        $('#save-lead-form').click(function() {
            if(add_lead_note_permission == 'all' || add_lead_note_permission == 'added' || add_lead_note_permission == 'both') {
            var note = document.getElementById('note').children[0].innerHTML;
            document.getElementById('note-text').value = note;
            }

            const url = "{{ route('leads.store') }}";
            var data = $('#save-lead-data-form').serialize();

            saveLead(data, url, "#save-lead-form");

        });

        function saveLead(data, url, buttonSelector) {

            $.easyAjax({
                url: url,
                container: '#save-lead-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: buttonSelector,
                data: data,
                success: function(response) {
                    if(response.add_more == true) {

                        var right_modal_content = $.trim($(RIGHT_MODAL_CONTENT).html());

                        if(right_modal_content.length) {

                            $(RIGHT_MODAL_CONTENT).html(response.html.html);
                            $('#add_more').val(false);
                        }
                        else {

                            $('.content-wrapper').html(response.html.html);
                            init('.content-wrapper');
                            $('#add_more').val(false);
                        }
                    }
                    else {
                        window.location.href = response.redirectUrl;
                    }

                    if (typeof showTable !== 'undefined' && typeof showTable === 'function') {
                            showTable();
                    }
                }
            });

        }

        $('body').on('click', '.add-lead-agent', function() {
            var url = '{{ route('lead-agent-settings.create') }}';
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('body').on('click', '.add-lead-source', function() {
            var url = '{{ route('lead-source-settings.create') }}';
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('body').on('click', '.add-lead-category', function() {
            var url = '{{ route('leadCategory.create') }}';
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('.toggle-other-details').click(function() {
            $(this).find('svg').toggleClass('fa-chevron-down fa-chevron-up');
            $('#other-details').toggleClass('d-none');
        });

        init(RIGHT_MODAL);
    });

    function checkboxChange(parentClass, id){
        var checkedData = '';
        $('.'+parentClass).find("input[type= 'checkbox']:checked").each(function () {
            checkedData = (checkedData !== '') ? checkedData+', '+$(this).val() : $(this).val();
        });
        $('#'+id).val(checkedData);
    }
</script>
