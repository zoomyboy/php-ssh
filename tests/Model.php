<?php

namespace Zoomyboy\PhpSsh\Tests;

use Zoomyboy\PhpSsh\Ssh;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel {
	use Ssh;

	public $fillable = ['title'];
}
