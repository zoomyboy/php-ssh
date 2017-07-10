<?php
namespace Zoomyboy\PhpSsh;
require(__DIR__.'/../helpers/functions.php');

use Illuminate\Database\Eloquent\Model;

class Connection extends Model {

	protected $table = 'ssh_connections';

	public $fillable = ['host', 'user', 'auth'];

	//---------------------------------- Relations ----------------------------------
	//*******************************************************************************
	public function model() {
		return $this->morphTo();
	}

	public function authMethod() {
		return $this->belongsTo(AuthMethod::class);
	}

	//--------------------------------- Boilerplate ---------------------------------
	public function check() {
		$ssh = Client::auth($this->host, $this->user);
		$ssh = $ssh->{$this->getAuthMethod()}($this->auth);

		return $ssh->check();
	}

	public function connect() {
		$ssh = Client::auth($this->host, $this->user);
		$ssh = $ssh->{$this->getAuthMethod()}($this->auth);

		return $ssh->connect();
	}

	public function getAuthMethod() {
		return 'with'.ucfirst($this->authMethod->title);
	}
}
