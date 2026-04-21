<p>Dear {{$customer->title}} {{$customer->first_name}},</p>

<p>Your one time password for registering for Olympus My Voice App is {{$customer->email_otp ?? 'N/A' }}. This is valid for the next 10 minutes.</p>

<p>Regards,</p>
<p>Olympus Medical Systems India</p>
