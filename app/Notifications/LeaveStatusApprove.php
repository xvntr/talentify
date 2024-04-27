<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Leave;

class LeaveStatusApprove extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $leave;
    private $emailSetting;

    public function __construct(Leave $leave)
    {
        $this->leave = $leave;
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
        $url = route('leaves.show', $this->leave->id);
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.leave.approve') . '<br>' . __('app.date') . ': ' . $this->leave->leave_date->format($this->company->date_format) . '<br>' . __('app.status') . ': ' . mb_ucwords($this->leave->status) . '<br>';

        if(!is_null($this->leave->approve_reason))
        {
            $content .= __('messages.reasonForLeaveApproval') . ': ' . mb_ucwords($this->leave->approve_reason);
        }

        return parent::build()
            ->subject(__('email.leaves.statusSubject') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.leaves.action'),
                'notifiableName' => $notifiable->name
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
