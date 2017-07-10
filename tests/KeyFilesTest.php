<?php

namespace Zoomyboy\PhpSsh\Tests;

use Zoomyboy\PhpSsh\Client;

class KeyFilesTest extends TestCase {
	public function setUp() {
		parent::setUp();
	}

	/** @test */
	public function it_reads_the_keyfiles_dirs_and_returns_private_key_file() {
		$this->assertContains(
			env('PHPSSH_KEYFILE'),
			Client::getPrivateKeyFiles(env('PHPSSH_KEYDIR'))
		);
	}

	/** @test */
	public function it_reads_the_keyfiles_dirs_and_returns_public_key_file() {
		$this->assertContains(
			env('PHPSSH_KEYFILE').'.pub',
			Client::getPublicKeyFiles(env('PHPSSH_KEYDIR'))
		);
	}
}
