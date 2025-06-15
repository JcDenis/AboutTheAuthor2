/*global $, dotclear, jsToolBar */
'use strict';

$(() => {
  dotclear.ATA = dotclear.getData('AboutTheAuthor2');

  if (typeof jsToolBar === 'function') {
    $('#AboutTheAuthor2_signature').each(function () {
      const tbATA = new jsToolBar(this);
      tbATA.context = 'user_signature';
      tbATA.switchMode(dotclear.ATA.mode);
    });
  }
});