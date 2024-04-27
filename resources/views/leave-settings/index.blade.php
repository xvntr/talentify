@extends('layouts.app')

@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        <x-setting-sidebar :activeMenu="$activeSettingMenu" />

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <nav class="tabs px-4 border-bottom-grey">
                        <div class="nav" id="nav-tab" role="tablist">

                            <a class="nav-item nav-link f-15 active type" data-toggle="tab"
                                href="{{ route('leaves-settings.index') }}?tab=type" role="tab" aria-controls="nav-type"
                                aria-selected="true">@lang($pageTitle)</a>

                            <a class="nav-item nav-link f-15 general" data-toggle="tab"
                                href="{{ route('leaves-settings.index') }}?tab=general" role="tab"
                                aria-controls="nav-general"
                                aria-selected="false">@lang('app.menu.leaveGenralSettings')</a>

                        </div>
                    </nav>
                </div>
            </x-slot>

            <x-slot name="buttons">
                <div class="row">
                    <div class="col-md-12 mb-2">
                        <x-forms.button-primary icon="plus" id="addNewLeaveType" class="addNewLeaveType mb-2 actionBtn">
                            @lang('app.addNew') @lang('modules.leaves.leaveType')
                        </x-forms.button-primary>
                    </div>
                </div>
            </x-slot>

            {{-- Include tabs here --}}
            @include($view)

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->
@endsection

@push('scripts')

    <script>

        $('.nav-item').removeClass('active');
        const activeTab = "{{ $activeTab }}";
        $('.' + activeTab).addClass('active');

        showBtn(activeTab);

        function showBtn(activeTab) {
            (activeTab == 'general') ? $('.actionBtn').addClass('d-none') : $('.actionBtn').removeClass('d-none')
        }

        $("body").on("click", "#editSettings .nav a", function(event) {
            event.preventDefault();

            $('.nav-item').removeClass('active');
            $(this).addClass('active');

            const requestUrl = this.href;

            $.easyAjax({
                url: requestUrl,
                blockUI: true,
                container: "#nav-tabContent",
                historyPush: true,
                success: function(response) {
                    if (response.status == "success") {
                        showBtn(response.activeTab);
                        $('#nav-tabContent').html(response.html);
                        init('#nav-tabContent');
                    }
                }
            });
        });

        $('.editNewLeaveType').click(function() {

            var id = $(this).data('leave-id');

            var url = "{{ route('leaveType.edit', ':id ') }}";
            url = url.replace(':id', id);

            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $(MODAL_LG).on('shown.bs.modal', function () {
        $('#page_reload').val('true')
        })

    </script>
@endpush
