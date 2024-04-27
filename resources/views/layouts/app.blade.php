<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="icon" type="image/png" sizes="16x16" href="{{ companyOrGlobalSetting()->favicon_url }}">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/css/all.min.css') }}">

    <!-- Simple Line Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/css/simple-line-icons.css') }}">

    <!-- Datepicker -->
    <link rel="stylesheet" href="{{ asset('vendor/css/datepicker.min.css') }}">

    <!-- TimePicker -->
    <link rel="stylesheet" href="{{ asset('vendor/css/bootstrap-timepicker.min.css') }}">

    <!-- Select Plugin -->
    <link rel="stylesheet" href="{{ asset('vendor/css/select2.min.css') }}">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/css/bootstrap-icons.css') }}">

    @stack('datatable-styles')

    <!-- Template CSS -->
    <link type="text/css" rel="stylesheet" media="all" href="{{ asset('css/main.css') }}">

    <title>@lang($pageTitle)</title>
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ companyOrGlobalSetting()->favicon_url }}">
    <meta name="theme-color" content="#ffffff">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    @isset($activeSettingMenu)
        <style>
            .preloader-container {
                margin-left: 510px;
                width: calc(100% - 510px)
            }

            .blur-code {
                filter: blur(3px);

            }

            .purchase-code {
                transition: filter .2s ease-out;
                margin-right: 4px;
            }


        </style>
    @endisset

    @stack('styles')

    <style>
        :root {
            --fc-border-color: #E8EEF3;
            --fc-button-text-color: #99A5B5;
            --fc-button-border-color: #99A5B5;
            --fc-button-bg-color: #ffffff;
            --fc-button-active-bg-color: #171f29;
            --fc-today-bg-color: #f2f4f7;
        }

        .fc a[data-navlink] {
            color: #99a5b5;
        }
    </style>

    {{-- Custom theme styles --}}
    @if (!user()->dark_theme)
        @include('sections.theme_css')
    @endif

    @if (file_exists(public_path() . '/css/app-custom.css'))
        <link href="{{ asset('css/app-custom.css') }}" rel="stylesheet">
    @endif

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery/modernizr.min.js') }}"></script>

    {{-- Timepicker --}}
    <script src="{{ asset('vendor/jquery/bootstrap-timepicker.min.js') }}"></script>

    @includeif('sections.push-setting-include')

    {{-- Include file for widgets if exist --}}
    @includeif('sections.custom_script')


    <script>
        const checkMiniSidebar = localStorage.getItem("mini-sidebar");
    </script>

</head>


<body id="body" class="{{ user()->dark_theme ? 'dark-theme' : '' }} {{ user()->rtl ? 'rtl' : '' }}">
<script>
    if (checkMiniSidebar == "yes" || checkMiniSidebar == "") {
        $('body').addClass('sidebar-toggled');
    }
</script>
{{-- include topbar --}}
@include('sections.topbar')

{{-- include sidebar menu --}}
@include('sections.sidebar')

<!-- BODY WRAPPER START -->
<div class="body-wrapper clearfix">


    <!-- MAIN CONTAINER START -->
    <section class="main-container bg-additional-grey mb-5 mb-sm-0" id="fullscreen">

        <div class="preloader-container d-flex justify-content-center align-items-center">
            <div class="spinner-border" role="status" aria-hidden="true"></div>
        </div>


        @yield('filter-section')

        <x-app-title class="d-block d-lg-none" :pageTitle="__($pageTitle)"></x-app-title>

        @yield('content')


    </section>
    <!-- MAIN CONTAINER END -->
</div>
<!-- BODY WRAPPER END -->
@include('sections.modals')

<!-- Global Required Javascript -->
<script src="{{ asset('js/main.js') }}"></script>
<script>
    const MODAL_DEFAULT = '#myModalDefault';
    const MODAL_LG = '#myModal';
    const MODAL_XL = '#myModalXl';
    const MODAL_HEADING = '#modelHeading';
    const RIGHT_MODAL = '#task-detail-1';
    const RIGHT_MODAL_CONTENT = '#right-modal-content';
    const RIGHT_MODAL_TITLE = '#right-modal-title';
    const company = @json(companyOrGlobalSetting());
    const pusher_setting = @json(pusher_settings());
    const SEARCH_KEYWORD = "{{ request('search_keyword') }}";
    const MOMENTJS_TIME_FORMAT = "{{ (companyOrGlobalSetting()->time_format == 'h:i A') ? 'hh:mm A' : ( (companyOrGlobalSetting()->time_format == 'h:i a') ? 'hh:mm a' : 'H:mm') }}";

    const datepickerConfig = {
        formatter: (input, date, instance) => {
            input.value = moment(date).format('{{ company()->moment_date_format }}')
        },
        showAllDates: true,
        customDays: ["@lang('app.weeks.Sun')", "@lang('app.weeks.Mon')", "@lang('app.weeks.Tue')",
            "@lang('app.weeks.Wed')", "@lang('app.weeks.Thu')", "@lang('app.weeks.Fri')",
            "@lang('app.weeks.Sat')"
        ],
        customMonths: ["@lang('app.months.January')", "@lang('app.months.February')",
            "@lang('app.months.March')", "@lang('app.months.April')", "@lang('app.months.May')",
            "@lang('app.months.June')", "@lang('app.months.July')", "@lang('app.months.August')",
            "@lang('app.months.September')", "@lang('app.months.October')",
            "@lang('app.months.November')", "@lang('app.months.December')"
        ],
        customOverlayMonths: ["@lang('app.monthsShort.Jan')", "@lang('app.monthsShort.Feb')",
            "@lang('app.monthsShort.Mar')", "@lang('app.monthsShort.Apr')",
            "@lang('app.monthsShort.May')", "@lang('app.monthsShort.Jun')",
            "@lang('app.monthsShort.Jul')", "@lang('app.monthsShort.Aug')",
            "@lang('app.monthsShort.Sep')", "@lang('app.monthsShort.Oct')",
            "@lang('app.monthsShort.Nov')", "@lang('app.monthsShort.Dec')"
        ],
        overlayButton: "@lang('app.submit')",
        overlayPlaceholder: "@lang('app.enterYear')",
        startDay: parseInt("{{ attendance_setting()->week_start_from }}")
    };

    const daterangeConfig = {
        "@lang('app.today')": [moment(), moment()],
        "@lang('app.last30Days')": [moment().subtract(29, 'days'), moment()],
        "@lang('app.thisMonth')": [moment().startOf('month'), moment().endOf('month')],
        "@lang('app.lastMonth')": [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month')
            .endOf(
                'month')
        ],
        "@lang('app.last90Days')": [moment().subtract(89, 'days'), moment()],
        "@lang('app.last6Months')": [moment().subtract(6, 'months'), moment()],
        "@lang('app.last1Year')": [moment().subtract(1, 'years'), moment()]
    };

    const daterangeLocale = {
        "format": "{{ companyOrGlobalSetting()->moment_date_format }}",
        "customRangeLabel": "@lang('app.customRange')",
        "separator": " @lang('app.to') ",
        "applyLabel": "@lang('app.apply')",
        "cancelLabel": "@lang('app.cancel')",
        "daysOfWeek": ['@lang("app.weeks.Sun")', '@lang("app.weeks.Mon")',
            '@lang("app.weeks.Tue")',
            '@lang("app.weeks.Wed")', '@lang("app.weeks.Thu")', '@lang("app.weeks.Fri")',
            '@lang("app.weeks.Sat")'
        ],
        "monthNames": [
            '@lang("app.months.January")',
            '@lang("app.months.February")',
            "@lang('app.months.March')",
            "@lang('app.months.April')",
            "@lang('app.months.May')",
            "@lang('app.months.June')",
            "@lang('app.months.July')",
            "@lang('app.months.August')",
            "@lang('app.months.September')",
            "@lang('app.months.October')",
            "@lang('app.months.November')",
            "@lang('app.months.December')"
        ],
        "firstDay": parseInt("{{ attendance_setting()->week_start_from }}")
    };

    const dropifyMessages = {
        default: "@lang('app.dragDrop')",
        replace: "@lang('app.dragDropReplace')",
        remove: "@lang('app.remove')",
        error: "@lang('messages.errorOccured')",
    };

    const DROPZONE_FILE_ALLOW = "{{ global_setting()->allowed_file_types }}";
    const DROPZONE_MAX_FILESIZE = "{{ global_setting()->allowed_file_size }}";
    Dropzone.prototype.defaultOptions.dictDefaultMessage = "{{ __('modules.projectTemplate.dropFile') }}";
    Dropzone.prototype.defaultOptions.timeout = 0;

</script>

<!-- Scripts -->
<script>
    window.Laravel = {!! json_encode([
    'csrfToken' => csrf_token(),
    'user' => user(),
]) !!};
</script>

@stack('scripts')

<script>
    $(window).on('load', function () {
        // Animate loader off screen
        init();
        $(".preloader-container").fadeOut("slow", function () {
            $(this).removeClass("d-flex");
        });
    });

    $('body').on('click', '.view-notification', function (event) {
        event.preventDefault();
        const id = $(this).data('notification-id');
        const href = $(this).attr('href');

        $.easyAjax({
            url: "{{ route('mark_single_notification_read') }}",
            type: "POST",
            data: {
                '_token': "{{ csrf_token() }}",
                'id': id
            },
            success: function () {
                if (typeof href !== 'undefined') {
                    window.location = href;
                }
            }
        });
    });

    $('body').on('click', '.img-lightbox', function () {
        const imageUrl = $(this).data('image-url');
        const url = "{{ route('invoices.show_image').'?image_url=' }}" + encodeURIComponent(imageUrl);
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    function updateOnesignalPlayerId(userId) {
        $.easyAjax({
            url: '{{ route('profile.update_onesignal_id') }}',
            type: 'POST',
            data: {
                'userId': userId,
                '_token': '{{ csrf_token() }}'
            }
        })
    }

    if (SEARCH_KEYWORD !== '' && $('#search-text-field').length > 0) {
        $('#search-text-field').val(SEARCH_KEYWORD);
        $('#reset-filters').removeClass('d-none');
    }

    $('body').on('click', '.show-hide-purchase-code', function () {
        $('> .icon', this).toggleClass('fa-eye-slash fa-eye');
        $(this).siblings('span').toggleClass('blur-code ');
    });


</script>

<script>
    let quillArray = {};

    function quillImageLoad(ID) {

        quillArray[ID] = new Quill(ID, {
            modules: {
                toolbar: [
                    // [{ align: '' }, { align: 'center' }, { align: 'right' }, { align: 'justify' }],
                    [{
                        header: [1, 2, 3, 4, 5, false]
                    }],
                    [{
                        'list': 'ordered'
                    }, {
                        'list': 'bullet'
                    }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['image', 'code-block', 'link'],
                    [{
                        'direction': 'rtl'
                    }],
                    ['clean']
                ],
                clipboard: {
                    matchVisual: false
                },
                "emoji-toolbar": true,
                "emoji-textarea": true,
                "emoji-shortname": true,
            },
            theme: 'snow'
        });
        $.each(quillArray, function (key, quill) {
            quill.getModule('toolbar').addHandler('image', selectLocalImage);
        });

    }

    /**
     * Step1. select local image
     *
     */
    function selectLocalImage() {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.click();

        // Listen upload local image and save to server
        input.onchange = () => {
            const file = input.files[0];

            // file type is only image.
            if (/^image\//.test(file.type)) {
                saveToServer(file);
            } else {
                console.warn('You could only upload images.');
            }
        };
    }

    /**
     * Step2. save to server
     *
     * @param {File} file
     */
    function saveToServer(file) {
        const fd = new FormData();
        fd.append('image', file);
        $.ajax({
            type: 'POST',
            url: "{{ route('image.store') }}",
            dataType: "json",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: fd,
            contentType: false,
            processData: false,
            success: function (response) {
                insertToEditor(response)
            },
        });
    }

    function insertToEditor(url) {
        // push image url to rich editor.
        $.each(quillArray, function (key, quill) {
            try {
                let range = quill.getSelection();
                quill.insertEmbed(range.index, 'image', url);
            } catch (err) {
            }
        });
    }
</script>

<script>
    $('body').on('click', '#pause-timer-btn, .pause-active-timer', function () {
        const id = $(this).data('time-id');
        let url = "{{ route('timelogs.pause_timer', ':id') }}";
        url = url.replace(':id', id);
        const token = '{{ csrf_token() }}';
        $.easyAjax({
            url: url,
            blockUI: true,
            type: "POST",
            disableButton: true,
            buttonSelector: "#pause-timer-btn",
            data: {
                timeId: id,
                _token: token
            },
            success: function (response) {
                if (response.status === 'success') {
                    if ($('#myActiveTimer').length > 0) {
                        $(MODAL_XL + ' .modal-content').html(response.html);

                        if ($('#allTasks-table').length) {
                            window.LaravelDataTables["allTasks-table"].draw(false);
                        }
                    } else {
                        window.location.reload();
                    }
                }
            }
        })
    });

    $('body').on('click', '#resume-timer-btn, .resume-active-timer', function () {
        const id = $(this).data('time-id');
        let url = "{{ route('timelogs.resume_timer', ':id') }}";
        url = url.replace(':id', id);
        const token = '{{ csrf_token() }}';
        $.easyAjax({
            url: url,
            blockUI: true,
            type: "POST",
            disableButton: true,
            buttonSelector: "#resume-timer-btn",
            data: {
                timeId: id,
                _token: token
            },
            success: function (response) {
                if (response.status === 'success') {
                    if ($('#myActiveTimer').length > 0) {
                        $(MODAL_XL + ' .modal-content').html(response.html);
                    } else {
                        window.location.reload();
                    }
                }
            }
        })
    });

    $('body').on('click', '.stop-active-timer', function () {
        const id = $(this).data('time-id');
        let url = "{{ route('timelogs.stop_timer', ':id') }}";
        url = url.replace(':id', id);
        const token = '{{ csrf_token() }}';
        $.easyAjax({
            url: url,
            type: "POST",
            data: {
                timeId: id,
                _token: token
            },
            success: function (response) {
                if ($('#myActiveTimer').length > 0) {
                    $(MODAL_XL + ' .modal-content').html(response.html);
                    if (response.activeTimerCount > 0) {
                        $('#show-active-timer .active-timer-count').html(response.activeTimerCount);
                    } else {
                        window.location.reload();
                    }
                } else {
                    window.location.reload();
                }
            }
        })

    });
</script>
</body>

</html>
