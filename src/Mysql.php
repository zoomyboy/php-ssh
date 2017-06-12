<?php

namespace Zoomyboy\PhpSsh;

class Mysql {
	private $host;
	private $user;
	private $password;
	private $ssh;
	private $dumpCommand = false;

	/** @var string CREDENTIALS_FILE The file where the credentials are stored */
	const CREDENTIALS_FILE="cred.cnf";

	public function __construct($host, $user, $password, $ssh) {
		$this->host = $host;
		$this->user = $user;
		$this->password = $password;
		$this->ssh = $ssh;
	}

	public function check($database) {
		$result = $this->ssh->exec('mysql --defaults-extra-file='.$this->getCredentialsFile().' '.$database.' -e ""');

		$this->disconnect();
		return strlen(trim($result)) == 0;
	}

	public function dump($database) {
		$this->dumpCommand = 'mysqldump --defaults-extra-file='.$this->getCredentialsFile().' '.$database;

		return $this;
	}

	public function output() {
		$output = $this->ssh->exec($this->dumpCommand);
		$this->disconnect();

		return $output;
	}

	public function disconnect() {
		$this->ssh->exec('rm '.self::CREDENTIALS_FILE);
	}

	public function toFile($file) {
		$this->ssh->exec($this->dumpCommand . ' > '.$file);
		$this->disconnect();
	}

	private function getCredentialsFile() {
		$this->ssh->exec('printf \'[client]\nhost="'.$this->maskPrintf($this->host).'"\nuser="'.$this->maskPrintf($this->user).'"\npassword="'
			.$this->maskPrintf($this->password)
		.'"\n\' > '.self::CREDENTIALS_FILE);
		
		return self::CREDENTIALS_FILE;
	}

	public static function maskPrintf($str, $printfDelimiter='\'', $stringDelimiter='"') {
		return str_replace('"', '\\\\"', self::forPrintf($str, '\''));
	}

	public static function forPrintf($string, $delimiter = '') {
		$string = str_replace('%', '%%', $string);
		if ($delimiter == "'") {
			$string = str_replace("'", "'\\''", $string);
		}

		return $string;
	}
}
