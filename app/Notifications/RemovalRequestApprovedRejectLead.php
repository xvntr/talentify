<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class RemovalRequestApprovedRejectLead extends BaseNotification
{


    protected $type;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($type)
    {

        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = array();

        if ($notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        if ($this->type == 'approved') {

            $content = __('email.removalRequestApprovedLead.text');

            return parent::build()
                ->subject(__('email.removalRequestApprovedLead.subject') . ' ' . config('app.name') . '.')
                ->markdown('mail.email', [
                    'content' => $content,
                    'notifiableName' => mb_ucwords($notifiable->client_name)
                ]);
        }

        $content = __('email.removalRequestRejectedLead.text');

        return parent::build()
            ->subject(__('email.removalRequestRejectedLead.subject') . ' ' . config('app.name') . '.')
            ->markdown('mail.email', [
                'content' => $content,
                'notifiableName' => mb_ucwords($notifiable->client_name)
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
        return [
            //
        ];
    }

}
