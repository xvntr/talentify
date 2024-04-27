@php
$addExpenseCategoryPermission = user()->permission('manage_expense_category');
$approveExpensePermission = user()->permission('approve_expenses');
@endphp

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-expense-data-form">
            @method('PUT')
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('app.expense') @lang('app.details')</h4>
                <div class="row p-20">
                    <div class="col-md-6 col-lg-3">
                        <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.expenses.itemName')"
                            fieldName="item_name" fieldRequired="true" fieldId="item_name"
                            :fieldPlaceholder="__('placeholders.expense.item')" :fieldValue="$expense->item_name" />
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <x-forms.select :fieldLabel="__('modules.invoices.currency')" fieldName="currency_id"
                            fieldRequired="true" fieldId="currency_id">
                            @foreach ($currencies as $currency)
                                <option @if ($currency->id == $expense->currency_id) selected @endif value="{{ $currency->id }}" data-currency-name="{{ $currency->currency_name }}">
                                    {{ $currency->currency_name }} - ({{ $currency->currency_symbol }})
                                </option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <x-forms.text fieldId="exchange_rate" :fieldLabel="__('modules.currencySettings.exchangeRate')"
                        fieldName="exchange_rate" fieldRequired="true" :fieldValue="$expense->exchange_rate" :fieldReadOnly="($companyCurrency->id == $expense->currency_id)" 
                        fieldHelp="( {{company()->currency->currency_name}} {{__('app.to')}} {{$expense->currency->currency_name}} )"/>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <x-forms.number class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.price')" fieldName="price"
                            fieldRequired="true" fieldId="price" :fieldPlaceholder="__('placeholders.price')"
                            :fieldValue="$expense->price" />
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <x-forms.datepicker fieldId="purchase_date" fieldRequired="true"
                            :fieldLabel="__('modules.expenses.purchaseDate')" fieldName="purchase_date"
                            :fieldPlaceholder="__('placeholders.date')"
                            :fieldValue="$expense->purchase_date->format(company()->date_format)" />
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <x-forms.label class="mt-3" fieldId="project_id" :fieldLabel="__('app.project')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="project_id" id="project_id"
                                data-live-search="true" data-size="8">
                                <option value="">--</option>
                                @foreach ($projects as $project)
                                    <option @if ($project->id == $expense->project_id) selected @endif value="{{ $project->id }}">
                                        {{ mb_ucwords($project->project_name) }}
                                    </option>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>

                    @if (user()->permission('add_expenses') == 'all')
                        <div class="col-md-6 col-lg-4">
                            <x-forms.label class="mt-3" fieldId="user_id" :fieldLabel="__('app.employee')">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control select-picker" name="user_id" id="user_id"
                                    data-live-search="true" data-size="8">
                                    <option value="">--</option>
                                    @foreach ($employees as $item)
                                        <x-user-option :user="$item" :selected="$item->id == $expense->user_id" />
                                    @endforeach
                                </select>
                            </x-forms.input-group>
                        </div>
                    @else
                        <input type="hidden" name="user_id" value="{{ user()->id }}">
                    @endif

                    <div class="col-lg-4 col-md-6">
                        <x-forms.label class="mt-3" fieldId="category_id"
                            :fieldLabel="__('modules.expenses.expenseCategory')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="category_id" id="expense_category_id"
                                data-live-search="true">
                                <option value="">--</option>
                                @foreach ($categories as $category)
                                    <option @if ($category->id == $expense->category_id) selected @endif value="{{ $category->id }}">
                                        {{ mb_ucwords($category->category_name) }}
                                    </option>
                                @endforeach
                            </select>

                            @if ($addExpenseCategoryPermission == 'all' || $addExpenseCategoryPermission == 'added')
                                <x-slot name="append">
                                    <button id="addExpenseCategory" type="button"
                                        class="btn btn-outline-secondary border-grey"
                                        data-toggle="tooltip" data-original-title="{{__('modules.expenseCategory.addExpenseCategory') }}">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.text :fieldLabel="__('modules.expenses.purchaseFrom')" fieldName="purchase_from"
                            fieldId="purchase_from" :fieldPlaceholder="__('placeholders.expense.vendor')"
                            :fieldValue="$expense->purchase_from" />
                    </div>

                    @if (
                        $approveExpensePermission != 'none'
                        && (
                            $approveExpensePermission == 'all'
                            || ($approveExpensePermission == 'added' && $expense->added_by == user()->id)
                            || ($approveExpensePermission == 'owned' && $expense->user_id == user()->id)
                            || ($approveExpensePermission == 'both' && ($expense->user_id == user()->id || $expense->added_by == user()->id))
                            )
                        )
                        <div class="col-lg-4 col-md-6">
                            <x-forms.select :fieldLabel="__('app.status')" fieldName="status" fieldId="status">
                                <option @if ($expense->status == 'approved') selected @endif>@lang('app.approved')
                                </option>
                                <option @if ($expense->status == 'pending') selected @endif>@lang('app.pending')</option>
                                <option @if ($expense->status == 'rejected') selected @endif>@lang('app.rejected')
                                </option>
                            </x-forms.select>
                        </div>
                    @endif

                    <div class="col-lg-12">
                        <x-forms.file :fieldLabel="__('app.bill')" fieldName="bill" fieldId="bill"
                            :fieldValue="$expense->bill_url" allowedFileExtensions="txt pdf doc xls xlsx docx rtf png jpg jpeg svg" :popover="__('messages.fileFormat.multipleImageFile')" />
                    </div>

                    <div class="col-lg-3 col-md-6 col-sm-12">
                        @if (!is_null($expense->bill))
                            <x-file-card :fileName="$expense->bill" :dateAdded="$expense->created_at->diffForHumans()">
                                <i class="fa fa-file text-lightest"></i>
                                <x-slot name="action">
                                    <div class="dropdown ml-auto file-action">
                                        <button
                                            class="btn btn-lg f-14 p-0 text-lightest text-capitalize rounded  dropdown-toggle"
                                            type="button" data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                            <i class="fa fa-ellipsis-h"></i>
                                        </button>

                                        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                            aria-labelledby="dropdownMenuLink" tabindex="0">
                                            <a class="cursor-pointer d-block text-dark-grey f-13 py-3 px-3 "
                                                target="_blank" href="{{ $expense->bill_url }}">@lang('app.view')</a>
                                        </div>
                                    </div>
                                </x-slot>
                            </x-file-card>
                        @endif

                    </div>


                </div>

                <x-forms.custom-field :fields="$fields" :model="$expense"></x-forms.custom-field>

                <x-form-actions>
                    <x-forms.button-primary id="save-expense-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('expenses.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>

<script>
    $(document).ready(function() {
        if ($('.custom-date-picker').length > 0) {
            datepicker('.custom-date-picker', {
                position: 'bl',
                ...datepickerConfig
            });
        }

        const dp1 = datepicker('#purchase_date', {
            position: 'bl',
            dateSelected: new Date("{{ str_replace('-', '/', $expense->purchase_date) }}"),
            ...datepickerConfig
        });

        let categoryId = $('#expense_category_id').val();
        let userId = $('#user_id').val();
        getExpenseCategoryEmp(userId, categoryId);
        getEmployeeProjectCat(userId, categoryId);

        $('#user_id').change(function() {
            let userId = $(this).val();
            let categoryId = $('#expense_category_id').val();

            getEmployeeProjectCat(userId, categoryId);
        });

        $('#expense_category_id').change(function() {
            let categoryId = $(this).val();
            let userId = $('#user_id').val();
            getExpenseCategoryEmp(userId, categoryId);
        });

        function getEmployeeProjectCat(userId, categoryId) {
            const url = "{{ route('expenses.get_employee_projects') }}";

            $.easyAjax({
                url: url,
                type: "GET",
                data: {'userId' : userId, 'categoryId' : categoryId},
                success: function(response) {
                    $('#project_id').html('<option value="">--</option>'+response.data);
                    $('#project_id').selectpicker('refresh')
                    $('#expense_category_id').html('<option value="">--</option>'+response.category);
                    $('#expense_category_id').selectpicker('refresh')
                }
            });

        }

        function getExpenseCategoryEmp(userId, categoryId) {
            const url = "{{ route('expenses.get_category_employees') }}";

            $.easyAjax({
                url: url,
                type: "GET",
                data: {'categoryId' : categoryId, 'userId' : userId},
                success: function(response) {
                    $('#user_id').html('<option value="">--</option>'+response.employees);
                    $('#user_id').selectpicker('refresh')
                }
            });
        }

        $('#save-expense-form').click(function() {
            const url = "{{ route('expenses.update', $expense->id) }}";
            var data = $('#save-expense-data-form').serialize();

            $.easyAjax({
                url: url,
                container: '#save-expense-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-expense-form",
                data: data,
                file: true
            });
        });

        $('#addExpenseCategory').click(function() {
            const url = "{{ route('expenseCategory.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
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

    $('#currency_id').change(function() {
            var id = $(this).val();
            var companyCurrencyName = "{{$companyCurrency->currency_name}}";
            var currentCurrencyName = $('#currency_id option:selected').attr('data-currency-name');
            var companyCurrency = '{{ $companyCurrency->id }}';

            if(id == companyCurrency){
                $('#exchange_rate').prop('readonly', true);
            } else{
                $('#exchange_rate').prop('readonly', false);
            }
            var url = "{{ route('invoices.get_exchange_rate', ':id') }}";
            url = url.replace(':id', id);
            var token = "{{ csrf_token() }}";

            $.easyAjax({
                url: url,
                type: "GET",
                blockUI: true,
                data: {
                    _token: token
                },
                success: function(response) {
                    if (response.status == 'success') {
                        $('#exchange_rate').val(response.data);
                        $('#exchange_rateHelp').html('( '+companyCurrencyName+' @lang('app.to') '+currentCurrencyName+' )');
                    }
                }
            });
        });
</script>
