@component('mail::message')
# You're Invited!

@if($isClientInvitation)
  You have been invited to join **URL Shortener** as a Client Admin for your company.
@else
  You have been invited to join **{{ $invitation->company->name }}** as a {{ $invitation->role }}.
@endif

@component('mail::button', ['url' => route('invitation.accept', $invitation->token)])
Accept Invitation
@endcomponent

This invitation link will expire in 7 days.

Thanks,<br>
{{ config('app.name') }}
@endcomponent