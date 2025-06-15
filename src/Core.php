<?php

declare(strict_types=1);

namespace Dotclear\Plugin\AboutTheAuthor2;

use ArrayObject;
use Dotclear\App;
use Dotclear\Database\Statement\SelectStatement;
use Dotclear\Helper\Html\Form\{ Div, Text };

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
     * Get user signature.
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

                    if (!is_null($rs)) {
                        while ($rs->fetch()) {
                            if ($user == (string) $rs->f('user_email')) {
                                $signatures[$user] = (string) App::userPreferences()->createFromUser($rs->f('user_id'))->get(My::id())->get('user_signature');
                            }
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
}
