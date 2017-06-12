<?php
namespace Zoomyboy\PhpSsh;
require(__DIR__.'/../helpers/functions.php');

use \phpseclib\Net\SSH2;
use \phpseclib\Crypt\RSA;
use Zoomyboy\PhpSsh\Exceptions\ConnectionFailException;
use League\Flysystem\Sftp\SftpAdapter;
use League\Flysystem\Filesystem;

class Ssh {

	private $connection = false;
	private $auth;

	private $host;
	private $user;
	private $authMethod;

	/* @var int $timeout Timeout for Ssh session */
	const TIMEOUT = 3000000;

	//---------------------------------- Key files ----------------------------------
	//*******************************************************************************
	public static function getPublicKeyFiles($path) {
		return glob($path.'/*.pub');
	}

	public static function getPrivateKeyFiles($path) {
		return array_filter(glob($path.'/*'), function($file) {
			return !preg_match('/\.pub$/', $file);
		});
	}
	
	//--------------------------------- Boilerplate ---------------------------------
	//*******************************************************************************
	public static function auth($host, $user) {
		$ssh = new self();
		$ssh->connection = new SSH2($host);
		$ssh->host = $host;
		$ssh->user = $user;

		return $ssh;
	}

	/**
	 * Alias for auth
	 */
	public static function login($host, $user) {
		return self::auth($host, $user);
	}

	public function connect() {
		try {
			$this->connection->login($this->user, $this->auth);
			$this->setTimeout(self::TIMEOUT);
			return $this;
		} catch(\ErrorException $e) {
			throw new ConnectionFailException('Host not found!', 2);
		}
	}

	public function withKeyFile($keyFile) {
		$key = new RSA();
		$key->loadKey(file_get_contents($keyFile));
		$this->auth = $key;
		$this->authMethod = 'keyFile';

		return $this;
	}

	public function withPassword($password) {
		$this->auth = $password;
		$this->authMethod = 'password';

		return $this;
	}

	public function check() {
		try {
			return $this->connection->login($this->user, $this->auth) == true;
		} catch(\ErrorException $e) {
			return false;
		}
	}

	public function __call($method, $params) {
		return $this->connection->{$method}($params[0]);
	}

	//------------------------------------- Ui --------------------------------------
	//*******************************************************************************
	/**
	 * Checks if the SSH Dir exists and can be read
	 */
	public function dirAccess($dir) {
		try {
			$result = $this->exec('ls -l "'.$this->mask($dir).'"');
			return strpos($result, 'No such file or directory') === false
				&& strpos($result, 'Datei oder Verzeichnis nicht gefunden') === false
				&& strpos($result, 'Keine Berechtigung') === false
				&& strpos($result, 'Permission denied') === false;
		} catch(\ErrorException $e) {
			return false;
		}
	}

	/**
	 * Gets the absolute path on the Remote machine
	 *
	 * @param string $dir Directory - relative to the Home-directory of the user who loggs in
	 *
	 * @return string The absolute path
	 */
	public function absolutePath($dir) {
		try {
			return trim($this->exec('readlink -f "'.$this->mask($dir).'"'));
		} catch(\ErrorException $e) {
			return false;
		}
	}

	private function commandSucceded($command) {
		return trim($this->exec($command.'; echo $?')) === '0';
	}

	public function dirExists($dir) {
		return $this->commandSucceded('[ -d "'.$this->mask($dir).'" ]');
	}

	public function fileExists($file) {
		return $this->commandSucceded('[ -f "'.$this->mask($file).'" ]');
	}

	public function isDir($dir) {
		return $this->dirExists ($dir);
	}

	public function isFile($file) {
		return $this->fileExists ($file);
	}

	public function exists($anything) {
		return $this->commandSucceded('[ -e "'.$this->mask($anything).'" ]');
	}

	private function mask($str) {
		return addcslashes($str, '";&');
	}

	public function mkdir($dir) {
		if (!$dir) {
			return false;
		}
		$this->exec('mkdir "'.$this->mask($dir).'"');
	}

	public function cat($file) {
		if (!$file) {
			return '';
		}
		return substr($this->exec('cat "'.$this->mask($file).'"'), 0, -1);
	}

	public function rm($dir) {
		if (!$dir) {
			return false;
		}
		$this->exec('rm -R "'.$this->mask($dir).'"');
	}

	public function authMysql($host, $user, $password) {
		return new Mysql($host, $user, $password, $this->connection);
	}

	public function upload($dir, $dest) {
		$beforeDir = pathinfo($dir, PATHINFO_DIRNAME).'/';

		if (!file_exists($dir)) {
			return false;
		}

		if ($this->exists($dest)) {
			return "File exists";
		}

		$login = [
			'host' => $this->host,
			'port' => 22,
			'username' => $this->user,
			'root' => '.',
			'timeout' => 10000000,
			'directoryPerm' => 0755
		];

		if ($this->authMethod == 'keyFile') {
			$login['privateKey'] = $this->auth;
		} elseif ($this->authMethod == 'password') {
			$login['password'] = $this->auth;
		}

		$adapter = new SftpAdapter($login);
		$filesystem = new Filesystem($adapter);
		foreach(glob_recursive($dir.'/*') as $file) {
			$localFile = str_replace($beforeDir, '', $file);
			if (is_dir($file)) {
				$filesystem->createDir($localFile);
			} else {
				$stream = fopen($file, 'r+');
				$filesystem->writeStream($localFile, $stream);

				if (is_resource($stream)) {
					fclose($stream);
				}
			}

		}

		return true;
	}
}
