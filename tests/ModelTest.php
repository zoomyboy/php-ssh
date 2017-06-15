<?php

namespace Zoomyboy\PhpSsh\Tests;

class ModelTest extends TestCase {
	public function setUp() {
		parent::setUp();
	}

	/** @test */
	public function it_creates_ssh_connection_on_a_model() {
		$model = new Model();
		$model->host = $this->keyfileHost;
		$model->user = $this->keyfileUser;
		$model->authMethod = 'keyFile';
		$model->auth = $this->keyfile;
		$this->assertTrue($model->ssh->check());
	}
}

