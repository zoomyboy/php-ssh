<?php

namespace Zoomyboy\PhpSsh\Tests;

use Zoomyboy\PhpSsh\Ssh;

class EnvTest extends TestCase {
	public function setUp() {
		parent::setUp();
	}
	
	/** @test */
	public function it_has_env_file() {
		$this->assertFileIsReadable(__DIR__.'/../../../../.env.test');
	}
}
