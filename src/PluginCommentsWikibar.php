<?php

declare(strict_types=1);

namespace Dotclear\Plugin\AboutTheAuthor2;

use Dotclear\App;
use Dotclear\Plugin\commentsWikibar\My as Wikibar;
use Dotclear\Plugin\commentsWikibar\FrontendBehaviors as WikibarHelper;

/**
 * @brief       AboutTheAuthor2 module frontend behaviors.
 * @ingroup     AboutTheAuthor2
 *
 * @author      Jean-Christian Paul Denis
 * @copyright   AGPL-3.0
 */
class PluginCommentsWikibar
{
    /**
     * Load JS and CSS and add wiki bar.
     */
    public static function headContent(): void
    {
        WikibarHelper::publicHeadContentHelper(My::id() . '_signature');
    }

    /**
     * Get comments syntax mode.
     */
    public static function getWikiMode(): string
    {
        return App::blog()->settings()->get('system')->getBool('markdown_comments', false) ? 'markdown' : 'wiki';
    }

    /**
     * Check requirements.
     */
    public static function hasWikiSyntax(): bool
    {
        return App::plugins()->moduleExists('commentsWikibar') && Wikibar::settings()->get('active');
    }
}
