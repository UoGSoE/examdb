<?php

return [
    'login_link_minutes' => 60,
    'check_passwords' => env('PASSWORD_CHECK', false),
    'sysadmin_email' => env('SYSADMIN_EMAIL'),
    'fallback_email' => env("FALLBACK_EMAIL"),
    'wlm_uri' => env('WLM_URI'),
    'api_key' => env('API_KEY', 'SET_ME_TO_SOMETHING_RANDOM'),
    'zip_expire_hours' => 2, // length of download link is valid, and file removed after expires
    'registry_temp_file_prefix' => 'ARGH',
    'checklist_temp_file_prefix' => 'MNGH',
    'delete_paper_limit_minutes' => 30,
    'paper_subcategories' => [
        'main' => [
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
            "External Examiner Comments",
            "---", // divider - disabled in the UI
            "External Examiner Solution Comments",
        ],
    ],
];
