<?php

return [
    'login_link_minutes' => 60,
    'check_passwords' => env('PASSWORD_CHECK', false),
    'sysadmin_email' => env('SYSADMIN_EMAIL'),
    'wlm_uri' => env('WLM_URI'),
    'api_key' => env('API_KEY'),
    'paper_subcategories' => [
        'main' => [
            "Pre-Internally Moderated Paper",
            "Moderator Comments",
            "Response To Moderator",
            "Post-Internally Moderated Paper",
            "Paper Checklist",
            "SIT Lecturer Comments",
            "Response To SIT Lecturer",
            "Response To External Examiner",
            "Paper For Registry",
        ],
        'solution' => [
            "Pre-Internally Moderated Solutions",
            "Moderator Solution Comments",
            "Response To Moderator (Solutions)",
            "Post-Internally Moderated Solutions",
            "Paper Checklist",
            "SIT Lecturer Comments (Solutions)",
            "Response To SIT Lecturer (Solutions)",
            "Response To External Examiner (Solutions)",
            "Solutions For Archive",
        ],
        'external' => [
            'main' => [
                "External Examiner Comments",
            ],
            'solution' => [
                "External Examiner Solution Comments",
            ],
        ],
    ],
];
