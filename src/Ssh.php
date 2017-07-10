<?php

namespace Zoomyboy\PhpSsh;

trait Ssh {
	public function ssh() {
		return $this->morphOne(\Zoomyboy\PhpSsh\Connection::class, 'model');
	}
}
