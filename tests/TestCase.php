<?php

namespace Zoomyboy\PhpSsh\Tests;

class TestCase extends \Orchestra\Testbench\TestCase {
	public function setUp() {
		parent::setUp();
	}

	protected function getPackageProviders($app) {}

	/**
	 * Define environment setup.
	 *
	 * @param  \Illuminate\Foundation\Application  $app
	 * @return void
	 */
	protected function getEnvironmentSetUp($app) {}

	/** @test */
	public function it_works() {
		$this->assertTrue(true);
	}
}
