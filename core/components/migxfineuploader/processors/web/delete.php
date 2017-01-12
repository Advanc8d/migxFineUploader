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
$uuid = htmlspecialchars(trim($modx->getOption('qquuid', $scriptProperties, false)));
$_SESSION['migxfineuploader'][$uid . 'delete'][$uuid] = $uuid;

$output = '';


if (isset($_SESSION['migxfineuploader'][$uid . 'config'])) {
    $directory = $_SESSION['migxfineuploader'][$uid . 'config']['cachePath'];
        
    $modx->migxfineuploader = new MigxFineUploader($modx, $_SESSION['migxfineuploader'][$uid . 'config']);
    $modx->migxfineuploader->initialize($_SESSION['migxfineuploader'][$uid . 'config']);
    $uploader = new UploadHandler();
    //unset($_SESSION['migxfineuploader']);

    if (is_array($_SESSION['migxfineuploader'][$uid])) {
        foreach ($_SESSION['migxfineuploader'][$uid] as $key=>$fileInfo) {
            if ($fileInfo['uuid'] == $uuid){
                unset($_SESSION['migxfineuploader'][$uid][$key]);
                $result = $uploader->handleDelete($directory);
                return htmlspecialchars(json_encode($result), ENT_NOQUOTES);
            }
        }
    }
    
}
return $output;