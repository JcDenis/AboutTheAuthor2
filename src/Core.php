<?php

declare(strict_types=1);

namespace Dotclear\Plugin\AboutTheAuthor2;

use ArrayObject;
use Dotclear\App;
use Dotclear\Database\Statement\SelectStatement;
use Dotclear\Helper\Html\Form\{ Div, Text };
use Dotclear\Helper\Html\{ Html, WikiToHtml };
use Dotclear\Plugin\commentsWikibar\My as Wb;

/**
 * @brief       AboutTheAuthor2 module core class.
 * @ingroup     AboutTheAuthor2
 *
 * @author      Jean-Christian Paul Denis
 * @copyright   AGPL-3.0
 */
class Core
{
    public const SIGNATURE_MAX_LENGTH = 250;

    /**
     * @var     array<string, string>   $signatures  The users signatures stack
     */
    private static array $signatures = [];

    /**
     * Get post/coment user signature.
     */
    public static function getSignature(string $user, bool $from_email = false, bool $content_only = false): string
    {
        if (!isset(self::$signatures[$user])) {
            /**
             * @var     ArrayObject<int, string>    $types
             */
            $types = new ArrayObject(['default', 'post']);

            App::behavior()->callBehavior('AboutTheAuthorPageType', $types);

            $signatures           = self::$signatures;
            $signatures[$user] = '';
            if (in_array(App::url()->getType(), iterator_to_array($types))) {
                if ($from_email) {
                    // find comment user signature (we always use auth user email)
                    $sql = new SelectStatement();
                    $rs = $sql
                        ->column(['user_id', 'user_email'])
                        ->from(App::con()->prefix() . App::auth()::USER_TABLE_NAME)
                        ->where('user_email = ' . $sql->quote($user))
                        ->select();

                    while ($rs->fetch()) {
                        if ($user == (string) $rs->f('user_email')) {
                            $signatures[$user] = (string) App::userPreferences()->createFromUser($rs->f('user_id'))->get(My::id())->get('user_signature');
                        }
                    }
                } else {
                    // find post user signature
                    $signatures[$user] = (string) App::userPreferences()->createFromUser($user)->get(My::id())->get('user_signature');
                }

                // a user exists
                if ($signatures[$user] != '') {
                    # --BEHAVIOR-- publicBeforeCommentTransform -- string
                    $buffer = App::behavior()->callBehavior('publicBeforeCommentTransform', $signatures[$user]);
                    if ($buffer !== '') {
                        $signatures[$user] = $buffer;
                    } else {
                        if (App::blog()->settings()->system->wiki_comments) {
                            App::filter()->initWikiComment();
                        } else {
                            App::filter()->initWikiSimpleComment();
                        }
                        $signatures[$user] = App::filter()->wikiTransform($signatures[$user]);
                    }
                    $signatures[$user] = App::filter()->HTMLfilter($signatures[$user]);
                }
            }

            self::$signatures = $signatures;
        }

        if (($signature = self::$signatures[$user]) == '') {
            return '';
        }

        return $content_only ? $signature : (new Div())
            ->class('user-signature')
            ->items([
                (new Text('h5', __('About the author'))),
                (new Text('', $signature)),
            ])
            ->render();
    }

    /**
     * Load JS and CSS and add wiki bar.
     */
    public static function wikibarHead(): void
    {
        // wiki, taken from plugin commentsWikibar
        if (!App::plugins()->moduleExists('commentsWikibar')
            || !Wb::settings()->get('active')
            || App::url()->getType() !== 'FrontendSession'
        ) {
            return;
        }

        $settings = Wb::settings();
        // CSS
        if ($settings->add_css) {
            $custom_css = trim((string) $settings->custom_css);
            if ($custom_css !== '') {
                if (str_starts_with($custom_css, '/') || preg_match('!^https?://.+!', $custom_css)) {
                    // Absolute URL
                    $css_file = $custom_css;
                } else {
                    // Relative URL
                    $css_file = App::blog()->settings()->system->themes_url . '/' .
                    App::blog()->settings()->system->theme . '/' .
                        $custom_css;
                }

                $css = App::plugins()->cssLoad($css_file);
            } else {
                $css = Wb::cssLoad('wikibar.css');
            }

            echo $css;
        }

        // JS
        if ($settings->add_jslib) {
            $custom_jslib = trim((string) $settings->custom_jslib);
            if ($custom_jslib !== '') {
                if (str_starts_with($custom_jslib, '/') || preg_match('!^https?://.+!', $custom_jslib)) {
                    $js_file = $custom_jslib;
                } else {
                    $js_file = App::blog()->settings()->system->themes_url . '/' .
                    App::blog()->settings()->system->theme . '/' .
                        $custom_jslib;
                }

                $js = App::plugins()->jsLoad($js_file);
            } else {
                $js = Wb::jsLoad('wikibar.js');
            }

            echo $js;
        }

        if ($settings->add_jsglue) {
            $mode = 'wiki';
            // Formatting Markdown activated
            if (App::blog()->settings()->system->markdown_comments) {
                $mode = 'markdown';
            }

            echo
            Html::jsJson('commentswikibar', [
                'base_url'   => App::blog()->host(),
                'id'         => My::id() . '_signature',
                'mode'       => $mode,
                'legend_msg' => __('You can use the following shortcuts to format your text.'),
                'label'      => __('Text formatting'),
                'elements'   => [
                    'strong' => ['title' => __('Strong emphasis')],
                    'em'     => ['title' => __('Emphasis')],
                    'ins'    => ['title' => __('Inserted')],
                    'del'    => ['title' => __('Deleted')],
                    'quote'  => ['title' => __('Inline quote')],
                    'code'   => ['title' => __('Code')],
                    'br'     => ['title' => __('Line break')],
                    'ul'     => ['title' => __('Unordered list')],
                    'ol'     => ['title' => __('Ordered list')],
                    'pre'    => ['title' => __('Preformatted')],
                    'bquote' => ['title' => __('Block quote')],
                    'link'   => [
                        'title'           => __('Link'),
                        'href_prompt'     => __('URL?'),
                        'hreflang_prompt' => __('Language?'),
                        'title_prompt'    => __('Title?'),
                    ],
                ],
                'options' => [
                    'no_format' => $settings->no_format,
                    'no_br'     => $settings->no_br,
                    'no_list'   => $settings->no_list,
                    'no_pre'    => $settings->no_pre,
                    'no_quote'  => $settings->no_quote,
                    'no_url'    => $settings->no_url,
                ],
            ]) .
            Wb::jsLoad('bootstrap.js');
        }
    }

    /**
     * Init wiki syntax for post form.
     */
    public static function wikibarInit(WikiToHtml $wiki): string
    {
        if (!App::plugins()->moduleExists('commentsWikibar')
            || !Wb::settings()->get('active')
            || App::url()->getType() != My::id()
        ) {
            return '';
        }

        $settings = Wb::settings();
        if ($settings->no_format) {
            $wiki->setOpt('active_strong', 0);
            $wiki->setOpt('active_em', 0);
            $wiki->setOpt('active_ins', 0);
            $wiki->setOpt('active_del', 0);
            $wiki->setOpt('active_q', 0);
            $wiki->setOpt('active_code', 0);
        }

        if ($settings->no_br) {
            $wiki->setOpt('active_br', 0);
        }

        if ($settings->no_list) {
            $wiki->setOpt('active_lists', 0);
        }

        if ($settings->no_pre) {
            $wiki->setOpt('active_pre', 0);
        }

        if ($settings->no_quote) {
            $wiki->setOpt('active_quote', 0);
        } elseif (App::blog()->settings()->system->wiki_comments) {
            $wiki->setOpt('active_quote', 1);
        }

        if ($settings->no_url) {
            $wiki->setOpt('active_urls', 0);
        }

        return '';
    }
}
