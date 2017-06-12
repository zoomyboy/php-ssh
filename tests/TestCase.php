<?php

namespace Zoomyboy\PhpSsh\Tests;

class TestCase extends \Orchestra\Testbench\TestCase {
	protected $keyfileUser;
	protected $keyfileHost;
	protected $passwordUser;
	protected $passwordHost;
	protected $password;
	protected $keyfile;
	protected $keydir;

	public function setUp() {
		parent::setUp();

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

	/** @test */
	public function it_works() {
		$this->assertTrue(true);
	}
}
