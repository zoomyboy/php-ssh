<?php

namespace Zoomyboy\PhpSsh;

class Archive {

	private $exists = false;
	private $filename = false;
	private $ssh;

	public function __construct($filename, $ssh) {
		$this->filename = $filename;
		$this->ssh = $ssh;

		return $this;
	}

	public function add($upload) {
		if ($this->exists) {
			$res = $this->ssh->exec('tar --append --file="'.$this->mask($this->filename).'" "'.$this->mask($upload).'"');
		} else {
			$this->ssh->exec('tar cf "'.$this->mask($this->filename).'" "'.$this->mask($upload).'"');
			$this->exists = true;
		}


		return $this;
	}

	public function hasFile($file) {
		$return = $this->ssh->exec('tar t'.($this->isCompressed() ? 'z' : '').'f "'.$this->mask($this->filename).'"');

		return strpos($return, $file) !== false;
	}

	public function isCompressed() {
		return substr($this->filename, -2, 2) == 'gz';
	}

	private function mask($str) {
		return addcslashes($str, '";&');
	}

	public function compress() {
		$this->ssh->exec('gzip "'.$this->mask($this->filename).'"');
		$this->filename .= '.gz';
	}
}
