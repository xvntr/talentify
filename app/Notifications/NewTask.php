<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Task;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class NewTask extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $task;
    private $emailSetting;

    public function __construct(Task $task)
    {
        $this->task = $task;
        $this->emailSetting = EmailNotificationSetting::userAssignTask();
        $this->company = $this->task->company;
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

        if ($this->emailSetting->send_slack == 'yes' && $this->company->slackSetting->status == 'active') {
            array_push($via, 'slack');
        }

        if ($this->emailSetting->send_push == 'yes') {
            array_push($via, OneSignalChannel::class);
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
        $url = route('tasks.show', $this->task->id);
        $url = getDomainSpecificUrl($url, $this->company);

        $dueDate = (!is_null($this->task->due_date)) ? $this->task->due_date->format($this->company->date_format) : null;

        $content = ucfirst($this->task->heading) . ' #' . $this->task->task_short_code . '<p>
            <b style="color: green">' . __('app.dueDate') . ': ' . $dueDate . '</b>
        </p>';

        return parent::build()
            ->subject(__('email.newTask.subject') . ' #' . $this->task->task_short_code . ' - ' . config('app.name') . '.')
            ->greeting(__('email.hello') . ' ' . $notifiable->name . ',')
            ->markdown('mail.task.created', [
                'url' => $url,
                'content' => $content,
                'name' => $notifiable->name,
                'themeColor' => $this->company->header_color
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->task->id,
            'created_at' => $this->task->created_at->format('Y-m-d H:i:s'),
            'heading' => $this->task->heading
        ];
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
        $dueDate = (!is_null($this->task->due_date)) ? $this->task->due_date->format($this->company->date_format) : null;

        if (count($notifiable->employee) > 0 && (!is_null($notifiable->employee[0]->slack_username) && ($notifiable->employee[0]->slack_username != ''))) {
            return (new SlackMessage())
                ->from(config('app.name'))
                ->image($slack->slack_logo_url)
                ->to('@' . $notifiable->employee[0]->slack_username)
                ->content('*' . __('email.newTask.subject') . '*' . "\n" . '<' . route('tasks.show', $this->task->id) . '|' . ucfirst($this->task->heading) . '>' . "\n" . ' #' . $this->task->task_short_code . "\n" . __('app.dueDate') . ': ' . $dueDate . (!is_null($this->task->project) ? "\n" . __('app.project') . ' - ' . ucfirst($this->task->project->project_name) : ''));
        }

        return (new SlackMessage())
            ->from(config('app.name'))
            ->image($slack->slack_logo_url)
            ->content('*' . __('email.newTask.subject') . '*' . "\n" .'This is a redirected notification. Add slack username for *' . $notifiable->name . '*');
    }

    // phpcs:ignore
    public function toOneSignal($notifiable)
    {
        return OneSignalMessage::create()
            ->subject(__('email.newTask.subject'))
            ->body($this->task->heading);
    }

}
