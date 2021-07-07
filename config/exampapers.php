<?php

return [
    'login_link_minutes' => 60,
    'check_passwords' => env('PASSWORD_CHECK', false),
    'sysadmin_email' => env('SYSADMIN_EMAIL'),
    'fallback_email' => env('FALLBACK_EMAIL'),
    'wlm_uri' => env('WLM_URI'),
    'api_key' => env('API_KEY', 'SET_ME_TO_SOMETHING_RANDOM'),
    'pdf_api_url' => env('PDF_API_URL'),
    'zip_expire_hours' => 2, // length of download link is valid, and file removed after expires
    'registry_temp_file_prefix' => 'ARGH',
    'checklist_temp_file_prefix' => 'MNGH',
    'delete_paper_limit_minutes' => 30,
    'paper_subcategories' => [
        'main' => [
            'Pre-Internally Moderated Paper',
            'Moderator Comments',
            'Post-Internally Moderated Paper',
            'Response To External Examiner',
            'Paper For Registry',
        ],
        'solution' => [
            'Pre-Internally Moderated Solutions',
            'Moderator Solution Comments',
            'Post-Internally Moderated Solutions',
            'Response To External Examiner (Solutions)',
            'Solutions For Archive',
        ],
        'assessment' => [
            'Pre-Internally Moderated Assessments',
            'Moderator Assessment Comments',
            'Post-Internally Moderated Assessments',
            'Response To External Examiner (Assessments)',
            'Assessments For Archive',
        ],
        'external' => [
            'External Examiner Comments',
            '---', // divider - disabled in the UI
            'External Examiner Solution Comments',
            '---', // divider - disabled in the UI
            'External Examiner Assessment Comments',
        ],
    ],
    'defaultDateOptions' => [
        [
          'label' => 'Receive call for exam papers from admin staff',
          'name' => 'date_receive_call_for_papers',
        ],
        [
          'label' => 'Deadline for Glasgow staff to submit exam materials to Management Database (staff are emailed 1 week before and again 1 day after the deadline if paperwork isn\'t complete)',
          'name' => 'glasgow_staff_submission_deadline',
        ],
        [
          'label' => 'Deadline for UESTC staff to submit exam materials to Management Database (staff are emailed 1 week before and again 1 day after deadline if paperwork isn\'t complete)',
          'name' => 'uestc_staff_submission_deadline',
        ],
        [
          'label' => 'Deadline for Internal moderation to be completed for UoG courses (staff are emailed 3 days before and again 1 day after the deadline if paperwork isn\'t complete)',
          'name' => 'glasgow_internal_moderation_deadline',
        ],
        [
          'label' => 'Deadline for Internal moderation to be completed for UESTC courses (staff are emailed 3 days before and again 1 day after the deadline if paperwork isn\'t complete)',
          'name' => 'uestc_internal_moderation_deadline',
        ],
        [
          'label' => 'Date UoG Teaching office will be notified to look at papers before alerting externals',
          'name' => 'date_remind_glasgow_office_externals',
        ],
        [
          'label' => 'Date UESTC Teaching office will be notified to look at papers before alerting externals',
          'name' => 'date_remind_uestc_office_externals',
        ],
        [
          'label' => 'Deadline for External moderation to be completed for UoG courses.',
          'name' => 'glasgow_external_moderation_deadline',
        ],
        [
          'label' => 'Deadline for External moderation to be completed for UESTC courses',
          'name' => 'uestc_external_moderation_deadline',
        ],
        [
          'label' => 'Deadline for print-ready version of UoG papers (UoG teaching office staff are emailed 1 day before and again 1 day after the deadline if paperwork isn\'t complete)',
          'name' => 'glasgow_print_ready_deadline',
        ],
        [
          'label' => 'Deadline for print-ready version of UESTC papers (UESTC teaching office staff are emailed 1 days before and again 1 day after the deadline if the paperwork isn\'t complete)',
          'name' => 'uestc_print_ready_deadline',
        ],
        [
          'label' => 'Start of Semester One',
          'name' => 'start_semester_1',
        ],
        [
          'label' => 'Start of Semester Two',
          'name' => 'start_semester_2',
        ],
        [
          'label' => 'Start of Semester Three',
          'name' => 'start_semester_3',
        ],
    ],
];
