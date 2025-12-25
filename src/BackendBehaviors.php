<?php

declare(strict_types=1);

namespace Dotclear\Plugin\AboutTheAuthor2;

use Dotclear\App;
use Dotclear\Database\{ Cursor, MetaRecord };
use Dotclear\Helper\Html\Form\{ Checkbox, Div, Fieldset, Img, Label, Legend, Para, Textarea };
use Dotclear\Helper\Html\Html;
use Dotclear\Interface\Core\BlogSettingsInterface;

/**
 * @brief       AboutTheAuthor2 module backend behaviors.
 * @ingroup     AboutTheAuthor2
 *
 * @author      Jean-Christian Paul Denis
 * @copyright   AGPL-3.0
 */
class BackendBehaviors
{
    /**
     * Add JS toolbar.
     */
    public static function adminPageHTMLHead(): void
    {
        // Limit to user profil pages
        if (!PluginCommentsWikibar::hasWikiSyntax() || !in_array($_REQUEST['process'] ?? '', ['User', 'UserPreferences'])) {
            return;
        }

        $format = PluginCommentsWikibar::getWikiMode();
        $editor = App::auth()->getOption('editor');

        if (isset($editor[$format])) {
            echo 
            App::backend()->page()->jsJson(My::id(), ['mode' => $format]) .
            App::behavior()->callBehavior('adminPostEditor', $editor[$format], My::id(), ['#' . My::id() . '_signature'], $format) .
            My::jsLoad('backend');
        }
    }

    /**
     * Blog settigns form.
     */
    public static function adminBlogPreferencesFormV2(BlogSettingsInterface $blog_settings): void
    {
        echo (new Fieldset(My::id() . '_params'))
            ->legend(new Legend((new Img(My::icons()[0]))->class('icon-small')->render() . ' ' . My::name()))
            ->items([
                (new Div())
                    ->class('two-cols')
                    ->items([
                        (new Div())
                            ->class('col')
                            ->items([
                                (new Para())
                                    ->items([
                                        (new Checkbox(My::id() . 'post_signature', (bool) !$blog_settings->get(My::id())->get('disable_post_signature')))
                                            ->value(1)
                                            ->label(new Label(__('Enable users signatures to the end of entries'), Label::IL_FT)),
                                    ]),
                                (new Para())
                                    ->items([
                                        (new Checkbox(My::id() . 'comment_signature', (bool) !$blog_settings->get(My::id())->get('disable_comment_signature')))
                                            ->value(1)
                                            ->label(new Label(__('Enable users signatures to the end of comments'), Label::IL_FT)),
                                    ]),
                                (new Para())
                                    ->items([
                                        (new Checkbox(My::id() . 'css', (bool) !$blog_settings->get(My::id())->get('disable_css')))
                                            ->value(1)
                                            ->label(new Label(__('Enable default CSS'), Label::IL_FT)),
                                    ]),
                            ]),
                        (new Div())
                            ->class('col')
                            ->items([
                                (new Para())
                                    ->items([
                                        (new Checkbox(My::id() . 'show_posts_count', (bool) $blog_settings->get(My::id())->get('show_posts_count')))
                                            ->value(1)
                                            ->label(new Label(__("Show author entries count"), Label::IL_FT)),
                                    ]),
                                (new Para())
                                    ->items([
                                        (new Checkbox(My::id() . 'show_comments_count', (bool) $blog_settings->get(My::id())->get('show_comments_count')))
                                            ->value(1)
                                            ->label(new Label(__("Show author comments count"), Label::IL_FT)),
                                    ]),
                                (new Para())
                                    ->items([
                                        (new Checkbox(My::id() . 'disable_displayname', (bool) $blog_settings->get(My::id())->get('disable_displayname')))
                                            ->value(1)
                                            ->label(new Label(__("Disable user to change his displayname"), Label::IL_FT)),
                                    ]),
                            ]),
                    ]),
            ])
            ->render();
    }

    /**
     * Blog settings update.
     */
    public static function adminBeforeBlogSettingsUpdate(BlogSettingsInterface $blog_settings): void
    {
        $blog_settings->get(My::id())->put('disable_post_signature', empty($_POST[My::id() . 'post_signature']), 'boolean');
        $blog_settings->get(My::id())->put('disable_comment_signature', empty($_POST[My::id() . 'comment_signature']), 'boolean');
        $blog_settings->get(My::id())->put('disable_css', empty($_POST[My::id() . 'css']), 'boolean');
        $blog_settings->get(My::id())->put('show_posts_count', !empty($_POST[My::id() . 'show_posts_count']), 'boolean');
        $blog_settings->get(My::id())->put('show_comments_count', !empty($_POST[My::id() . 'show_comments_count']), 'boolean');
    }

    /**
     * Current user preferences form.
     */
    public static function preferencesForm(): void
    {
        if (PluginCommentsWikibar::hasWikiSyntax()) {
            echo (new Fieldset())
                ->id(My::id() . '_prefs')
                ->legend(new Legend((new Img(My::icons()[0]))->class('icon-small')->render() . ' ' . My::name()))
                ->fields([
                    self::commonForm(App::auth()->prefs()->get(My::id())->get('user_signature')),
                ])->render();
        }
    }

    /**
     * A user preferences form.
     */
    public static function userForm(?MetaRecord $rs): void
    {
        if (PluginCommentsWikibar::hasWikiSyntax()) {
            echo self::commonForm(is_null($rs)  || $rs->isEmpty() ? '' : (string) App::userPreferences()->createFromUser($rs->f('user_id'))->get(My::id())->get('user_signature'))->render();
        }
    }

    /**
     * User preferences form.
     */
    public static function commonForm(?string $option): Para
    {
        return (new Para())
            ->items([
                (new Textarea(My::id() . '_signature', Html::escapeHTML($option ?? '')))
                ->class('maximal')
                    ->rows(4)
                    ->label(new Label(__('Signature block:'), Label::OL_TF)),
            ]);
    }

    /**
     * User preferences update.
     */
    public static function updateUser(Cursor $cur, string $user_id = ''): void
    {
        if (PluginCommentsWikibar::hasWikiSyntax()) {
            App::userPreferences()->createFromUser($user_id)->get(My::id())->put(
                'user_signature',
                $_POST[My::id() . '_signature'] ?? '',
                'string',
                'user signature',
                true,
                false
            );
        }
    }
}
