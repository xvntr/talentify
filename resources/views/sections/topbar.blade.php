<!-- HEADER START -->
<header class="main-header clearfix bg-white" id="header">


    <!-- NAVBAR LEFT(MOBILE MENU COLLAPSE) START-->
    <div class="navbar-left float-left d-flex align-items-center">
        <x-app-title class="d-none d-lg-flex" :pageTitle="__($pageTitle)"></x-app-title>

        <div class="d-block d-lg-none menu-collapse cursor-pointer position-relative" onclick="openMobileMenu()">
            <div class="mc-wrap">
                <div class="mcw-line"></div>
                <div class="mcw-line center"></div>
                <div class="mcw-line"></div>
            </div>
        </div>

        @if (in_array('admin', user_roles()) && $checkListCompleted < $checkListTotal && App::environment('codecanyon'))
            <div class="ml-3 d-none d-lg-block d-md-block">
                <span class="f-12 mb-1"><a href="{{ route('checklist') }}" class="text-lightest ">
                        @lang('modules.accountSettings.setupProgress')</a>
                    <span class="float-right">{{ $checkListCompleted }}/{{ $checkListTotal }}</span>
                </span>
                <div class="progress" style="height: 5px; width: 150px">
                    <div class="progress-bar bg-primary" role="progressbar"
                         style="width: {{ ($checkListCompleted / $checkListTotal) * 100 }}%;" aria-valuenow="25"
                         aria-valuemin="0" aria-valuemax="100">&nbsp;
                    </div>
                </div>
            </div>
        @endif

    </div>

    <!-- NAVBAR LEFT(MOBILE MENU COLLAPSE) END-->
    <!-- NAVBAR RIGHT(SEARCH, ADD, NOTIFICATION, LOGOUT) START-->
    <div class="page-header-right float-right d-flex align-items-center">

        @if (!is_null($selfActiveTimer))

            <span class="border rounded f-14 py-2 px-2 d-none d-sm-block mr-3">
                <span id="active-timer" class="mr-2">{{ $selfActiveTimer->timer }}</span> 

                @if (is_null($selfActiveTimer->activeBreak))
                    <a href="javascript:;" class="pause-active-timer mr-1 border-right" data-toggle="tooltip" data-original-title="{{ __('modules.timeLogs.pauseTimer') }}" data-time-id="{{ $selfActiveTimer->id }}">
                        <i class="fa fa-pause-circle text-primary"></i>
                    </a>
                    <a href="javascript:;" class="stop-active-timer" data-toggle="tooltip" data-original-title="{{ __('modules.timeLogs.stopTimer') }}" data-time-id="{{ $selfActiveTimer->id }}">
                        <i class="fa fa-stop-circle text-danger"></i>
                    </a>
                @else
                    <a href="javascript:;" class="resume-active-timer" data-toggle="tooltip" data-original-title="{{ __('modules.timeLogs.resumeTimer') }}" data-time-id="{{ $selfActiveTimer->activeBreak->id }}">
                        <i class="fa fa-play-circle text-primary"></i>
                    </a>
                @endif

            </span>

            @if (is_null($selfActiveTimer->activeBreak))
                <a href="javascript:;" class='btn-danger btn btn-sm rounded mr-3 f-14 py-2 px-2 stop-active-timer d-block d-sm-none mr-2' data-time-id="{{ $selfActiveTimer->id }}">
                    {{ __('modules.timeLogs.stopTimer') }}
                </a>            
            @endif
        @endif

        <ul>
            <!-- SEARCH START -->
            <li data-toggle="tooltip" data-placement="top" title="{{__('app.search')}}" class="d-none d-sm-block">
                <div class="d-flex align-items-center">
                    <a href="javascript:;" class="d-block header-icon-box open-search">
                        <i class="fa fa-search f-16 text-dark-grey"></i>
                    </a>
                </div>
            </li>
            <!-- SEARCH END -->
            <!-- Sticky Note START -->
            <li data-toggle="tooltip" data-placement="top" title="{{__('app.menu.stickyNotes')}}" class="d-none d-sm-block">
                <div class="d-flex align-items-center">
                    <a href="{{ route('sticky-notes.index') }}" class="d-block header-icon-box openRightModal">
                        <i class="fa fa-sticky-note f-16 text-dark-grey"></i>
                    </a>
                </div>
            </li>
            <!-- Sticky Note END -->

        @if (!in_array('client', user_roles()))

            @if (in_array('timelogs', user_modules()) && (add_timelogs_permission() == 'all' || add_timelogs_permission() == 'added' || manage_active_timelogs() == 'all'))
                <!-- START TIMER -->
                    <li data-toggle="tooltip" data-placement="top" title="{{__('modules.timeLogs.startTimer')}}">
                        <div class="add_box dropdown">
                            <a class="d-block dropdown-toggle header-icon-box" type="link" id="show-active-timer"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-clock f-16 text-dark-grey"></i>
                                @if ($activeTimerCount > 0)
                                    <span
                                        class="badge badge-primary active-timer-count position-absolute">{{ $activeTimerCount }}</span>
                                @endif
                            </a>
                        @if ($activeTimerCount == 0)
                            <!-- DROPDOWN - INFORMATION -->
                                <div class="dropdown-menu dropdown-menu-right" id="active-timer-list"
                                     aria-labelledby="dropdownMenuLink" tabindex="0">
                                    <a class="dropdown-item text-primary f-w-500" href="javascript:;"
                                       id="start-timer-modal">
                                        <i class="fa fa-play mr-2"></i>
                                        @lang("modules.timeLogs.startTimer")
                                    </a>
                                </div>
                            @endif
                        </div>
                    </li>
                    <!-- START TIMER END -->
            @endif

            <!-- ADD START -->
                <li data-toggle="tooltip" data-placement="top" title="{{__('app.createNew')}}">
                    <div class="add_box dropdown">
                        <a class="d-block dropdown-toggle header-icon-box" type="link" data-toggle="dropdown"
                           aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-plus-circle f-16 text-dark-grey"></i>
                        </a>
                        <!-- DROPDOWN - INFORMATION -->
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink" tabindex="0">
                            @if (in_array('projects', user_modules()) && (add_project_permission() == 'all' || add_project_permission() == 'added'))
                                <a class="dropdown-item f-14 text-dark openRightModal"
                                   href="{{ route('projects.create') }}">
                                    <i class="fa fa-plus f-w-500 mr-2 f-11"></i>
                                    @lang('app.add') @lang('app.project')
                                </a>
                            @endif

                            @if (in_array('tasks', user_modules()) && (add_tasks_permission() == 'all' || add_tasks_permission() == 'added'))
                                <a class="dropdown-item f-14 text-dark openRightModal"
                                   href="{{ route('tasks.create') }}">
                                    <i class="fa fa-plus f-w-500 mr-2 f-11"></i>
                                    @lang('app.add') @lang('app.task')
                                </a>
                            @endif

                            @if (in_array('clients', user_modules()) && (add_clients_permission() == 'all' || add_clients_permission() == 'added'))
                                <a class="dropdown-item f-14 text-dark openRightModal"
                                   href="{{ route('clients.create') }}">
                                    <i class="fa fa-plus f-w-500 mr-2 f-11"></i>
                                    @lang('app.add') @lang('app.client')
                                </a>
                            @endif

                            @if (in_array('employees', user_modules()) && (add_employees_permission() == 'all' || add_employees_permission() == 'added'))
                                <a class="dropdown-item f-14 text-dark openRightModal"
                                   href="{{ route('employees.create') }}">
                                    <i class="fa fa-plus f-w-500 mr-2 f-11"></i>
                                    @lang('app.add') @lang('app.employee')
                                </a>
                            @endif

                            @if (in_array('payments', user_modules()) && (add_payments_permission() == 'all' || add_payments_permission() == 'added'))
                                <a class="dropdown-item f-14 text-dark openRightModal"
                                   href="{{ route('payments.create') }}">
                                    <i class="fa fa-plus f-w-500 mr-2 f-11"></i>
                                    @lang('modules.payments.addPayment')
                                </a>
                            @endif

                            @if (in_array('tickets', user_modules()) && (add_tickets_permission() == 'all' || add_tickets_permission() == 'added'))
                                <a class="dropdown-item f-14 text-dark openRightModal"
                                   href="{{ route('tickets.create') }}">
                                    <i class="fa fa-plus f-w-500 mr-2 f-11"></i>
                                    @lang('app.add') @lang('modules.tickets.ticket')
                                </a>
                            @endif
                        </div>
                    </div>
                </li>
                <!-- ADD END -->
        @endif

        <!-- NOTIFICATIONS START -->
            <li data-toggle="tooltip" data-placement="top" title="{{__('app.newNotifications')}}">
                <div class="notification_box dropdown">
                    <a class="d-block dropdown-toggle header-icon-box show-user-notifications" type="link"
                       data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-bell f-16 text-dark-grey"></i>
                        @if ($unreadNotificationCount > 0)
                            <span
                                class="badge badge-primary unread-notifications-count active-timer-count position-absolute">{{ $unreadNotificationCount }}</span>
                        @endif
                    </a>
                    <!-- DROPDOWN - INFORMATION -->
                    <div
                        class="dropdown-menu dropdown-menu-right notification-dropdown border-0 shadow-lg py-0 bg-additional-grey"
                        tabindex="0">
                        <div
                            class="d-flex px-3 justify-content-between align-items-center border-bottom-grey py-1 bg-white">
                            <div class="___class_+?50___">
                                <p class="f-14 mb-0 text-dark f-w-500">@lang('app.newNotifications')</p>
                            </div>
                            @if ($unreadNotificationCount > 0)
                                <div class="f-12 ">
                                    <a href="javascript:;"
                                       class="text-dark-grey mark-notification-read">@lang('app.markRead')</a> |
                                    <a href="{{ route('all-notifications') }}"
                                       class="text-dark-grey">@lang('app.showAll')</a>
                                </div>
                            @endif
                        </div>
                        <div id="notification-list">

                        </div>

                        @if($unreadNotificationCount > 6)
                            <div class="d-flex px-3 pb-1 pt-2 justify-content-center bg-additional-grey">
                                <a href="{{ route('all-notifications') }}"
                                   class="text-darkest-grey f-13">@lang('app.showAll')</a>
                            </div>
                        @endif
                    </div>
                </div>
            </li>
            <!-- NOTIFICATIONS END -->
            <!-- LOGOUT START -->
            <li data-toggle="tooltip" data-placement="top" title="{{__('app.logout')}}">
                <div class="logout_box">
                    <a class="d-block header-icon-box" href="javascript:;" onclick="event.preventDefault();
                    document.getElementById('logout-form').submit();">
                        <i class="fa fa-power-off f-16 text-dark-grey"></i>
                    </a>
                </div>
            </li>
            <!-- LOGOUT END -->
        </ul>
    </div>
    <!-- NAVBAR RIGHT(SEARCH, ADD, NOTIFICATION, LOGOUT) START-->
</header>
<!-- HEADER END -->

<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<script>
    $(document).ready(function () {
        const activeTimerCount = parseInt("{{ $activeTimerCount }}");

        $('#start-timer-modal').click(function () {
            const url = "{{ route('timelogs.show_timer') }}";
            $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_XL, url);
        });

        $('.open-search').click(function () {
            const url = "{{ route('search.index') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        if (activeTimerCount > 0) {

            $('#show-active-timer').click(function () {
                const url = "{{ route('timelogs.show_active_timer') }}";
                $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_XL, url);
            });

        }

        $('.show-user-notifications').click(function () {
            const openStatus = $(this).attr('aria-expanded');

            if (typeof openStatus == "undefined" || openStatus == "false") {

                const token = '{{ csrf_token() }}';
                $.easyAjax({
                    type: 'POST',
                    url: "{{ route('show_notifications') }}",
                    container: "#notification-list",
                    blockUI: true,
                    data: {
                        '_token': token
                    },
                    success: function (data) {
                        if (data.status === 'success') {
                            $('#notification-list').html(data.html);
                        }
                    }
                });

            }

        });

        $('.mark-notification-read').click(function () {
            const token = '{{ csrf_token() }}';
            $.easyAjax({
                type: 'POST',
                url: "{{ route('mark_notification_read') }}",
                blockUI: true,
                data: {
                    '_token': token
                },
                success: function (data) {
                    if (data.status === 'success') {
                        $('#notification-list').html('');
                        $('.unread-notifications-count').remove();
                        window.location.reload();
                    }
                }
            });

        });

        var $worked = $("#active-timer");
        var activeBreak = "{{ (!is_null($selfActiveTimer) && !is_null($selfActiveTimer->activeBreak)) }}";

        function updateTimerTask() {
            var myTime = $worked.html();
            var ss = myTime.split(":");

            var hours = ss[0];
            var mins = ss[1];
            var secs = ss[2];
            secs = parseInt(secs) + 1;

            if (secs > 59) {
                secs = '00';
                mins = parseInt(mins) + 1;
            }

            if (mins > 59) {
                secs = '00';
                mins = '00';
                hours = parseInt(hours) + 1;
            }

            if (hours.toString().length < 2) {
                hours = '0' + hours;
            }
            if (mins.toString().length < 2) {
                mins = '0' + mins;
            }
            if (secs.toString().length < 2) {
                secs = '0' + secs;
            }
            var ts = hours + ':' + mins + ':' + secs;

            $worked.html(ts);
            setTimeout(updateTimerTask, 1000);
        }

        if ($('#active-timer').length && activeBreak != '1') {
            setTimeout(updateTimerTask, 1000);
        }

    });
</script>
