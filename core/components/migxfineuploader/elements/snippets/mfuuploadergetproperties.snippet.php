<?php
/*
 * don't modify this snippet, but create a new customized snippet and change the MIGX-config 'Hook - Snippets' under the tab MIGXdb - Settings
 * this snippet will get overridden on upgrades!
 */

$prop = &$scriptProperties['scriptProperties'];

$prop['addJquery'] = '1';//load jQuery into the DOM
$prop['addUikitCss'] = '1';
$prop['addUikit'] = '1';//load uikit, used for drag/drop - sortable of uploaded items and more
$prop['debug'] = '0';
$prop['maxFiles'] = '10';
$prop['allowedExtensions'] = 'jpg,jpeg,png,gif';
$prop['tplUiTemplate'] = 'mfu.migx.template';
$prop['tplInitUploader'] = 'mfu.inituploader';

return '';