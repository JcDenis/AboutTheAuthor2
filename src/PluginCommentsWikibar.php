<?php

declare(strict_types=1);

namespace Dotclear\Plugin\AboutTheAuthor2;

use Dotclear\App;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Html\WikiToHtml;
use Dotclear\Plugin\commentsWikibar\My as Wikibar;

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
        $settings = Wikibar::settings();
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
                $css = Wikibar::cssLoad('wikibar.css');
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
                $js = Wikibar::jsLoad('wikibar.js');
            }

            echo $js;
        }

        if ($settings->add_jsglue) {
            echo
            Html::jsJson('commentswikibar', [
                'base_url'   => App::blog()->host(),
                'id'         => My::id() . '_signature',
                'mode'       => self::getWikiMode(),
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
            Wikibar::jsLoad('bootstrap.js');
        }
    }

    /**
     * Get comments syntax mode.
     */
    public static function getWikiMode(): string
    {
        return App::blog()->settings()->get('system')->get('markdown_comments') ? 'markdown' : 'wiki';
    }

    /**
     * Check requirements.
     */
    public static function hasWikiSyntax(): bool
    {
        return App::plugins()->moduleExists('commentsWikibar') && Wikibar::settings()->get('active');
    }
}
