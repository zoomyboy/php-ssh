<?php

namespace Zoomyboy\PhpSsh\Tests;

use Zoomyboy\PhpSsh\Connection;
use Zoomyboy\PhpSsh\AuthMethod;

class TestCase extends \Orchestra\Testbench\TestCase {
	protected $keyfileUser;
	protected $keyfileHost;
	protected $passwordUser;
	protected $passwordHost;
	protected $password;
	protected $keyfile;
	protected $keydir;
	protected $ssh;

	/**
	 * Gets a Connection model
	 *
	 * @return Connection
	 */
	public function getKeyFileModel() {
		$connection = new Connection([
			'host' => $this->keyfileHost,
			'user' => $this->keyfileUser,
			'auth' => $this->keyfile
		]);
		$connection->authMethod()->associate(AuthMethod::where('title', 'KeyFile')->first());
		$connection->save();
		$connection = Connection::find($connection->id);

		$this->assertNotNull($connection);

		return $connection;
	}

	public function getKeyFileConnection() {
		$connection = new Connection([
			'host' => $this->keyfileHost,
			'user' => $this->keyfileUser,
			'auth' => $this->keyfile
		]);
		$connection->authMethod()->associate(AuthMethod::where('title', 'KeyFile')->first());
		$connection->save();
		$connection = Connection::find($connection->id);

		return $connection->connect();
	}

	public function setUp() {
		parent::setUp();

		$this->artisan('migrate', ['--database' => 'testbench']);

		$env = __DIR__.'/../../../../';
		if (file_exists($env)) {
			(new \Dotenv\Dotenv($env, '.env.test'))->load();
		}

		$this->keyfileUser = env('PHPSSH_KEYFILE_USER');
		$this->keyfileHost = env('PHPSSH_KEYFILE_HOST');
		$this->passwordUser = env('PHPSSH_PASSWORD_USER');
		$this->passwordHost = env('PHPSSH_PASSWORD_HOST');
		$this->password = env('PHPSSH_PASSWORD');
		$this->keydir = env('PHPSSH_KEYDIR');
		$this->keyfile = env('PHPSSH_KEYFILE');

	}

	/**
	 * Define environment setup.
	 *
	 * @param  \Illuminate\Foundation\Application  $app
	 * @return void
	 */
	protected function getEnvironmentSetUp($app) {
		// Setup default database to use sqlite :memory:
		$app['config']->set('database.default', 'testbench');
		$app['config']->set('database.connections.testbench', [
			'driver'   => 'sqlite',
			'database' => ':memory:',
			'prefix'   => '',
		]);
	}

	protected function getPackageProviders($app) {
		return ['Zoomyboy\PhpSsh\ServiceProvider'];
	}
}
