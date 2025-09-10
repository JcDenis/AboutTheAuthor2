<?php

declare(strict_types=1);

namespace Dotclear\Plugin\AboutTheAuthor2;

use ArrayObject;
use Dotclear\App;
use Dotclear\Database\Statement\{ JoinStatement, SelectStatement };
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
     * @var     array<string, string>   $users  The users stack
     */
    private static array $users = [];

    /**
     * @var     array<string, string>   $signatures     The users signatures stack
     */
    private static array $signatures = [];

    /**
     * @var     array<string, string>   $posts     The users posts counts stack
     */
    private static array $posts = [];

    /**
     * @var     array<string, string>   $comments     The users comments counts stack
     */
    private static array $comments = [];

    /**
     * Get formated user info.
     */
    public static function getAbout(string $user_email): string
    {
        /**
         * @var     ArrayObject<int, string>    $types
         */
        $types = new ArrayObject(['default', 'post']);

        App::behavior()->callBehavior('AboutTheAuthorPageType', $types);

        if (!in_array(App::url()->getType(), iterator_to_array($types))) {
            return '';
        }

        $items = [];

        if (My::settings()->get('show_posts_count') || My::settings()->get('show_comments_count')) {
            $count = [];
            if (My::settings()->get('show_posts_count')) {
                $count[] = self::getPostsCount($user_email);
            }
            if (My::settings()->get('show_comments_count')) {
                $count[] = self::getCommentsCount($user_email);
            }

            $items[] = (new Text('', implode(', ', $count)));
        }

        if (($res = self::getSignature($user_email)) != '') {
            $items[] = (new Text('', $res));
        }
        if ($items === []) {
            return '';
        }

        return (new Div())
            ->class('user-signature')
            ->items([
                (new Text('h5', __('About the author'))),
                ...$items,
            ])
            ->render();
    }

    /**
     * Get user signature from an email.
     */
    public static function getSignature(string $user_email): string
    {
        if (!isset(self::$signatures[$user_email])) {
            $signatures              = self::$signatures;
            $signatures[$user_email] = '';

            if (($user_id = self::getUser($user_email)) != '') {
                $signatures[$user_email] = (string) App::userPreferences()->createFromUser($user_id)->get(My::id())->get('user_signature');
            }

            // a user exists
            if ($signatures[$user_email] != '') {
                # --BEHAVIOR-- publicBeforeCommentTransform -- string
                $buffer = App::behavior()->callBehavior('publicBeforeCommentTransform', $signatures[$user_email]);
                if ($buffer !== '') {
                    $signatures[$user_email] = $buffer;
                } else {
                    if (App::blog()->settings()->system->wiki_comments) {
                        App::filter()->initWikiComment();
                    } else {
                        App::filter()->initWikiSimpleComment();
                    }
                    $signatures[$user_email] = App::filter()->wikiTransform($signatures[$user_email]);
                }
                $signatures[$user_email] = App::filter()->HTMLfilter($signatures[$user_email]);
            }

            self::$signatures = $signatures;
        }

        return self::$signatures[$user_email];
    }

    /**
     * Get number of posts from an email.
     */
    public static function getPostsCount(string $user_email): string
    {
        if (!isset(self::$posts[$user_email])) {
            $posts              = self::$posts;
            $posts[$user_email] = '';

            $sql = new SelectStatement();
            $rs = $sql
                ->column($sql->count($sql->unique('P.post_id')))
                ->from($sql->as(App::db()->con()->prefix() . App::blog()::POST_TABLE_NAME, 'P'), false, true)
                ->join(
                    (new JoinStatement())
                        ->inner()
                        ->from($sql->as(App::db()->con()->prefix() . App::auth()::USER_TABLE_NAME, 'U'))
                        ->on('U.user_id = P.user_id')
                        ->statement()
                )
                ->where('U.user_email = ' . $sql->quote($user_email))
                ->and('P.blog_id = ' . $sql->quote(App::blog()->id()))
                ->select();

            $nb = is_null($rs) ? 0 : (int) $rs->f('0');

            $posts[$user_email] = sprintf(__('one entry', '%s entries', $nb), $nb);

            self::$posts = $posts;
        }

        return self::$posts[$user_email];
    }

    /**
     * Get number of comments from an email.
     */
    public static function getCommentsCount(string $user_email): string
    {
        if (!isset(self::$comments[$user_email])) {
            $comments              = self::$comments;
            $comments[$user_email] = '';

            $sql = new SelectStatement();
            $rs = $sql
                ->column($sql->count($sql->unique('comment_id')))
                ->from($sql->as(App::db()->con()->prefix() . App::blog()::COMMENT_TABLE_NAME, 'C'))
                ->join(
                    (new JoinStatement())
                        ->inner()
                        ->from($sql->as(App::db()->con()->prefix() . App::blog()::POST_TABLE_NAME, 'P'))
                        ->on('C.post_id = P.post_id')
                        ->statement()
                )
                ->where('C.comment_email = ' . $sql->quote($user_email))
                ->and('P.blog_id = ' . $sql->quote(App::blog()->id()))
                ->select();

            $nb = is_null($rs) ? 0 : (int) $rs->f('0');

            $comments[$user_email] = sprintf(__('one comment', '%s comments', $nb), $nb);

            self::$comments = $comments;
        }

        return self::$comments[$user_email];
    }

    /**
     * Get user id from its email.
     */
    private static function getUser(string $user_email): string
    {
        if (!isset(self::$users[$user_email])) {
            $users = self::$users;

            $sql = new SelectStatement();
            $rs = $sql
                ->column('user_id')
                ->from(App::db()->con()->prefix() . App::auth()::USER_TABLE_NAME)
                ->where('user_email = ' . $sql->quote($user_email))
                ->limit(1)
                ->select();

            $users[$user_email] = (is_null($rs) || $rs->isEmpty()) ? '' : $rs->f('user_id');
            self::$users = $users;
        }

        return self::$users[$user_email];
    }
}
