<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Leave;

class MultipleLeaveApplication extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $leave;
    private $multiDates;
    private $emailSetting;

    public function __construct(Leave $leave, $multiDates)
    {
        $this->leave = $leave;
        $this->multiDates = $multiDates;
        $this->company = $this->leave->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'new-leave-application')->first();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = ['database'];

        if ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $dates = explode(',', $this->multiDates);
        $emailDate = __('app.leaveDate') . '<br>';

        foreach ($dates as $key => $date) {
            $emailDate .= ($key + 1) . '. ' . $date . '<br>';
        }

        return parent::build()
            ->subject(__('email.leave.applied') . ' - ' . config('app.name'))
            ->greeting(__('email.hello') . ' ' . $notifiable->name . ',')
            ->markdown('mail.leaves.multiple', [
                'content' => $emailDate
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    //phpcs:ignore
    public function toArray($notifiable)
    {
        return $this->leave->toArray();
    }

}
