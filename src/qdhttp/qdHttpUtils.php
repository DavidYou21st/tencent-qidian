<?php

namespace TencentQidian\App\Qdhttp;

use TencentQidian\App\Qderror\NetWorkError;
use TencentQidian\App\Qderror\HttpError;
use TencentQidian\App\Qderror\InternalError;

class QdHttpUtils
{
	/**
	 * http get
	 * @param string $url
	 * @return http response body
	 */
	static public function httpGet($url)
	{
		self::__checkDeps();
		$ch = curl_init();

		self::__setSSLOpts($ch, $url);

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		return self::__exec($ch);
	}

	/**
	 * http post
	 * @param string $url
	 * @param string or dict $postData
	 * @return http response body
	 */
	static public function httpPost($url, $postData)
	{
		self::__checkDeps();
		$ch = curl_init();

		self::__setSSLOpts($ch, $url);

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		return self::__exec($ch);
	}

	//
	// private:
	//

	static private function __setSSLOpts($ch, $url)
	{
		if (stripos($url, "https://") !== false) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_SSLVERSION, 1);
		}
	}

	static private function __exec($ch)
	{
		$output = curl_exec($ch);
		$status = curl_getinfo($ch);
		curl_close($ch);

		if ($output === false) {
			throw new NetWorkError("network error");
		}

		if (intval($status["http_code"]) != 200) {
			throw new HttpError(
				"unexpected http code " . intval($status["http_code"])
			);
		}

		return $output;
	}

	static private function __checkDeps()
	{
		if (!function_exists("curl_init")) {
			throw new InternalError("missing curl extend");
		}
	}
}
