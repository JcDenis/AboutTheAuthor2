<?php

/**
 * @file
 * @brief       The plugin AboutTheAuthor2 definition
 * @ingroup     AboutTheAuthor2
 *
 * @defgroup    AboutTheAuthor2 Plugin AboutTheAuthor2.
 *
 * Takes from plugin "About the author" aboutTheAuhtor by Pierre Boinelle.
 *
 * @author      Jean-Christian Paul Denis
 * @copyright   AGPL-3.0
 */
declare(strict_types=1);

$this->registerModule(
    'About the author',
    'Displays information about the author of entries or comments',
    'Jean-Christian Paul Denis and Contributors',
    '0.7.1',
    [
        'requires'    => [
            ['core', '2.36'],
            //['FrontendSession', '0.30'], // optional
            //['commentsWikibar', '6.4'], // optional
            //['legacyMarkdown', '7.8'], // optional
        ],
        'settings'    => [
            'blog' => '#params.' . $this->id . '_params',
            'pref' => '#user-options.' . $this->id . '_prefs',
        ],
        'permissions' => 'My',
        'priority'    => 3000, // somewhere after plugin FrontendSession
        'type'        => 'plugin',
        'support'     => 'https://github.com/JcDenis/' . $this->id . '/issues',
        'details'     => 'https://github.com/JcDenis/' . $this->id . '/',
        'repository'  => 'https://raw.githubusercontent.com/JcDenis/' . $this->id . '/master/dcstore.xml',
        'date'        => '2025-12-25T19:59:21+00:00',
    ]
);