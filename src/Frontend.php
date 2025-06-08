<?php

declare(strict_types=1);

namespace Dotclear\Plugin\AboutTheAuthor2;

use Dotclear\App;
use Dotclear\Core\Process;

/**
 * @brief       AboutTheAuthor2 module frontend process.
 * @ingroup     AboutTheAuthor2
 *
 * @author      Jean-Christian Paul Denis
 * @copyright   AGPL-3.0
 */
class Frontend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::FRONTEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        App::behavior()->addBehaviors([
            'initWidgets'                       => Widgets::initWidgets(...),
            'publicHeadContent'                 => FrontendBehaviors::publicHeadContent(...),
            'publicEntryAfterContent'           => FrontendBehaviors::publicEntryAfterContent(...),
            'publicCommentAfterContent'         => FrontendBehaviors::publicCommentAfterContent(...),
            'FrontendSessionAction'             => FrontendBehaviors::FrontendSessionAction(...),
            'FrontendSessionProfil'             => FrontendBehaviors::FrontendSessionProfil(...),
            'coreInitWikiPost'                  => Core::wikibarInit(...),
        ]);

        return true;
    }
}
