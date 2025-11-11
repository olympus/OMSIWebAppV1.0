<?php

return [
    'driver' => env('FCM_PROTOCOL', 'http'),
    'log_enabled' => true,

    'http' => [
        'server_key' => env('FCM_SERVER_KEY', 'AAAA4svZ8Rw:APA91bEk6pJFiBelwQmFz4_0AqEWU-ucD8_pgog5lnWjY5GPmGKaUP_XUoBWKCiRlLf0J-_w-Yj9XoLRBbSVpsbrSK7DzEWCe_11Xkp08pftMutPwkzN_XhdiABy7h0PtWhfzf9CIjfU'),
        'sender_id' => env('FCM_SENDER_ID', '974082666780'),
        'server_send_url' => 'https://fcm.googleapis.com/fcm/send',
        'server_group_url' => 'https://android.googleapis.com/gcm/notification',
        'timeout' => 50.0, // in second
    ],
];
