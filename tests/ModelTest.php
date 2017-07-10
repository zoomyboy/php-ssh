<?php

namespace Zoomyboy\PhpSsh\Tests;

use DB;

class ModelTest extends TestCase {
	public function setUp() {
		parent::setUp();
	}

	/** @test */
	public function it_creates_a_model() {
		$model = Model::create(['title' => 'test']);
		$model->save();
		$model = Model::find($model->id);
		$this->assertNull($model->sshModel);
		$this->assertEquals('test', $model->title);
	}

	/** @test */
	public function it_has_the_correct_auth_method_on_the_connection() {
		$connection = $this->getKeyFileModel();
		$this->assertEquals('withKeyFile', $connection->getAuthMethod());
	}

	/** @test */
	public function it_creates_ssh_connection_on_a_model() {
		$connection = $this->getKeyFileModel();
		$model = Model::create(['title' => 'test']);
		$connection->model()->associate($model);
		$connection->save();

		$query = DB::select('SELECT * FROM ssh_connections WHERE id=?', [$connection->id]);
		$this->assertEquals('Zoomyboy\PhpSsh\Tests\Model', $query[0]->model_type);
		$this->assertEquals($model->id, $query[0]->model_id);

		$connection->load('model');
		$model = Model::with('ssh')->where('title', 'test')->first();
		$this->assertNotNull($model);
		$this->assertTrue($model->ssh->check());
	}

	/** @test */
	public function it_stores_the_auth_method_and_credentials_on_the_client() {
		$connection = $this->getKeyFileModel();

		$this->assertTrue($connection->check());
		$client = $connection->connect();
		$this->assertEquals('KeyFile', $client->authMethod);
		$this->assertEquals($this->keyfile, $client->keyfile);
		$this->assertInstanceOf('phpseclib\Crypt\RSA', $client->authValue);
		
	}
}

