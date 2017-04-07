<?php

namespace Zoomyboy\PhpSsh;

use \phpseclib\Net\SSH2;
use \phpseclib\Crypt\RSA;
use Zoomyboy\PhpSsh\Exceptions\ConnectionFailException;

class Ssh {

	private $connection = false;
	private $auth;

	public static function auth($host, $user) {
		$ssh = new self();
		$ssh->connection = new SSH2($host);
		$ssh->host = $host;
		$ssh->user = $user;

		return $ssh;
	}

	public function check() {
		try {
			return $this->connection->login($this->user, $this->auth) == true;
		} catch(\ErrorException $e) {
			return false;
		}
	}

	public function withKeyFile($keyFile) {
		$key = new RSA();
		$key->loadKey(file_get_contents($keyFile));
		$this->auth = $key;

		return $this;
	}

	public function withPassword($password) {
		$this->auth = $password;

		return $this;
	}

	public function connect() {
		try {
			$this->connection->login($this->user, $this->auth);
			return $this;
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
