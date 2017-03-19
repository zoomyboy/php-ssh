<?php

namespace Zoomyboy\PhpSsh;

use \phpseclib\Net\SSH2;
use \phpseclib\Crypt\RSA;
use Zoomyboy\PhpSsh\Exceptions\ConnectionFailException;

class Ssh {

	private $connection = false;

	public static function connect($host, $user, $privateKeyFile) {
		try {
			$ssh = new self();
			$ssh->connection = new SSH2($host);
			$key = new RSA();
			$key->loadKey(file_get_contents($privateKeyFile));
			if (!$ssh->connection->login($user, $key)) {
				throw new ConnectionFailException('Connection to '.$host.' failed', 1);
			} 

			return $ssh;
		} catch(\ErrorException $e) {
			throw new ConnectionFailException('Host not found!', 2);
		}
	}

	public function __call($method, $params) {
		return $this->connection->{$method}($params[0]);
	}

	public static function getPublicKeyFiles($path) {
		return glob($path.'/*.pub');
	}

	public static function getPrivateKeyFiles($path) {
		return array_filter(glob($path.'/*'), function($file) {
			return !preg_match('/\.pub$/', $file);
		});
	}
}
