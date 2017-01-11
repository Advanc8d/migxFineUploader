<?php

/**
 * MigxFineUploader
 * 
 * (Fork of AjaxUpload)
 * Copyright 2013-2016 by Thomas Jakobi <thomas.jakobi@partout.info> 
 * Copyright 2017 by Bruno Perner <b.perner@gmx.de>
 *
 * @package migxfineuploader
 * @subpackage classfile
 */
class MigxFineUploader {
    /**
     * A reference to the modX instance
     * @var modX $modx
     */
    public $modx;

    /**
     * The namespace
     * @var string $namespace
     */
    public $namespace = 'migxfineuploader';

    /**
     * The version
     * @var string $version
     */
    public $version = '0.1.0';

    /**
     * A configuration array
     * @var array $config
     */
    public $config = array();

    /**
     * An array of debug informations
     * @var array $debug
     */
    public $debug;

    /**
     * MigxFineUploader constructor
     *
     * @param modX $modx A reference to the modX instance.
     * @param array $config An array of configuration options. Optional.
     */
    function __construct(modX & $modx, array $config = array()) {
        $this->modx = &$modx;

        $corePath = $this->getOption('core_path', $config, $this->modx->getOption('core_path') . 'components/' . $this->namespace . '/');
        $assetsPath = $this->getOption('assets_path', $config, $this->modx->getOption('assets_path') . 'components/' . $this->namespace . '/');
        $assetsUrl = $this->getOption('assets_url', $config, $this->modx->getOption('assets_url') . 'components/' . $this->namespace . '/');

        // Load some default paths for easier management
        $this->config = array(
            'namespace' => $this->namespace,
            'version' => $this->version,
            'assetsPath' => $assetsPath,
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
            'sourceUrl' => $assetsUrl . 'source/',
            'imagesUrl' => $assetsUrl . 'images/',
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'chunksPath' => $corePath . 'elements/chunks/',
            'pagesPath' => $corePath . 'elements/pages/',
            'snippetsPath' => $corePath . 'elements/snippets/',
            'pluginsPath' => $corePath . 'elements/plugins/',
            'controllersPath' => $corePath . 'controllers/',
            'processorsPath' => $corePath . 'processors/',
            'templatesPath' => $corePath . 'templates/',
            'connectorUrl' => $assetsUrl . 'connector.php',
            'cachePath' => $assetsPath . 'cache/',
            'cacheUrl' => $assetsUrl . 'cache/');

        // Set parameters
        $resourceId = ($this->modx->resource) ? $this->modx->resource->get('id') : 0;
        $this->config = array_merge($this->config, array(
            'debug' => false,
            'uid' => $this->getOption('uid', $config, md5($this->modx->getOption('site_url') . '-' . $resourceId)),
            'uploadAction' => $assetsUrl . 'connector.php',
            'newFilePermissions' => '0664',
            'maxConnections' => 3,
            'cacheExpires' => intval($this->getOption('cacheExpires', $config, 4)),
            'allowOverwrite' => (bool)$this->getOption('allowOverwrite', $config, false)));
        $this->config = array_merge($this->config, $config);
        $this->debug = array();
    }

    /**
     * Get a local configuration option or a namespaced system setting by key.
     *
     * @param string $key The option key to search for.
     * @param array $options An array of options that override local options.
     * @param mixed $default The default value returned if the option is not found locally or as a
     * namespaced system setting; by default this value is null.
     * @return mixed The option value or the default value specified.
     */
    public function getOption($key, $options = array(), $default = null) {
        $option = $default;
        if (!empty($key) && is_string($key)) {
            if ($options != null && array_key_exists($key, $options)) {
                $option = $options[$key];
            } elseif (array_key_exists($key, $this->config)) {
                $option = $this->config[$key];
            } elseif (array_key_exists("{$this->namespace}.{$key}", $this->modx->config)) {
                $option = $this->modx->getOption("{$this->namespace}.{$key}");
            }
        }
        return $option;
    }

    /**
     * Load all class files and init defaults.
     *
     * @param array $properties properties to override the default config (if set)
     * @access public
     * @return boolean success state of initialization
     */
    public function initialize($properties = array()) {
        /*
        if (!$this->modx->getService('smarty', 'smarty.modSmarty')) {
        $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not load modSmarty service.', '', 'MigxFineUploader');
        $this->debug[] = 'Could not load modSmarty service.';
        return false;
        }
        */

        if (!$this->modx->loadClass('modPhpThumb', $this->modx->getOption('core_path') . 'model/phpthumb/', true, true)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not load modPhpThumb class.', '', 'MigxFineUploader');
            $this->debug[] = 'Could not load modPhpThumb class.';
            return false;
        }
        if (!class_exists('UploadHandler')) {
            include_once $this->config['modelPath'] . 'fineuploader/handler.php';
        }
        $language = empty($this->config['language']) ? '' : $this->config['language'] . ':';
        $this->modx->lexicon->load($language . 'migxfineuploader:default');

        if (!isset($_SESSION['migxfineuploader'][$this->config['uid']])) {
            $_SESSION['migxfineuploader'][$this->config['uid']] = array();
        }
        if (!isset($_SESSION['migxfineuploader'][$this->config['uid'] . 'delete'])) {
            $_SESSION['migxfineuploader'][$this->config['uid'] . 'delete'] = array();
        }
        if (is_array($properties)) {
            $allowedExtensions = $this->modx->getOption('allowedExtensions', $properties, 'jpg,jpeg,png,gif');
            $allowedExtensions = (!is_array($allowedExtensions)) ? explode(',', $allowedExtensions) : $allowedExtensions;
            $config = array(
                'allowedExtensions' => $allowedExtensions,
                'allowedExtensionsString' => (!empty($allowedExtensions)) ? "'" . implode("','", $allowedExtensions) . "'" : '',
                'sizeLimit' => $this->modx->getOption('sizeLimit', $properties, $this->modx->getOption('maxFilesizeMb', $properties, 8) * 1024 * 1024),
                'maxFiles' => (integer)$this->modx->getOption('maxFiles', $properties, 3),
                'resourceBasePath' => $this->modx->getOption('resourceBasePath', $properties, 'assets/resourcefiles/'),
                'thumbX' => (integer)$this->modx->getOption('thumbX', $properties, 100),
                'thumbY' => (integer)$this->modx->getOption('thumbY', $properties, 100),
                'addJquery' => (bool)$this->modx->getOption('addJquery', $properties, false),
                'addJscript' => $this->modx->getOption('addJscript', $properties, true),
                'addCss' => $this->modx->getOption('addCss', $properties, true),
                'debug' => (bool)$this->getOption('debug', $properties, false));
            $this->config = array_merge($this->config, $config);
            $_SESSION['migxfineuploader'][$this->config['uid'] . 'config'] = $this->config;
        }
        if (!@is_dir($this->config['cachePath'])) {
            if (!@mkdir($this->config['cachePath'], 0755)) {
                $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not create the cache path.', '', 'MigxFineUploader');
            }
            ;

        }

        $this->clearCache($this->config['cacheExpires']);

        return true;
    }
    
    public function loadInitialItems(){
        $isPostBack = isset($_POST['hidSubmit']) && $_POST['hidSubmit'] == 'true' ? true : false;
        $isAjax = isset($_REQUEST['action']) ? true : false;
        $tvname = $this->getOption('tvname');

        if (!$isAjax && !$isPostBack) {
            //clear Session and load exisisting files from MIGX - TV of resource
            $resource_id = isset($_POST['np_doc_id']) ? $_POST['np_doc_id'] : 0;
            $resource_path = $this->getResourcePath($resource_id);

            $_SESSION['migxfineuploader'][$this->config['uid']] = array();
            if (!empty($resource_id) && $resource = $this->modx->getObject('modResource', $resource_id)) {
                $items = $resource->getTVValue($tvname);
                $items = json_decode($items, true);

                foreach ($items as $item) {
                    $uuid = $item['uuid'];
                    $fileInfo = array();

                    $fileInfo['originalName'] = $item['image'];
                    $path = $this->config['cachePath'];
                    $file = $path . $uuid . DIRECTORY_SEPARATOR . $fileInfo['originalName'];
                    if (file_exists($file)) {
                        $fileInfo['path'] = $path . $uuid . DIRECTORY_SEPARATOR;
                        $item['thumbName'] = $this->generateThumbnail($fileInfo);
                        $item['size'] = filesize($file);
                    } else {
                        $path = $resource_path;
                        $file = $path . $uuid . DIRECTORY_SEPARATOR . $fileInfo['originalName'];
                        if (file_exists($file)) {
                            $fileInfo['path'] = $path . $uuid . DIRECTORY_SEPARATOR;
                            $item['thumbName'] = $this->generateThumbnail($fileInfo);
                            $item['size'] = filesize($file);
                            $source = $path . $uuid . DIRECTORY_SEPARATOR . $item['thumbName'];
                            $target = $this->config['cachePath'] . $uuid . DIRECTORY_SEPARATOR . $item['thumbName'];
                            rename($source, $target);
                        }
                    }

                    $item['base_url'] = $this->config['cacheUrl'] . $uuid . DIRECTORY_SEPARATOR;

                    $item['originalName'] = $item['image'];
                    $_SESSION['migxfineuploader'][$this->config['uid']][] = $item;
                }
            }
        } 
        return true;       
    }

    /**
     * Gets resource-specific file-path
     *
     * @access public
     * @param int $resource_id The id of the resource
     * @param bool $create Should the directory be created?
     */
    public function getResourcePath($resource_id, $create = false) {
        $resourcePath = $this->modx->getOption('base_path') . $this->getOption('resourceBasePath') . $resource_id . DIRECTORY_SEPARATOR;
        if ($create) {
            $this->rmkdir($resourcePath);
        }
        return $resourcePath;
    }

    /**
     * Gets a Chunk and caches it; also falls back to file-based templates
     * for easier debugging.
     *
     * @access public
     * @param string $name The name of the Chunk
     * @param array $properties The properties for the Chunk
     * @return string The processed content of the Chunk
     */
    public function getChunk($name, array $properties = array()) {
        $chunk = null;
        if (!isset($this->chunks[$name])) {
            $chunk = $this->modx->getObject('modChunk', array('name' => $name), true);
            if (empty($chunk)) {
                $chunk = $this->_getTplChunk($name);
                if ($chunk == false)
                    return false;
            }
            $this->chunks[$name] = $chunk->getContent();
        } else {
            $o = $this->chunks[$name];
            $chunk = $this->modx->newObject('modChunk');
            $chunk->setContent($o);
        }
        $chunk->setCacheable(false);
        return $chunk->process($properties);
    }
    /**
     * Returns a modChunk object from a template file.
     *
     * @access private
     * @param string $name The name of the Chunk. Will parse to name.chunk.tpl
     * @return modChunk/boolean Returns the modChunk object if found, otherwise
     * false.
     */
    private function _getTplChunk($name) {
        $chunk = false;
        $f = $this->config['chunksPath'] . strtolower($name) . '.chunk.html';
        if (file_exists($f)) {
            $o = file_get_contents($f);
            $chunk = $this->modx->newObject('modChunk');
            $chunk->set('name', $name);
            $chunk->setContent($o);
        }
        return $chunk;
    }


    /**
     * Generate a thumbnail with a random name for an image.
     *
     * @access public
     * @param array $fileInfo An array of file information.
     * @return string html file list to prefill the template
     */
    public function generateThumbnail($fileInfo = array(), $makeNameUnique = false) {
        $filenameKey = $makeNameUnique ? 'uniqueName' : 'originalName';
        if (file_exists($fileInfo['path'] . $fileInfo[$filenameKey])) {
            if (!isset($fileInfo['thumbName'])) {
                $path_info = pathinfo($fileInfo[$filenameKey]);
                $thumbOptions = array();
                if (in_array(strtolower($path_info['extension']), array(
                    'jpg',
                    'jpeg',
                    'png',
                    'gif'))) {
                    $thumbOptions['src'] = $fileInfo['path'] . $fileInfo[$filenameKey];
                    if ($this->config['thumbX']) {
                        $thumbOptions['w'] = $this->config['thumbX'];
                    }
                    if ($this->config['thumbY']) {
                        $thumbOptions['h'] = $this->config['thumbY'];
                    }
                    if ($this->config['thumbX'] && $this->config['thumbY']) {
                        $thumbOptions['zc'] = '1';
                    }
                } else {
                    $thumbOptions['src'] = $this->config['assetsPath'] . '/images/generic.png';
                    $thumbOptions['aoe'] = '1';
                    $thumbOptions['fltr'] = array('wmt|' . strtoupper($path_info['extension']) . '|5|C|000000');
                    if ($this->config['thumbX']) {
                        $thumbOptions['w'] = $this->config['thumbX'];
                    }
                    if ($this->config['thumbY']) {
                        $thumbOptions['h'] = $this->config['thumbY'];
                    }
                    if ($this->config['thumbX'] && $this->config['thumbY']) {
                        $thumbOptions['zc'] = '1';
                    }
                    $thumbOptions['f'] = 'png';
                    $path_info['extension'] = 'png';
                }
                $thumbName = md5($path_info['basename'] . time() . '.thumb') . '.' . $path_info['extension'];

                // generate Thumbnail & save it
                $phpThumb = new modPhpThumb($this->modx, $thumbOptions);
                $phpThumb->initialize();
                if ($phpThumb->GenerateThumbnail()) {
                    if (!$phpThumb->RenderToFile($fileInfo['path'] . $thumbName)) {
                        $this->modx->log(modX::LOG_LEVEL_ERROR, 'Thumbnail generation: Thumbnail not saved.' . "\nDebugmessages:\n" . implode("\n", $phpThumb->debugmessages), '', 'MigxFineUploader');
                        $this->debug[] = 'Thumbnail generation: Thumbnail not saved.' . "\nDebugmessaes:\n" . implode("\n", $phpThumb->debugmessages);
                    } else {
                        $filePerm = (int)$this->config['newFilePermissions'];
                        if (!@chmod($fileInfo['path'] . $thumbName, octdec($filePerm))) {
                            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not change the thumbnail file permission.', '', 'MigxFineUploader');
                        }
                        ;
                    }
                } else {
                    $this->modx->log(modX::LOG_LEVEL_ERROR, 'Thumbnail generation: Thumbnail not created.' . "\nDebugmessages:\n" . implode("\n", $phpThumb->debugmessages), '', 'MigxFineUploader');
                    $this->debug[] = 'Thumbnail generation: Thumbnail not created.' . "\nDebugmessaes:\n" . implode("\n", $phpThumb->debugmessages);
                }
                $fileInfo['thumbName'] = $thumbName;
            }
            return $fileInfo['thumbName'];
        } else {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Thumbnail generation: Original file not found.', '', 'MigxFineUploader');
            $this->debug[] = 'Thumbnail generation: Original file not found';
            return false;
        }
    }

    /**
     * Retreive already uploaded files.
     *
     * @access public
     * @param array| $files Array of filenames (relative to $modx->getOption['assetsPath'])
     * @return void
     */
    public function retrieveUploads($files = array()) {
        foreach ($files as $file) {
            $file = str_replace($this->modx->getOption('assets_url'), '', '/' . ltrim($file, '/'));
            $pathinfo = pathinfo($file);
            if (file_exists($this->modx->getOption('assets_path') . $file)) {
                $fileInfo = array();

                // Get original file info
                $originalName = $pathinfo['basename'];
                $originalExtension = $pathinfo['extension'];
                $originalFilename = (isset($pathinfo['filename'])) ? $pathinfo['filename'] : substr($originalName, 0, strrpos($originalName, '.'));
                $path = $this->modx->getOption('assets_path') . $pathinfo['dirname'] . '/';

                // Prepare session file info
                $fileInfo['originalName'] = $originalName;
                $fileInfo['originalPath'] = $path;
                $fileInfo['originalBaseUrl'] = $this->modx->getOption('assets_url');
                $fileInfo['path'] = $this->config['cachePath'];
                $fileInfo['base_url'] = $this->config['cacheUrl'];

                // Check if file is already in session
                $found = false;
                foreach ($_SESSION['migxfineuploader'][$this->config['uid']] as $sessionInfo) {
                    if ($sessionInfo['originalName'] === $fileInfo['originalName']) {
                        $found = true;
                        break;
                    }
                }

                // create unique filename and set permissions
                if (empty($fileInfo['uniqueName'])) {
                    $fileInfo['uniqueName'] = md5($originalFilename . time()) . '.' . $originalExtension;
                }
                if (!@copy($fileInfo['originalPath'] . $fileInfo['originalName'], $fileInfo['path'] . $fileInfo['uniqueName'])) {
                    $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not copy the uploaded file to the upload cache.', '', 'MigxFineUploader');
                }
                ;
                $filePerm = (int)$this->config['newFilePermissions'];
                if (!@chmod($fileInfo['path'] . $fileInfo['uniqueName'], octdec($filePerm))) {
                    $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not change the uploaded file permission in the upload cache.', '', 'MigxFineUploader');
                }
                ;

                // create thumbnail
                $fileInfo['thumbName'] = $this->generateThumbnail($fileInfo);
                if ($fileInfo['thumbName']) {
                    // fill session
                    if (!$found) {
                        $_SESSION['migxfineuploader'][$this->config['uid']][] = $fileInfo;
                    }
                } else {
                    $this->modx->log(modX::LOG_LEVEL_ERROR, 'Thumbnail generation: Original file not found.', '', 'MigxFineUploader');
                    $this->debug[] = 'Thumbnail generation: Original file not found';
                    @unlink($fileInfo['path'] . $fileInfo['uniqueName']);
                }
            } else {
                // Check if not found file is in session and delete the unique file and the thumbnail
                foreach ($_SESSION['migxfineuploader'][$this->config['uid']] as $sessionInfo) {
                    if ($sessionInfo['originalName'] === $pathinfo['basename']) {
                        @unlink($this->config['cachePath'] . $sessionInfo['uniqueName']);
                        @unlink($this->config['cachePath'] . $sessionInfo['thumbName']);
                        break;
                    }
                }
            }
        }
    }

    /**
     * Save the uploaded files to the specified target.
     *
     * @access public
     * @param string $target Target path (relative to $modx->getOption['assets_path'])
     * @return boolean|string
     */
    public function saveUploads($target, $clearQueue = false) {
        $errors = false;
        $target = rtrim($target, '/') . '/';
        if (!file_exists($this->modx->getOption('assets_path') . $target)) {
            $mode = octdec($this->modx->getOption('new_folder_permissions', null, 0777));
            $this->rmkdir($this->modx->getOption('assets_path') . $target, $mode);
        }
        foreach ($_SESSION['migxfineuploader'][$this->config['uid']] as $fileId => &$fileInfo) {
            if (file_exists($fileInfo['path'] . $fileInfo['uniqueName'])) {
                if ($this->getOption('allowOverwrite')) {
                    $pathinfo = pathinfo($fileInfo['originalName']);
                    $i = '';
                    while (file_exists($this->modx->getOption('assets_path') . $target . $pathinfo['filename'] . (($i) ? '_' . $i : '') . '.' . $pathinfo['extension'])) {
                        $i = ($i == '') ? 1 : $i++;
                    }
                    $fileInfo['originalName'] = $pathinfo['filename'] . (($i) ? $i : '') . '.' . $pathinfo['extension'];
                }
                if (!@copy($fileInfo['path'] . $fileInfo['uniqueName'], $this->modx->getOption('assets_path') . $target . $fileInfo['originalName'])) {
                    $errors = $this->modx->lexicon('migxfineuploader.targetNotWritable');
                    $this->modx->log(modX::LOG_LEVEL_ERROR, $errors, '', 'MigxFineUploader');
                } else {
                    $fileInfo['originalPath'] = $this->modx->getOption('assets_path') . $target;
                    $fileInfo['originalBaseUrl'] = $this->modx->getOption('assets_url') . $target;
                }
                if ($clearQueue) {
                    @unlink($fileInfo['path'] . $fileInfo['uniqueName']);
                    @unlink($fileInfo['path'] . $fileInfo['thumbName']);
                    unset($fileInfo[$fileId]);
                }
            }
        }
        return $errors;
    }

    /**
     * Delete existing files in target that are deleted in $_SESSION.
     *
     * @access public
     * @return void
     */
    public function deleteExisting() {
        foreach ($_SESSION['migxfineuploader'][$this->config['uid'] . 'delete'] as &$fileInfo) {
            if (isset($fileInfo['originalPath']) && file_exists($fileInfo['originalPath'] . $fileInfo['originalName'])) {
                @unlink($fileInfo['originalPath'] . $fileInfo['originalName']);
            }
        }
        $_SESSION['migxfineuploader'][$this->config['uid'] . 'delete'] = array();
    }

    /**
     * Get the current uploads in specified format.
     *
     * @access public
     * @param string $format Format of the returned value
     * @return string Current uploads formatted by $format
     */
    public function getValue($format) {
        $output = array();
        foreach ($_SESSION['migxfineuploader'][$this->config['uid']] as $fileInfo) {
            $output[] = (isset($fileInfo['originalBaseUrl']) ? $fileInfo['originalBaseUrl'] : $fileInfo['base_url']) . $fileInfo['originalName'];
        }
        switch ($format) {
            case 'json':
                $output = json_encode($output);
                break;
            case 'csv':
            default:
                $output = implode(',', $output);
        }
        return $output;
    }

    /**
     * Clear the current uploads.
     *
     * @access public
     * @param void
     * @return void
     */
    public function clearValue() {
        if (isset($_SESSION['migxfineuploader'][$this->config['uid']])) {
            unset($_SESSION['migxfineuploader'][$this->config['uid']]);
            unset($_SESSION['migxfineuploader'][$this->config['uid'] . 'config']);
        }
    }

    /**
     * Clear all files in cache older than specified hours.
     *
     * @access public
     * @param integer $hours Specified hours
     * @return void
     */
    public function clearCache($hours = 4) {
        $cache = opendir($this->config['cachePath']);
        while (false !== ($file = readdir($cache))) {
            $filelastmodified = filemtime($this->config['cachePath'] . $file);
            if (((time() - $filelastmodified) > ($hours * 3600)) && is_file($this->config['cachePath'] . $file)) {
                @unlink($this->config['cachePath'] . $file);
            }
        }
        closedir($cache);
    }

    /**
     * Output the form inputs.
     *
     * @access public
     * @return string The output
     */
    public function output() {
        
        $this->loadInitialItems();

        $assetsUrl = $this->getOption('assetsUrl');
        $jsUrl = $this->getOption('jsUrl');
        $jsSourceUrl = $this->getOption('sourceUrl') . 'js/';
        $cssUrl = $this->getOption('cssUrl');
        $cssSourceUrl = $this->getOption('sourceUrl') . 'css/';

        if ($this->config['addJquery']) {
            $this->modx->regClientScript('http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js');
        }
        if ($this->config['addCss']) {
            if ($this->getOption('debug') && ($assetsUrl != MODX_ASSETS_URL . 'components/' . $this->namespace . '/')) {
                $this->modx->regClientCSS($cssSourceUrl . 'fine-uploader.css');
            } else {
                $this->modx->regClientCSS($cssUrl . 'fine-uploader-new.min.css');
            }
        }
        if ($this->config['addJscript']) {
            if ($this->getOption('debug') && ($assetsUrl != MODX_ASSETS_URL . 'components/' . $this->namespace . '/')) {
                $this->modx->regClientScript($jsSourceUrl . 'fine-uploader.js');
                //$this->modx->regClientScript($jsSourceUrl . 'migxfineuploader.js');
            } else {
                $this->modx->regClientScript($jsUrl . 'fine-uploader.min.js');
            }
        }
        //$this->modx->smarty->assign('_lang', $this->modx->lexicon->fetch('migxfineuploader.', true));
        //$this->modx->smarty->assign('params', $this->config);
        //$this->modx->regClientScript($this->modx->smarty->fetch($this->config['templatesPath'] . 'web/script.tpl'), true);

        // preload files from $_SESSION
        $itemList = '';
        //unset($_SESSION['migxfineuploader']);
        /*
        if (is_array($_SESSION['migxfineuploader'][$this->config['uid']])) {
        $itemList = $this->loadFiles($_SESSION['migxfineuploader'][$this->config['uid']]);
        }
        */
        //$this->modx->smarty->assign('items', $itemList);
        //return $this->modx->smarty->fetch($this->config['templatesPath'] . 'web/uploadSection.tpl');

        $this->modx->regClientScript($this->getChunk('mfu.migx.template', $this->config));
        $this->modx->regClientScript($this->getChunk('mfu.inituploader', $this->config));

        $output = '<div id="uploader"></div>';
        return $output;
    }


    /**
     * Output debug informations.
     *
     * @access public
     * @return string The debug output
     */
    public function debugOutput() {
        if ($this->config['debug']) {
            $this->debug[] = '$_SESSION["migxfineuploader"]:<pre>' . print_r($_SESSION['migxfineuploader'][$this->config['uid']], true) . '</pre>';
        }
        return implode('<br/>', $this->debug);
    }

    public function OnDocFormSave(&$resource) {
        if (isset($_POST['mfu_tvnames'])) {
            $tvnames = is_array($_POST['mfu_tvnames']) ? $_POST['mfu_tvnames'] : array($_POST['mfu_tvnames']);
            foreach ($tvnames as $tvname) {
                if (isset($_POST[$tvname . '_uid'])) {
                    $uid = $_POST[$tvname . '_uid'];
                    $items = array();
                    if (is_array($_SESSION["migxfineuploader"][$uid])) {
                        foreach ($_SESSION["migxfineuploader"][$uid] as $item) {
                            if (isset($item['uuid'])) {
                                $items[$item['uuid']] = $item;
                            }
                        }
                    }
                    $migx_items = array();
                    if (isset($_POST[$tvname . '_uuid'])) {
                        $uuids = is_array($_POST[$tvname . '_uuid']) ? $_POST[$tvname . '_uuid'] : array($_POST[$tvname . '_uuid']);
                        $migx_id_max = 0;
                        if (count($uuids) > 0) {
                            $resource_path = $this->getResourcePath($resource->get('id'), true);
                        }
                        foreach ($uuids as $uuid) {
                            if (isset($items[$uuid])) {
                                $source = $items[$uuid]['path'] . $items[$uuid]['originalName'];
                                $targetPath = $resource_path . $uuid . DIRECTORY_SEPARATOR;
                                $this->rmkdir($targetPath);
                                $target = $targetPath . $items[$uuid]['originalName'];

                                if (file_exists($source) && $source != $target) {
                                    rename($source, $target);
                                }

                                if (file_exists($target)) {
                                    $item = $items[$uuid];
                                    $item['MIGX_id'] = $migx_id_max + 1;
                                    if (isset($items[$uuid]['MIGX_id']) && !empty($items[$uuid]['MIGX_id'])) {
                                        $item['MIGX_id'] = $items[$uuid]['MIGX_id'];
                                    }
                                    if ($item['MIGX_id'] > $migx_id_max) {
                                        $migx_id_max = $item['MIGX_id'];
                                    }
                                    $item['uuid'] = $uuid;
                                    $item['image'] = $items[$uuid]['originalName'];
                                    $migx_items[] = $item;
                                }

                            }
                        }
                    }
                    $resource->setTVValue($tvname, json_encode($migx_items));
                }
            }


        }
        return '';
    }

    /**
     * Recursive mkdir function
     *
     * @param $strPath
     * @param $mode
     * @return bool
     */
    function rmkdir($strPath, $mode) {
        if (is_dir($strPath)) {
            return true;
        }
        $pStrPath = dirname($strPath);
        if (!$this->rmkdir($pStrPath, $mode)) {
            return false;
        }
        return @mkdir($strPath);
    }
}
