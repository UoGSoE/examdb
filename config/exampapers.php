<?php

return [
    'login_link_minutes' => 60,
    'check_passwords' => env('PASSWORD_CHECK', false),
    'sysadmin_email' => env('SYSADMIN_EMAIL'),
    'wlm_uri' => env('WLM_URI'),
    'api_key' => env('API_KEY', 'SET_ME_TO_SOMETHING_RANDOM'),
    'paper_subcategories' => [
        'main' => [
            "Paper Checklist",
            "---", // divider - disabled in the UI
            "Pre-Internally Moderated Paper",
            "Moderator Comments",
            "Post-Internally Moderated Paper",
            "Response To External Examiner",
            "Paper For Registry",
            "---", // divider - disabled in the UI
            "Pre-Internally Moderated Solutions",
            "Moderator Solution Comments",
            "Post-Internally Moderated Solutions",
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
