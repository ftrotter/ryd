/*
 * Copyright Fred Trotter and Cautios Patient
 * License AGPL 3.0 or later
 */

CKEDITOR.editorConfig = function( config )
{
    config.toolbar = 'YDAtoolbar';

    config.toolbar_YDAtoolbar =
    [
        ['Bold','Italic','Underline','Strike'],
        ['Cut','Copy','Paste','PasteText','PasteFromWord'],
        ['Undo'],
        ['NumberedList','BulletedList'],
    ];
};

