<?php

namespace Zoomyboy\PhpSsh\Tests\Ui;

use Zoomyboy\PhpSsh\Client;
use Zoomyboy\PhpSsh\Mysql;
use Zoomyboy\PhpSsh\SshConnection;
use Zoomyboy\PhpSsh\Tests\TestCase;
use \Mockery as M;
use phpseclib\Net\SSH2;

class MkdirTest extends TestCase {

	public function setUp() {
		parent::setUp();
	}

    /** @test */
    public function it_creates_a_dir_that_doesnt_exist_yet() {
        $backend = M::mock(SSH2::class);

        $backend->shouldReceive('exec')->with('mkdir -p \'/home/user/new\' > /dev/null 2>&1; echo $?')->once()->andReturn('0\n');
        $backend->shouldReceive('exec')->with('mkdir -p \'/home/user2\' > /dev/null 2>&1; echo $?')->once()->andReturn('0 ');

        $ssh = Client::fromBackend($backend);

        $this->assertTrue($ssh->mkdir('/home/user/new'));
        $this->assertTrue($ssh->mkdir('/home/user2'));
    }

    /** @test */
    public function it_cannot_create_a_dir_when_return_code_is_false() {
        $backend = M::mock(SSH2::class);

        $backend->shouldReceive('exec')->with('mkdir -p \'/home/user/new\' > /dev/null 2>&1; echo $?')->once()->andReturn('1\n');

        $ssh = Client::fromBackend($backend);

        $this->assertFalse($ssh->mkdir('/home/user/new'));
    }

    /** @test */
    public function it_can_create_a_dir_that_has_single_quotes() {
        $backend = M::mock(SSH2::class);

        $backend->shouldReceive('exec')->with("mkdir -p '/home/O'\''new' > /dev/null 2>&1; echo $?")->once();

        $ssh = Client::fromBackend($backend);

        $ssh->mkdir("/home/O'new");
    }

    /** @test */
    public function it_can_create_a_home_dir() {
        $backend = M::mock(SSH2::class);

        $backend->shouldReceive('exec')->with("mkdir -p ~/'T' > /dev/null 2>&1; echo $?")->once();

        $ssh = Client::fromBackend($backend);

        $ssh->mkdir("~/T");
    }
}
