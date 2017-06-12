<?php

namespace Zoomyboy\PhpSsh\Tests;

use Zoomyboy\PhpSsh\Ssh;
use Zoomyboy\PhpSsh\Mysql;

class PrintfMaskTest extends TestCase {
	public function setUp() {
		parent::setUp();

		$this->ssh = Ssh::auth($this->keyfileHost, $this->keyfileUser)->withKeyFile($this->keyfile)->connect();
	}
	
	/** @test */
	public function it_masks_double_quotes_with_no_delimiter() {
		$result = $this->ssh->exec('printf \''.Mysql::forPrintf('"').'\' > cred.cnf && cat cred.cnf');
		$this->assertEquals('"', $result);
	}

	/** @test */
	public function it_masks_other_special_chars_with_no_delimiter() {
		$result = $this->ssh->exec('printf \''.Mysql::forPrintf('!§$=&()').'\' > cred.cnf && cat cred.cnf');
		$this->assertEquals('!§$=&()', $result);
	}

	/** @test */
	public function it_masks_percent_with_no_delimiter() {
		$result = $this->ssh->exec('printf \''.Mysql::forPrintf('%').'\' > cred.cnf && cat cred.cnf');
		$this->assertEquals('%', $result);
	}

	/** @test */
	public function it_maks_slash_with_no_delimiter() {
		$result = $this->ssh->exec('printf \''.Mysql::forPrintf('/').'\' > cred.cnf && cat cred.cnf');
		$this->assertEquals('/', $result);
	}

	/** @test */
	public function it_masks_alt_gr_chars_with_no_delimiter() {
		$result = $this->ssh->exec('printf \''.Mysql::forPrintf('¹²³¼½¬{[]}').'\' > cred.cnf && cat cred.cnf');
		$this->assertEquals('¹²³¼½¬{[]}', $result);
	}

	/** @test */
	public function it_masks_plus_and_numbersign_with_no_delimiter() {
		$result = $this->ssh->exec('printf \''.Mysql::forPrintf('`´¸~+*’\#').'\' > cred.cnf && cat cred.cnf');
		$this->assertEquals('`´¸~+*’\#', $result);
	}

	/** @test */
	public function it_masks_delimiter_single_quote() {
		$result = $this->ssh->exec('printf \''
			.Mysql::forPrintf('\'', '\'')
			.'\' > cred.cnf && cat cred.cnf');
		$this->assertEquals('\'', $result);
	}

	/** @test */
	public function it_masks_delimiter_single_quote_inside_double_quote() {
		$result = $this->ssh->exec('printf \'pw="'
			.Mysql::maskPrintf('a\'b"c', '\'', '"')
			.'"\' > cred.cnf && cat cred.cnf');
		$this->assertEquals('pw="a\'b\"c"', $result);
	}

}
