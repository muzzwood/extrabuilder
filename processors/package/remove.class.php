<?php

/**
 * Remove a Package and all files.
 *
 * @package grv
 * @subpackage processors/package
 */
class GrvPackageRemoveProcessor extends modObjectRemoveProcessor
{
    public $classKey = 'grvPackage';
    public $languageTopics = array('grv:default');
	public $objectType = 'grv.package';

	/**
	 * Package key value
	 * @var string packageKey
	 */
	public $packageKey = '';

	/**
	 * Table array
	 * @var array tables
	 */
	public $tables = '';

	/**
	 * Core path based on pattern
	 * @var array corePath
	 */
	public $corePath = '';

	/**
	 * Assets path based on pattern
	 * @var array assetsPath
	 */
	public $assetsPath = '';
	
	/**
     * Can contain pre-removal logic; return false to prevent remove.
     * @return boolean
     */
	public function beforeRemove() 
	{
		// Store the key value
		$this->packageKey = $this->object->get('package_key');
		
		if (empty($this->packageKey)) {
			$this->addFieldError('package_key', 'Unable to determine Package Key.');
			return !$this->hasErrors();
		}

		// Determine the core path
		$this->corePath = str_replace('{core_path}', MODX_CORE_PATH, $this->object->get('core_path'));
		$this->corePath = str_replace('{base_path}', MODX_BASE_PATH, $this->corePath);
		$this->corePath = str_replace('{package_key}', $this->packageKey, $this->corePath);

		// Also set the assets path
		$this->assetsPath = str_replace('{assets_path}', MODX_ASSETS_PATH, $this->object->get('assets_path'));
		$this->assetsPath = str_replace('{core_path}', MODX_CORE_PATH, $this->assetsPath);
		$this->assetsPath = str_replace('{base_path}', MODX_BASE_PATH, $this->assetsPath);
		$this->assetsPath = str_replace('{package_key}', $this->packageKey, $this->assetsPath);

		// Add the package so we can use getMany
		$this->modx->addPackage($this->packageKey, $this->corePath.'model/');

		// Get the related object entries and loop through
		$objectEntries = $this->object->getMany('Objects');
		$dropErrors = [];
		if ($objectEntries) {
			foreach ($objectEntries as $entry) {
				$class = $entry->get('class');
				if ($class) {
					// Get the xpdo manager and drop the table
					$manager = $this->modx->getManager();
					if (!$manager->removeObjectContainer($class)) {
						// Add to the error list
						$dropErrors[] = $class;
					}
				}
			}
		}
		else {
			$this->modx->log(xPDO::LOG_LEVEL_ERROR, 'Unable to get child objects.');
		}

		// Delete all files
		if (is_dir($this->corePath)) {
			$this->rrmdir($this->corePath);
		}
		if (is_dir($this->assetsPath)) {
			$this->rrmdir($this->assetsPath);
		}

		// Delete the namespace
		$nameSpace = $this->modx->getObject('modNamespace', ['name' => $this->packageKey]);
		if ($nameSpace) {
			$nameSpace->remove();
		}

		// Return false to block remove
		return !$this->hasErrors();
	}
	
	/**
	 * Delete directory recursively
	 * 
	 */
	private function rrmdir($src) {
		$dir = opendir($src);
		if ($dir) {
			while(false !== ( $file = readdir($dir)) ) {
				if (( $file != '.' ) && ( $file != '..' )) {
					$full = $src . '/' . $file;
					if ( is_dir($full) ) {
						$this->rrmdir($full);
					}
					else {
						unlink($full);
					}
				}
			}

			closedir($dir);
			rmdir($src);
		}
	}
}

return 'GrvPackageRemoveProcessor';