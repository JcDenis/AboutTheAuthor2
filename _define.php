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

if (!isset($this) || !is_object($this) || !method_exists($this, 'registerModule') || !isset($this->id) || !is_string($this->id)) {
    return;
}

$this->registerModule(
    'About the author',
    'Displays information about the author of entries or comments',
    'Jean-Christian Paul Denis and Contributors',
    '0.9',
    [
        'requires'    => [
            ['core', '2.39'],
            //['FrontendSession', '0.30'], // optional
            //['commentsWikibar', '7.5'], // optional
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
        'date'        => '2026-08-12T17:34:47+00:00',
    ]
);