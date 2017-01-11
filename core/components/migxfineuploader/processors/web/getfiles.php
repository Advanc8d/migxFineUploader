<?php

/**
 * MigxFineUploader Processor
 *
 * @package migxfineuploader
 * @subpackage processor
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$uid = htmlspecialchars(trim($modx->getOption('uid', $scriptProperties, false)));
$output = '';


if (isset($_SESSION['migxfineuploader'][$uid . 'config'])) {

    $modx->migxfineuploader = new MigxFineUploader($modx, $_SESSION['migxfineuploader'][$uid . 'config']);
    $modx->migxfineuploader->initialize($_SESSION['migxfineuploader'][$uid . 'config']);

    //unset($_SESSION['migxfineuploader']);

    $result = array();

    if (is_array($_SESSION['migxfineuploader'][$uid])) {
        foreach ($_SESSION['migxfineuploader'][$uid] as $fileInfo) {
            $file = array();
            $file['name'] = $fileInfo['originalName'];
            $file['uuid'] = $fileInfo['uuid'];
            $file['size'] = $fileInfo['size'];
            $file['thumbnailUrl'] = $fileInfo['base_url'] . $fileInfo['thumbName'];
            $result[] = $file;
        }
    }


    $output = htmlspecialchars(json_encode($result), ENT_NOQUOTES);
}
return $output;
