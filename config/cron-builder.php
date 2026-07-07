<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Show the next run preview by default
    |--------------------------------------------------------------------------
    | Can be overridden per field with ->showNextRun(false).
    */
    'show_next_run' => true,

    /*
    |--------------------------------------------------------------------------
    | Default layout
    |--------------------------------------------------------------------------
    | 'grid' renders all five positions side by side; 'tabs' renders one
    | position at a time behind a tab bar. Can be overridden per field
    | with ->layout('tabs').
    */
    'layout' => 'grid',

    /*
    |--------------------------------------------------------------------------
    | Show cron tokens in tab headers
    |--------------------------------------------------------------------------
    | In the tabs layout, show each position's current cron token as a badge
    | next to the tab label. Can be overridden per field with
    | ->showTabTokens().
    */
    'show_tab_tokens' => false,
];
