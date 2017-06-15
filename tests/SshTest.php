<?php

namespace Zoomyboy\PhpSsh\Tests;

use Zoomyboy\PhpSsh\SshConnection;

class SshTest extends TestCase {
	public function setUp() {
		parent::setUp();
	}

	/** @test */
	public function it_has_ssh_env() {
		$this->assertInternalType('string', $this->keyfileUser);
		$this->assertInternalType('string', $this->keyfileHost);
		$this->assertInternalType('string', $this->passwordUser);
		$this->assertInternalType('string', $this->passwordHost);
		$this->assertInternalType('string', $this->password);

		$this->assertInternalType('string', $this->keydir);
		$this->assertDirectoryIsReadable($this->keydir);

		$this->assertInternalType('string', $this->keyfile);
		$this->assertFileIsReadable($this->keyfile);
	}

	/** @test */
	public function it_checks_if_an_ssh_connection_succeeds_with_keyfile() {
		$this->assertTrue(SshConnection::auth($this->keyfileHost, $this->keyfileUser)
			->withKeyFile($this->keyfile)->check());
		$this->assertFalse(SshConnection::auth($this->keyfileHost.'a', $this->keyfileUser)
			->withKeyFile($this->keyfile)->check());

		/* Login is alias for auth */
		$this->assertTrue(SshConnection::login($this->keyfileHost, $this->keyfileUser)
			->withKeyFile($this->keyfile)->check());
		$this->assertFalse(SshConnection::login($this->keyfileHost.'a', $this->keyfileUser)
			->withKeyFile($this->keyfile)->check());
	}

	/**  @test */
	public function it_checks_if_an_ssh_connection_succeeds_with_password() {
		$this->assertTrue(SshConnection::auth($this->passwordHost, $this->passwordUser)
			->withPassword($this->password)->check());
		$this->assertFalse(SshConnection::auth($this->passwordHost.'a', $this->passwordUser)
			->withPassword($this->password)->check());

		/* Login is alias for auth */
		$this->assertTrue(SshConnection::login($this->passwordHost, $this->passwordUser)
			->withPassword($this->password)->check());
		$this->assertFalse(SshConnection::login($this->passwordHost.'a', $this->passwordUser)
			->withPassword($this->password)->check());
	}

	/** @test */
	public function it_connects_successfully() {
		$this->assertInstanceOf(SshConnection::class, SshConnection::auth($this->passwordHost, $this->passwordUser)
			->withPassword($this->password)->connect());
	}

	/**
	 * @test
	 * @expectedException Zoomyboy\PhpSsh\Exceptions\ConnectionFailException
	 */
	public function it_throws_exception_on_connection_failure() {
		SshConnection::auth($this->keyfileHost.'a', $this->keyfileUser)->withKeyFile($this->keyfile)->connect();
	}

}
