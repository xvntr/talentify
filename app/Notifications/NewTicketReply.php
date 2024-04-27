<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Support\HtmlString;

class NewTicketReply extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $ticket;
    private $ticketReply;
    private $emailSetting;

    public function __construct(TicketReply $ticket)
    {
        $this->ticketReply = $ticket;
        $this->ticket = $ticket->ticket;
        $this->company = $this->ticket->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'new-support-ticket-request')->first();
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

        if ($this->emailSetting->send_slack == 'yes' && $this->company->slackSetting->status == 'active' && $notifiable->isEmployee($notifiable->id)) {
            array_push($via, 'slack');
        }

        return $via;
    }

    public function toMail($notifiable)
    {
        $url = route('tickets.show', $this->ticket->ticket_number);
        $url = getDomainSpecificUrl($url, $this->company);


        $userReplied = TicketReply::orderBy('created_at', 'DESC')->first();

        if($userReplied->user_id == $notifiable->id)
        {
            $text = '<p>' . __('email.ticketReply.repliedText') . $this->ticket->ticket_number . '</p>';
        }
        else
        {
            $text = '<p>' . __('email.ticketReply.receivedText') . $this->ticket->ticket_number . '</p>';
        }

        $content = new HtmlString($text) . '<br>' . __('email.ticketReply.text') . $this->ticket->ticket_number . '<br>';

        return parent::build()
            ->subject(__('email.ticketReply.subject') . ' - ' . ucfirst($this->ticket->subject))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.ticketReply.action'),
                'notifiableName' => $notifiable->name
            ]);
    }

    public function toSlack($notifiable)
    {
        $slack = $notifiable->company->slackSetting;

        $message = (new SlackMessage())
            ->from(config('app.name'))
            ->image($slack->slack_logo_url);

        if (count($notifiable->employee) > 0 && (!is_null($notifiable->employee[0]->slack_username) && ($notifiable->employee[0]->slack_username != ''))) {

            return $message
                ->to('@' . $notifiable->employee[0]->slack_username)
                ->content('*' . __('email.ticketReply.subject') . '*' . "\n" . $this->ticket->subject . "\n" . __('modules.tickets.requesterName') . ' - ' . $this->ticket->requester->name . "\n" . '<' . route('tickets.show', $this->ticket->id) . '|' . __('modules.tickets.ticket') . ' #' . $this->ticket->id . '>' . "\n");
        }

        return $message->content('*' . __('email.ticketReply.subject') . '*' . "\n" .'This is a redirected notification. Add slack username for *' . $notifiable->name . '*');
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
            'id' => $this->ticket->id,
            'created_at' => $this->ticketReply->created_at->format('Y-m-d H:i:s'),
            'subject' => $this->ticket->subject,
            'user_id' => $this->ticketReply->user_id,
            'status' => $this->ticket->status,
            'agent_id' => $this->ticket->agent_id,
            'ticket_number' => $this->ticket->ticket_number
        ];
    }

}
