<?php

namespace Zoomyboy\PhpSsh;

use \phpseclib\Net\SSH2;
use \phpseclib\Crypt\RSA;
use Zoomyboy\PhpSsh\Exception\ConnectionFailException;

class Ssh {

	private $connection = false;

	public static function connect($host, $user, $privateKeyFile) {
		$ssh = new self();
		$ssh->connection = new SSH2($host);
		$key = new RSA();
		$key->loadKey(file_get_contents($privateKeyFile));
		if (!$ssh->connection->login($user, $key)) {
			throw new ConnectionFailException('Connection to '.$host.' failed');
		}

		return $ssh;
	}

	public function __call($method, $params) {
		return $this->connection->{$method}($params[0]);
	}

	public static function getKeyFiles($path) {
		return glob($path.'/*.pub');
	}
}
