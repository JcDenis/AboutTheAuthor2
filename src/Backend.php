<?php

declare(strict_types=1);

namespace Dotclear\Plugin\AboutTheAuthor2;

use Dotclear\App;
use Dotclear\Helper\Process\TraitProcess;

/**
 * @brief       AboutTheAuthor2 module backend process.
 * @ingroup     AboutTheAuthor2
 *
 * @author      Jean-Christian Paul Denis
 * @copyright   AGPL-3.0
 */
class Backend
{
    use TraitProcess;

    public static function init(): bool
    {
        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        App::behavior()->addBehaviors([
            'initWidgets'                   => Widgets::initWidgets(...),
            'adminPageHTMLHead'             => BackendBehaviors::adminPageHTMLHead(...),
            'adminBlogPreferencesFormV2'    => BackendBehaviors::adminBlogPreferencesFormV2(...),
            'adminBeforeBlogSettingsUpdate' => BackendBehaviors::adminBeforeBlogSettingsUpdate(...),
            'adminPreferencesFormV2'        => BackendBehaviors::preferencesForm(...),
            'adminUserForm'                 => BackendBehaviors::userForm(...),
            'adminBeforeUserCreate'         => BackendBehaviors::updateUser(...),
            'adminBeforeUserUpdate'         => BackendBehaviors::updateUser(...),
            'adminBeforeUserOptionsUpdate'  => BackendBehaviors::updateUser(...),
        ]);

        return true;
    }
}
