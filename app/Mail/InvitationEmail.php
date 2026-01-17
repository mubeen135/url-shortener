<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvitationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $invitation;
    public $isClientInvitation;

    public function __construct(Invitation $invitation, $isClientInvitation = false)
    {
        $this->invitation = $invitation;
        $this->isClientInvitation = $isClientInvitation;
    }

    public function build()
    {
        $subject = $this->isClientInvitation 
            ? 'Invitation to join URL Shortener as a Client Admin'
            : 'Invitation to join ' . $this->invitation->company->name;

        return $this->subject($subject)
                    ->markdown('emails.invitation')
                    ->with([
                        'invitation' => $this->invitation,
                        'isClientInvitation' => $this->isClientInvitation,
                    ]);
    }
}