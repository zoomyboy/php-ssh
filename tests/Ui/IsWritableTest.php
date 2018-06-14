<?php

namespace Zoomyboy\PhpSsh\Tests\Ui;

use Zoomyboy\PhpSsh\Client;
use Zoomyboy\PhpSsh\Mysql;
use Zoomyboy\PhpSsh\SshConnection;
use Zoomyboy\PhpSsh\Tests\TestCase;
use \Mockery as M;
use phpseclib\Net\SSH2;

class IsWritableTest extends TestCase {

	public function setUp() {
		parent::setUp();
	}

    /** @test */
    public function it_checks_dir_access() {
        $backend = M::mock(SSH2::class);

        $backend->shouldReceive('exec')->with("[ -w '/home/user/new' ]; echo $?")->once()->andReturn('0\n');

        $ssh = Client::fromBackend($backend);

        $this->assertTrue($ssh->isWritable('/home/user/new'));
    }

    /** @test */
    public function it_returns_false_when_dir_isnt_writable() {
        $backend = M::mock(SSH2::class);

        $backend->shouldReceive('exec')->with("[ -w '/home/user/new' ]; echo $?")->once()->andReturn('1\n');

        $ssh = Client::fromBackend($backend);

        $this->assertFalse($ssh->isWritable('/home/user/new'));
    }

    /** @test */
    public function it_can_check_a_dir_with_single_quotes() {
        $backend = M::mock(SSH2::class);

        $backend->shouldReceive('exec')->with("[ -w '/home/O'\''new' ]; echo $?")->once();

        $ssh = Client::fromBackend($backend);

        $ssh->isWritable("/home/O'new");
    }

    /** @test */
    public function it_checks_for_a_home_dir() {
        $backend = M::mock(SSH2::class);

        $backend->shouldReceive('exec')->with("[ -w ~/'T' ]; echo $?")->once();

        $ssh = Client::fromBackend($backend);

        $ssh->isWritable("~/T");
    }
}
