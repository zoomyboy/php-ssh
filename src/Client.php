<?php

namespace Zoomyboy\PhpSsh;

use Zoomyboy\PhpSsh\Exceptions\ConnectionFailException;
use Zoomyboy\PhpSsh\Exceptions\FileDoesntExistsRemote;
use League\Flysystem\Sftp\SftpAdapter;
use League\Flysystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use phpseclib\Net\SSH2;
use phpseclib\Crypt\RSA;

class Client {
	private $user;
	private $host;
	private $ssh2;
	public $authValue;
	public $authMthod;
	public $keyfile;

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

	public static function authMethodFromIndex($index) {
		return 'with' . AuthMethod::find($index)->first()->title;
	}

	//--------------------------------- Boilerplate ---------------------------------
	//*******************************************************************************
	public static function auth($host, $user) {
		$ssh = new self();
		$ssh->ssh2 = new SSH2($host);
		$ssh->host = $host;
		$ssh->user = $user;

		return $ssh;
	}

	public static function login($host, $user) {
		return self::auth($host, $user);
	}

	public function connect() {
		try {
			$this->ssh2->login($this->user, $this->authValue);
			$this->ssh2->setTimeout(self::TIMEOUT);
			return $this;
		} catch(\ErrorException $e) {
			throw new ConnectionFailException('Host not found!', 2);
		}
	}

	public function withKeyFile($keyFile) {
		$this->keyfile = $keyFile;
		$key = new RSA();
		$key->loadKey(file_get_contents($keyFile));
		$this->authValue = $key;
		$this->authMethod = 'KeyFile';

		return $this;
	}

	public function withPassword($password) {
		$this->authValue = $password;
		$this->authMethod = 'Password';
		return $this;
	}

	public function check() {
		try {
			return $this->ssh2->login($this->user, $this->authValue) == true;
		} catch(\ErrorException $e) {
			return false;
		}
	}

	public function __call($method, $command) {

		return $this->ssh2->{$method}($command[0]);
	}

	//------------------------------------- Ui --------------------------------------
	//*******************************************************************************
	/**
	 * Checks if the SSH Dir exists and can be read
	 */
	public function isWritable(string $dir) {
        return $this->exec("[ -w ".static::escapePath($dir)." ]; echo $?") === '0\n';
	}

    public static function escapePath($dir) {
        if (substr($dir, 0, 2) === '~/') {
            return "~/".static::escapeString(substr($dir, 2));
        }

        return static::escapeString($dir);
    }

    public static function escapeString($dir) {
        return "'".str_replace("'", "'\''", $dir)."'";
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

    /**
     * Tests if the last command was executed successful
     *
     * @return bool
     */
    public function lastCommandSucceeded() {
        return trim($this->exec('echo $?')) === '0';
    }

	private function commandSucceded($command) {
		return trim($this->exec($command.'; echo $?')) === '0';
	}

	public function dirExists($dir) {
		return $this->commandSucceded('[ -d "'.$this->mask($dir).'" ]');
	}

	public function fileExists($file) {
        return $this->exec("[ -f ".static::escapePath($file)." ]; echo $?") === '0\n';
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
        $code = $this->exec('mkdir -p '.static::escapePath($dir).' > /dev/null 2>&1; echo $?');

        return strpos($code, '0') !== false;

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
		return new Mysql($host, $user, $password, $this->ssh2);
	}

	public function upload($dir, $dest=false) {
		$dir = rtrim($dir, '/');

		if (substr($dir, 0, 1) != '/') {
			throw new \InvalidArgumentException('Relative Paths not allowed as source');
		}

		if (!file_exists($dir)) {
			return false;
		}

		if ($dest == false) {
			$dest = is_dir($dir) ? pathinfo($dir, PATHINFO_FILENAME) : pathinfo($dir, PATHINFO_BASENAME);
		}

		$localDir = [
		   'before' => pathinfo($dir, PATHINFO_DIRNAME).'/',
		   'file' => pathinfo($dir, PATHINFO_FILENAME).'/'
		];

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

		if ($this->authMethod == 'KeyFile') {
			$login['privateKey'] = $this->keyfile;
		} elseif ($this->authMethod == 'Password') {
			$login['password'] = $this->auth;
		}

		$adapter = new SftpAdapter($login);
		$filesystem = new Filesystem($adapter);

		$allFiles = (is_dir($dir)) ? glob_recursive($dir.'/*') : [$dir];

		foreach($allFiles as $file) {
			$remoteFile = $dest . str_replace($dir, '', $file);
			if (is_dir($file)) {
				$filesystem->createDir($remoteFile);
			} else {
				$stream = fopen($file, 'r+');
				$filesystem->writeStream($remoteFile, $stream);

				if (is_resource($stream)) {
					fclose($stream);
				}
			}

		}

		return true;
	}

	/**
	 * Creates tar archive of a folder / file
	 *
	 * @param string $archiveName Name of archive
	 * @param string $upload File / Folder to archive
	 *
	 * @return bool
	 */
	public function archive($archiveName) {
		return new Archive($archiveName, $this->ssh2);
	}

	public function isArchive($archive) {
		$return = $this->exec('file -b "'.$this->mask($archive).'"');

		return strpos($return, 'POSIX tar archive') !== false;
	}

	public function isCompressedArchive($archive) {
		$return = $this->exec('file -b "'.$this->mask($archive).'"');

		return strpos($return, 'gzip compressed data') !== false;
	}

	public function download($file, $local) {
		if (!$this->exists($file)) {
			throw new FileDoesntExistsRemote($file);
		}

		if (is_dir($local)) {
			$local = rtrim($local, '/').'/'.pathinfo($file, PATHINFO_BASENAME);
		}

		$login = [
			'host' => $this->host,
			'port' => 22,
			'username' => $this->user,
			'root' => '.',
			'timeout' => 10000000,
			'directoryPerm' => 0755
		];

		if ($this->authMethod === 'KeyFile') {
			$login['privateKey'] = $this->keyfile;
		} elseif ($this->authMethod === 'Password') {
			$login['password'] = $this->auth;
		}

		$adapter = new SftpAdapter($login);
		$filesystem = new Filesystem($adapter);

		$stream = $filesystem->readStream($file);
		$contents = stream_get_contents($stream);
		file_put_contents($local, $contents);

		if (is_resource($stream)) {
			fclose($stream);
		}

		return true;
	}

    /**
     * Set a custom phpseclib SSH Handler to work with
     *
     * @param phpseclib\Net\SSH2 $backend
     */
    public function setBackend(SSH2 $backend) {
        $this->ssh2 = $backend;
    }

    /**
     * Gets a new Client instance from a custom phpseclib Backend
     *
     * @param phpseclib\Net\SSH2 $backend
     * @return static
     */
    public static function fromBackend($backend) {
        $ssh = new static();
        $ssh->setBackend($backend);

        return $ssh;
    }
}
