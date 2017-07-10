<?php

namespace Zoomyboy\PhpSsh;

use Illuminate\Database\Eloquent\Model;

class AuthMethod extends Model
{
	public $timestamps = false;
	public $fillable = ['title'];
	public $table = 'ssh_auth_methods';
}
