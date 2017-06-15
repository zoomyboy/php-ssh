<?php

namespace Zoomyboy\PhpSsh\Tests;

use Zoomyboy\PhpSsh\Ssh;
use Zoomyboy\PhpSsh\SshConnection;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel {
	use Ssh;

	public $fillable = ['host', 'user', 'authMethod', 'auth'];
}
