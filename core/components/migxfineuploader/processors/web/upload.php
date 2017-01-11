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

    $result = array();

    // Upload the image(s)
    //$uploader = new qqFileUploader($modx->migxfineuploader->config['allowedExtensions'], $modx->migxfineuploader->config['sizeLimit']);
    $uploader = new UploadHandler();
    // Specify the list of valid extensions, ex. array("jpeg", "xml", "bmp")
    $uploader->allowedExtensions = $modx->migxfineuploader->config['allowedExtensions']; // all files types allowed by default

    // Specify max file size in bytes.
    $uploader->sizeLimit = $modx->migxfineuploader->config['sizeLimit'];

    // To pass data through iframe you will need to encode all html tags
    $path = $modx->migxfineuploader->config['cachePath'];
    $result = $uploader->handleUpload($path);

    // File successful uploaded
    if ($result['success']) {
        $pathinfo = pathinfo($uploader->getUploadName());
        $filename = $pathinfo['filename'];
        //$filename = md5(uniqid());
        $extension = @$pathinfo['extension']; // hide notices if extension is empty
        $uuid = $_REQUEST['qquuid'];

        $fileInfo = array();

        // Check if count of uploaded files are below max file count
        if (count($_SESSION['migxfineuploader'][$uid]) < $modx->migxfineuploader->config['maxFiles']) {
            $fileInfo['originalBaseUrl'] = $modx->migxfineuploader->config['cachePath'] . $uuid . DIRECTORY_SEPARATOR;
            $fileInfo['path'] = $path . $uuid . DIRECTORY_SEPARATOR;
            $fileInfo['base_url'] = $modx->migxfineuploader->config['cacheUrl'] . $uuid . DIRECTORY_SEPARATOR;

            // Create unique filename and set permissions
            $fileInfo['uniqueName'] = md5($filename . time()) . '.' . $extension;
            $filePerm = (int)$modx->migxfineuploader->config['newFilePermissions'];

            //@rename($fileInfo['path'] . $filename . '.' . $extension, $fileInfo['path'] . $fileInfo['uniqueName']);
            //@chmod($fileInfo['path'] . $fileInfo['uniqueName'], octdec($filePerm));

            @chmod($fileInfo['path'] . $filename . '.' . $extension, octdec($filePerm));


            $fileInfo['originalName'] = $filename . '.' . $extension;
            $fileInfo['uuid'] = $uuid;
            $fileInfo['size'] = filesize($fileInfo['path'] . $filename . '.' . $extension);

            // Create thumbnail
            $fileInfo['thumbName'] = $modx->migxfineuploader->generateThumbnail($fileInfo);
            if ($fileInfo['thumbName']) {
                // Fill session
                $hash = hash('md5', serialize($fileInfo));
                $_SESSION['migxfineuploader'][$uid][$hash] = $fileInfo;

                //print_r($_SESSION['migxfineuploader']);

                // Prepare returned values (filename, originalName & fileid)
                $result['thumbnailUrl'] = $fileInfo['base_url'] . $fileInfo['thumbName'];
                $result['originalName'] = $fileInfo['originalName'];
                $result['fileid'] = $hash;
            } else {
                unset($result['success']);
                $result['error'] = $modx->lexicon('migxfineuploader.thumbnailGenerationProblem');
                @unlink($fileInfo['path'] . $fileInfo['uniqueName']);
            }
        } else {
            unset($result['success']);
            // Error message
            $result['error'] = $modx->lexicon('migxfineuploader.maxFiles', array('maxFiles' => $modx->migxfineuploader->config['maxFiles']));
            // Delete uploaded file
            @unlink($fileInfo['path'] . $filename . '.' . $extension);
        }

    }
    $output = htmlspecialchars(json_encode($result), ENT_NOQUOTES);
}
return $output;
