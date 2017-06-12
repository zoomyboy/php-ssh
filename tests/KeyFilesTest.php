<?php

namespace Zoomyboy\PhpSsh\Tests;

use Zoomyboy\PhpSsh\Ssh;

class KeyFilesTest extends TestCase {
	public function setUp() {
		parent::setUp();
	}

	/** @test */
	public function it_reads_the_keyfiles_dirs_and_returns_private_key_file() {
		$this->assertContains(
			env('PHPSSH_KEYFILE'),
			Ssh::getPrivateKeyFiles(env('PHPSSH_KEYDIR'))
		);
	}

	/** @test */
	public function it_reads_the_keyfiles_dirs_and_returns_public_key_file() {
		$this->assertContains(
			env('PHPSSH_KEYFILE').'.pub',
			Ssh::getPublicKeyFiles(env('PHPSSH_KEYDIR'))
		);
	}
}
