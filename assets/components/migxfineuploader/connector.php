<?php
/**
 * MigxFineUploader Connector
 *
 * @package migxfineuploader
 * @subpackage connector
 *
 * @var modx $modx
 */
/* Allow anonymous users */
define('MODX_REQP', false);

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

$corePath = $modx->getOption('migxfineuploader.core_path', null, $modx->getOption('core_path') . 'components/migxfineuploader/');

/** @var MigxFineUploader $migxfineuploader */
$migxfineuploader = $modx->getService('migxfineuploader', 'MigxFineUploader', $corePath . 'model/migxfineuploader/', array(
    'core_path' => $corePath
));

if (in_array($_REQUEST['action'],array('web/upload','web/getfiles','web/delete'))) {
    $_SERVER['HTTP_MODAUTH'] = $modx->user->getUserToken($modx->context->get('key'));
}

// Handle request
$path = $modx->getOption('processorsPath', $migxfineuploader->config, $corePath . 'processors/');
$modx->request->handleRequest(array(
    'processors_path' => $path,
    'location' => ''
));