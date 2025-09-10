<?php

declare(strict_types=1);

namespace Dotclear\Plugin\AboutTheAuthor2;

use Dotclear\App;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Html\Form\Li;
use Dotclear\Helper\Html\Form\Ul;
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
            ->setting(
                'show_post',
                __('Show author entries count'),
                0,
                'check'
            )
            ->setting(
                'show_comment',
                __('Show author comments count'),
                0,
                'check'
            )
            ->setting(
                'show_signature',
                __('Show user signature'),
                1,
                'check'
            )
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
            || App::frontend()->context()->posts->isEmpty()
        ) {
            return '';
        }

        $user_email = (string) App::frontend()->context()->posts->f('user_email');

        $count = '';
        if ($w->get('show_post') || $w->get('show_comment')) {
            $li = [];
            if ($w->get('show_post')) {
                $li[] = (new Li())->text(Core::getPostsCount($user_email));
            }
            if ($w->get('show_comment')) {
                $li[] = (new Li())->text(Core::getCommentsCount($user_email));
            }
            $count = (new Ul())->items($li)->render();
        }
 
        $signature = Core::getSignature($user_email);

        return $signature === '' ? '' : $w->renderDiv(
            (bool) $w->get('content_only'),
            My::id() . ' ' . $w->get('class'),
            '',
            ($w->get('title') ? $w->renderTitle(Html::escapeHTML($w->get('title'))) : '') . $count . $signature
        );
    }
}
