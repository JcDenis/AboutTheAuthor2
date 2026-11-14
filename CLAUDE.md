# AboutTheAuthor2

Notes for AI assistants working on the Dotclear plugin AboutTheAuthor2.

**Read first** the shared conventions of the author's Dotclear modules:
https://raw.githubusercontent.com/JcDenis/gh2da/master/CONVENTIONS.md
(if a web tool refuses the address, use `curl -s` on it from the shell).
The rules below take precedence for this module.

## Module

Displays information about the author of entries or comments.

No admin page. Public side: behaviors.

Required Dotclear and PHP versions: see `_define.php`.

## Structure

* `src/Backend.php`: admin side: menu entry, favorites, behaviors registration.
* `src/BackendBehaviors.php`: admin behaviors.
* `src/Core.php`: module core class.
* `src/Frontend.php`: public side: behaviors, template tags and URL handlers registration.
* `src/FrontendBehaviors.php`: public behaviors.
* `src/My.php`: module helpers (`My::id()`, settings, permissions).
* `src/PluginCommentsWikibar.php`: module frontend behaviors.
* `src/Widgets.php`: widgets definition and rendering.
* `js/`: `backend.js`.
* Behaviors used: `initWidgets`, `adminPageHTMLHead`, `adminBlogPreferencesFormV2`, `adminBeforeBlogSettingsUpdate`, `adminPreferencesFormV2`, `adminUserForm`, `adminAfterUserCreate`, `adminAfterUserUpdate`, `adminBeforeUserOptionsUpdate`, `initCommentsWikibar`, `publicHeadContent`, `publicEntryAfterContent`, `publicCommentAfterContent`.

## Pitfalls

* None recorded yet: add here the traps met while working on the module.

## Testing

* CI browses the common admin and public pages; extra pages can be listed in `.github/dotclear-test.txt`.
