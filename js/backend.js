/*global $, dotclear, jsToolBar */
'use strict';

$(() => {
  if (typeof jsToolBar === 'function') {
    $('#AboutTheAuthor2_signature').each(function () {
      const tbATA = new jsToolBar(this);
      tbATA.context = 'user_signature';
      tbATA.switchMode('wiki');
    });
  }
});