<?php

namespace Zoomyboy\PhpSsh;

trait Ssh {
	public function getSshAttribute() {
		$ssh = SshConnection::auth($this->host, $this->user);

		if ($this->authMethod == 'keyFile') {
			$ssh = $ssh->withKeyFile($this->auth);
		}
		if ($this->authMethod == 'password') {
			$ssh = $ssh->withPassword($this->auth);
		}

		return $ssh;
	}
}
