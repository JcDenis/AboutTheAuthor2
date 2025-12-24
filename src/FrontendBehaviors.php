<?php

declare(strict_types=1);

namespace Dotclear\Plugin\AboutTheAuthor2;

use Dotclear\App;
use Dotclear\Helper\Html\Form\Input;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Note;
use Dotclear\Helper\Html\Form\Textarea;
use Dotclear\Helper\Html\Html;
use Dotclear\Plugin\FrontendSession\FrontendSessionProfil;
use Throwable;

/**
 * @brief       AboutTheAuthor2 module frontend behaviors.
 * @ingroup     AboutTheAuthor2
 *
 * @author      Jean-Christian Paul Denis
 * @copyright   AGPL-3.0
 */
class FrontendBehaviors
{
    /**
     * Load JS and CSS and add wiki bar to session signature form.
     */
    public static function publicHeadContent(): void
    {
        // style
        $tplset = App::themes()->moduleInfo(App::blog()->settings()->get('system')->get('theme'), 'tplset');
        if (!My::settings()->get('disable_css') && $tplset == 'dotty') {
            echo My::cssLoad('frontend-' . $tplset);
        }

        // JS for wikibar
        if (PluginCommentsWikibar::hasWikiSyntax() &&  App::url()->getType() === 'FrontendSession') {
            PluginCommentsWikibar::headContent();
        }
    }

    /**
     * Add users signatures to the end of posts.
     */
    public static function publicEntryAfterContent(): void
    {
        if (App::frontend()->context()->exists('posts') && PluginCommentsWikibar::hasWikiSyntax() && !My::settings()->get('disable_post_signature')) {
            echo Core::getAbout(App::frontend()->context()->posts->f('user_email'));
        }
    }

    /**
     * Add users signatures to the end of comments.
     */
    public static function publicCommentAfterContent(): void
    {
        if (App::frontend()->context()->exists('posts') 
            && App::frontend()->context()->exists('comments') 
            && !App::frontend()->context()->comments->f('comment_trackback')
            && PluginCommentsWikibar::hasWikiSyntax() 
            && !My::settings()->get('disable_comment_signature')
        ) {
            echo Core::getAbout(App::frontend()->context()->comments->f('comment_email'));
        }
    }

    /**
     * Save user profil from session.
     */
    public static function FrontendSessionAction(string $action): void
    {
        if ($action == My::id() && App::auth()->userID() != '') {
            $user_url       = $_POST[My::id() . '_url'];
            $user_signature = $_POST[My::id() . '_signature'];

            if (!preg_match('|^https?://|', (string) $user_url)) {
                $user_url = 'http://' . $user_url;
            }
            $user_url = (string) filter_var($user_url, FILTER_VALIDATE_URL);
            $user_id  = (string) App::auth()->userID();

            try {
                // change user url
                $cur = App::auth()->openUserCursor();
                $cur->setField('user_url', $user_url);
                App::auth()->sudo(App::users()->updUser(...), $user_id, $cur);

                // change user signature
                if (PluginCommentsWikibar::hasWikiSyntax()) {
                    App::auth()->prefs()->get(My::id())->put(
                        'user_signature',
                        substr($user_signature, 0, Core::SIGNATURE_MAX_LENGTH),
                        'string',
                        'user signature',
                        true,
                        false
                    );
                }

                // reload user
                App::auth()->checkUser($user_id);

                App::frontend()->context()->frontend_session->success = __('Profile successfully updated.');
            } catch (Throwable $e) {
                App::frontend()->context()->form_error = $e->getMessage();
            }
        }
    }

    /**
     * Add user profil form to session.
     */
    public static function FrontendSessionProfil(FrontendSessionProfil $profil): void
    {
        if (App::auth()->userID() != '') {
            $fields = [
                // user_site
                $profil->getInputfield([
                    (new Input(My::id() . '_url'))
                        ->size(30)
                        ->maxlength(Core::SIGNATURE_MAX_LENGTH)
                        ->value(Html::escapeHTML(App::auth()->getInfo('user_url')))
                        ->label(new Label(__('Your site URL:'), Label::OL_TF)),
                ])
            ];

            if (PluginCommentsWikibar::hasWikiSyntax()) {
                $fields[] = $profil->getInputfield([
                    (new Textarea(My::id() . '_signature', Html::escapeHTML((string) App::auth()->prefs()->get(My::id())->get('user_signature'))))
                        ->rows(4)
                        ->label((new Label(__('Signature block:'), Label::OL_TF))),
                    (new Note())
                        ->class('note')
                        ->text(sprintf(__('Signature max length is %s chars long and accept %s syntax.'), Core::SIGNATURE_MAX_LENGTH, PluginCommentsWikibar::getWikiMode())),
                ]);
            }

            $profil->addAction(My::id(), __('Profile'), [
                ...$fields,
                $profil->getControlset(My::id(), __('Save')),
            ]);
        }
    }
}
