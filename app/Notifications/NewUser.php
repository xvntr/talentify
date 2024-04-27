<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\User;
use Illuminate\Notifications\Messages\SlackMessage;

class NewUser extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $password;
    private $emailSetting;

    public function __construct(User $user, $password)
    {
        $this->password = $password;
        $this->company = $user->company;

        // When there is company of user.
        if ($this->company) {
            $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'user-registrationadded-by-admin')->first();
        }

    }

    /**
     * Get the notification's delivery channels.
     *t('mail::layout')
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = ['database'];

        if (is_null($this->company)) {
            array_push($via, 'mail');

            return $via;
        }

        if ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }


        if ($this->emailSetting->send_slack == 'yes' && $this->company->slackSetting->status == 'active') {
            array_push($via, 'slack');
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
        $url = route('login');
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.newUser.text') . '<br>' . __('app.email') . ':- ' . $notifiable->email . '<br>' . __('app.password') . ':- ' . $this->password;

        return parent::build()
            ->subject(__('email.newUser.subject') . ' ' . config('app.name') . '.')
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company ? $this->company->header_color : null,
                'actionText' => __('email.newUser.action'),
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
        return $notifiable->toArray();
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable)
    {

        $slack = $notifiable->company->slackSetting;

        $slackMessage = (new SlackMessage())
            ->from(config('app.name'))
            ->image($slack->slack_logo_url);

        try {

            $url = route('login');
            $url = getDomainSpecificUrl($url, $this->company);

            //phpcs:ignore
            return $slackMessage
                    //phpcs:ignore
                ->to('@' . $notifiable->employee[0]->slack_username)
                //phpcs:ignore
                ->content('*' . __('email.newUser.subject') . ' ' . config('app.name') . '!*' . "\n" . __('email.newUser.text')) . "\n" . '<' . $url . '|' . __('email.newUser.action') . '>';

        } catch (\Exception $e) {
            return $slackMessage->content('*' . __('email.newUser.subject') . '*' . "\n" .'This is a redirected notification. Add slack username for *' . $notifiable->name . '*');
        }

    }

}
