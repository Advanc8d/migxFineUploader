<?php
/**
 * migxFineUploader
 *
 * @package migxfineuploader
 * @subpackage snippet
 *
 * @var modx $modx
 * @var array $scriptProperties
 */

$tvname = $modx->getOption('tvname', $scriptProperties, '');
$migx = $modx->getService('migx', 'Migx', $modx->getOption('migx.core_path', null, $modx->getOption('core_path') . 'components/migx/') . 'model/migx/');
if ($migx instanceof Migx) {
    //$migx->working_context = $resource ? $resource->get('context_key') : 'web';
    if ($tv = $modx->getObject('modTemplateVar', array('name' => $tvname))) {

        /*
        *   get inputProperties
        */

        $properties = $tv->get('input_properties');
        $properties = isset($properties['formtabs']) ? $properties : $tv->getProperties();

        $migx->config['configs'] = $modx->getOption('configs', $properties, '');
        if (!empty($migx->config['configs'])) {
            $migx->loadConfigs();

            $config = $migx->customconfigs;

            $hooksnippets = json_decode($modx->getOption('hooksnippets', $config, ''),true);
            if (is_array($hooksnippets)) {
                $hooksnippet_getproperties = $modx->getOption('mfugetproperties', $hooksnippets, '');
                $snippetProperties = array();
                $snippetProperties['scriptProperties'] = &$scriptProperties;
                $modx->runSnippet($hooksnippet_getproperties,$snippetProperties);
            }

        }
    }
}


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
$resourceId = isset($_POST['np_doc_id']) ? $_POST['np_doc_id'] : $modx->resource->get('id');
$scriptProperties['uid'] = $modx->getOption('uid', $scriptProperties, md5($modx->getOption('site_url') . '-' . $resourceId . '-' . $tvname));

$migxFineUploader = new MigxFineUploader($modx, $scriptProperties);

if (!$migxFineUploader->initialize($scriptProperties, true)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not initialize MigxFineUploader class.', '', 'MigxFineUploader');
    if ($debug) {
        return 'Could not load initialize MigxFineUploader class.';
    } else {
        return '';
    }
}
return $migxFineUploader->output() . $migxFineUploader->debugOutput();