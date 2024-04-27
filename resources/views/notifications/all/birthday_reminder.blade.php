@php
    $name = '';
    $count = count($notification->data['birthday_name']) - 1;
    $name = $notification->data['birthday_name'][0]['name'];
    $date = \Carbon\Carbon::parse($notification->data['birthday_name'][0]['date_of_birth'])->timezone($global->timezone)->format('Y-m-d');
    $formatDate = \Carbon\Carbon::parse($date)->timezone($global->timezone)->format('j M');

    if ($count == 0) {
        $title = $name . ' ' . __('email.BirthdayReminder.birthdayNotification');
    }
    else {
        $title = $name . ' ' . __('app.and') . ' ' . $count . __('email.BirthdayReminder.birthdayNotificationText');
    }

    $notificationUser = \App\Models\User::findOrFail($notification->data['birthday_name'][0]['id']);

@endphp

@if (\Carbon\Carbon::today()->timezone($global->timezone)->toDateString() == $date)
    <x-cards.notification :notification="$notification"  :link="route('dashboard')" :image="$notificationUser->image_url"
            :title="$title . ' ' . __('app.today')" :time="$notification->created_at" />
@else
    <x-cards.notification :notification="$notification"  :link="route('dashboard')" :image="$notificationUser->image_url"
        :title="$title  . ' ' . $formatDate" :time="$notification->created_at" />
@endif
