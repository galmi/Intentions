<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	/**
     * @return Zend_Application_Module_Autoloader
     */
    protected function _initAutoload()
    {
        $moduleLoader = new Zend_Application_Module_Autoloader(
            array(
                'namespace' => '',
                'basePath'  => APPLICATION_PATH
            )
        );
        return $moduleLoader;
    }

		/**
	 * @return
	 */
	protected function _initTranslate()
	{

//		$writer = new Zend_Log_Writer_Stream( APPLICATION_PATH . '/../logs/untranslated.log');
//		$log    = new Zend_Log($writer);

		// get the translate resource from the ini file and fire it up
		$resource = $this->getPluginResource('translate');
		$translate = $resource->getTranslate();

		$cfg = $this->getOption('translate');
		$locale = $translate->getAdapter()->getLocale();
		if (!in_array($locale, $cfg['allow'])) {
			$translate->getAdapter()->setLocale($cfg['default']);
		}
		// add the log to the translate
//		$translate->setOptions(
//				array(
//					'log'             => $log,
//					'logUntranslated' => (APPLICATION_ENV!='development')
//				)
//			);
		// return the translate to get it in the registry and so on
		return $translate;
	}

	function _initRoutes()
	{
		$routes = $this->getOption('routes');

		$this->bootstrap("frontController");

		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/routes.ini', $routes['section']);
        $router = new Zend_Controller_Router_Rewrite();
        $router->addConfig($config, 'routes');

		$frontController = Zend_Controller_Front::getInstance();
        $frontController->setRouter($router);

		if (isset($routes['block_modules']))
		{
			App_Controller_Plugin_ModuleBlock::setModulesToBlock($routes['block_modules']);
		}
	}

	function _initMongodb()
	{
		require_once 'Shanty/Mongo.php';

		$mongodb = $this->getOption('mongodb');
		$connections = array(
			'master' => array(
				'host' => $mongodb['host'],
				'port' => $mongodb['port'],
				'username' => $mongodb['username'],
				'password' => $mongodb['password']
			)
		);
		Shanty_Mongo::addConnections($connections);
    App_Model_Base::setDbName($mongodb['db']);
	}

	function _initVkontakte() {
		$options = $this->getOption('vkontakte');
		App_Vkontakte::init($options);
	}

	function _initConf() {
		$options = $this->getOption('conf');
		Zend_Registry::set('authors', $options['authors']);
	}
}

