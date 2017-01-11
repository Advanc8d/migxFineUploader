<?php
if ($modx->event->name == 'OnDocFormSave'){
$migxfineuploaderCorePath = $modx->getOption('migxfineuploader.core_path', null, $modx->getOption('core_path') . 'components/migxfineuploader/');
$migxfineuploaderAssetsPath = $modx->getOption('migxfineuploader.assets_path', null, $modx->getOption('assets_path') . 'components/migxfineuploader/');
$migxfineuploaderAssetsUrl = $modx->getOption('migxfineuploader.assets_url', null, $modx->getOption('assets_url') . 'components/migxfineuploader/');
$debug = $modx->getOption('debug', $scriptProperties, $modx->getOption('migxfineuploader.debug', null, false), true);

if (!$modx->loadClass('MigxFineUploader', $migxfineuploaderCorePath . 'model/migxfineuploader/', true, true)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not load MigxFineUploader class.', '', 'MigxFineUploader');
    if ($debug) {
        return 'Could not load MigxFineUploader class.';
    } else {
        return '';
    }
}

$scriptProperties['migxfineuploader.core_path'] = $migxfineuploaderCorePath;
$scriptProperties['migxfineuploader.assets_path'] = $migxfineuploaderAssetsPath;
$scriptProperties['migxfineuploader.assets_url'] = $migxfineuploaderAssetsUrl;
$migxFineUploader = new MigxFineUploader($modx);
$migxFineUploader->initialize();

$migxFineUploader->OnDocFormSave($resource);

}

return;