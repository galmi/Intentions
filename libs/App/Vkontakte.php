<?php

class App_Vkontakte
{

	protected static $appId;
	protected static $appSecret;
    public static $apiUrl = 'http://api.vkontakte.ru/api.php';

	public static function init($options)
	{
		self::$appId = $options['app_id'];
		self::$appSecret = $options['secret'];
	}

	public static function getAppID()
	{
		return self::$appId;
	}

	public static function getSecret()
	{
		return self::$appSecret;
	}


	public static function api($api_url, $method, $params = false)
	{
		if (!$params) $params = array();
		$params['api_id'] = self::$appId;
		$params['v'] = '3.0';
		$params['method'] = $method;
		$params['timestamp'] = time();
		$params['format'] = 'json';
		$params['random'] = rand(0, 10000);
		ksort($params);
		$sig = '';
		foreach ($params as $k => $v) {
			if ($k != 'sid') {
				$sig .= $k . '=' . $v;
			}
		}
		$sig .= self::$appSecret;
		//return $sig;
		$params['sig'] = md5($sig);
		$query = $api_url . '?' . self::params($params);
		$res = file_get_contents($query);
		return json_decode($res, true);
		//return $query;
	}

	protected static function params($params)
	{
		$pice = array();
		foreach ($params as $k => $v) {
			$pice[] = $k . '=' . urlencode($v);
		}
		return implode('&', $pice);
	}

	public static function checkAuthKey($params)
	{
		$result = false;
		if (isset($params['api_id']) && isset($params['viewer_id']) && isset($params['auth_key'])) {
			$mySign = md5($params['api_id'] . '_' . $params['viewer_id'] . '_' . self::$appSecret);
			if ($mySign == $params['auth_key']) {
				$result = true;
			}
		}
		return $result;
	}

	public function getAJAXfriendsApp()
	{
		if ($this->getRequest()->isXmlHttpRequest()) {

		}
		else exit;
	}
}