<?php

declare(strict_types=1);

namespace Dotclear\Plugin\AboutTheAuthor2;

use Dotclear\App;
use Dotclear\Helper\Html\Html;
use Dotclear\Plugin\widgets\WidgetsStack;
use Dotclear\Plugin\widgets\WidgetsElement;

/**
 * @brief       AboutTheAuthor2 widgets class.
 * @ingroup     AboutTheAuthor2
 *
 * @author      Jean-Christian Paul Denis
 * @copyright   AGPL-3.0
 */
class Widgets
{
    /**
     * Widget initialisation.
     *
     * @param  WidgetsStack $w WidgetsStack instance
     */
    public static function initWidgets(WidgetsStack $w): void
    {
        $w
            ->create(
                basename(__DIR__),
                My::name(),
                self::parseWidget(...),
                null,
                __('Add a widget of user signature to an entry')
            )
            ->addTitle(__('About the author'))
            ->addContentOnly()
            ->addClass()
            ->addOffline();
    }

    /**
     * Parse widget.
     *
     * @param  WidgetsElement $w WidgetsElement instance
     */
    public static function parseWidget(WidgetsElement $w): string
    {
        if ($w->get('offline')
            || !App::frontend()->context()->exists('posts')
            || !App::frontend()->context()->posts->f('post_id')
        ) {
            return '';
        }
 
        $signature = Core::getSignature((string) App::frontend()->context()->posts->f('user_id'), false, true);

        return $signature === '' ? '' : $w->renderDiv(
            (bool) $w->get('content_only'),
            My::id() . ' ' . $w->get('class'),
            '',
            ($w->get('title') ? $w->renderTitle(Html::escapeHTML($w->get('title'))) : '') . $signature
        );
    }
}
