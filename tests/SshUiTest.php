<?php

namespace Zoomyboy\PhpSsh\Tests;

use Zoomyboy\PhpSsh\SshConnection;
use Zoomyboy\PhpSsh\Mysql;

class SshUiTest extends TestCase {

	public function setUp() {
		parent::setUp();

		$this->ssh = $this->getKeyFileConnection();
	}

	/** @test */
	public function it_checks_dir_access() {
		$this->assertTrue($this->ssh->dirAccess('.'));
		$this->assertFalse($this->ssh->dirAccess('/root'));
		$this->assertFalse($this->ssh->dirAccess('/thisdiesntexists'));
	}

	/** @test */
	public function it_creates_a_dir_and_removes_it() {
		$this->ssh->mkdir(env('PHPSSH_TESTDIR'));
		$this->assertTrue($this->ssh->dirExists(env('PHPSSH_TESTDIR')));

		$this->ssh->rm(env('PHPSSH_TESTDIR'));
		$this->assertFalse($this->ssh->dirExists(env('PHPSSH_TESTDIR')));
	}

	/** @test */
	public function it_gets_absolute_path_of_a_dir() {
		$this->ssh->mkdir(env('PHPSSH_TESTDIR'));
		$this->assertEquals(env('PHPSSH_TESTDIR_ABS'), $this->ssh->absolutePath(env('PHPSSH_TESTDIR')));
		$this->ssh->rm(env('PHPSSH_TESTDIR'));
	}

	/** @test */
	public function it_checks_mysql_connection() {
		$this->assertTrue($this->ssh->authMysql(
			'localhost', env('PHPSSH_MYSQL_USER'), env('PHPSSH_MYSQL_PASSWORD')
		)->check(env('PHPSSH_MYSQL_DATABASE')));
		$this->assertFalse($this->ssh->authMysql(
			'localhost', env('PHPSSH_MYSQL_USER'), env('PHPSSH_MYSQL_PASSWORD').'aa'
		)->check(env('PHPSSH_MYSQL_DATABASE')));

		$this->assertFalse($this->ssh->isFile(Mysql::CREDENTIALS_FILE));
	}

	/** @test */
	public function it_dumps_a_mysql_database_to_output() {
		$dump = $this->ssh->authMysql('localhost', env('PHPSSH_MYSQL_USER'), env('PHPSSH_MYSQL_PASSWORD'))
			->dump(env('PHPSSH_MYSQL_DATABASE'));
		$this->assertTrue($this->ssh->isFile(Mysql::CREDENTIALS_FILE));
		$output = $dump->output();

		$this->assertContains(env('PHPSSH_MYSQL_CONTAINS'), $output);

		$this->assertFalse($this->ssh->isFile(Mysql::CREDENTIALS_FILE));
	}

	/** @test */
	public function it_dumps_a_mysql_database_to_a_file() {
		$output = $this->ssh->authMysql('localhost', env('PHPSSH_MYSQL_USER'), env('PHPSSH_MYSQL_PASSWORD'))
			->dump(env('PHPSSH_MYSQL_DATABASE'))
			->toFile(env('PHPSSH_MYSQL_FILE'));

		$this->assertTrue($this->ssh->isFile(env('PHPSSH_MYSQL_FILE')));
		$this->assertContains(env('PHPSSH_MYSQL_CONTAINS'), $this->ssh->cat(env('PHPSSH_MYSQL_FILE')));

		$this->ssh->rm(env('PHPSSH_MYSQL_FILE'));
		$this->assertFalse($this->ssh->isFile(env('PHPSSH_MYSQL_FILE')));

		$this->assertFalse($this->ssh->isFile(Mysql::CREDENTIALS_FILE));
	}

	/** @test */
	public function it_uploads_a_folder() {
		$this->assertTrue($this->ssh->upload(__DIR__.'/uploadData'));
		$this->assertTrue($this->ssh->isDir('uploadData'));
		$this->assertTrue($this->ssh->isFile('uploadData/upload.txt'));
		$this->assertTrue($this->ssh->isDir('uploadData/subDir'));
		$this->assertTrue($this->ssh->isFile('uploadData/subDir/upload2.txt'));
		$this->assertEquals('test1', $this->ssh->cat('uploadData/upload.txt'));
		$this->assertEquals('test2', $this->ssh->cat('uploadData/subDir/upload2.txt'));
		$this->ssh->rm('uploadData');
		$this->assertFalse($this->ssh->isDir('uploadData'));
	}

	/** @test */
	public function it_uploads_a_folder_with_slashes() {
		$this->assertTrue($this->ssh->upload(__DIR__.'/uploadData/'));
		$this->assertTrue($this->ssh->isDir('uploadData'));
		$this->assertTrue($this->ssh->isFile('uploadData/upload.txt'));
		$this->assertTrue($this->ssh->isDir('uploadData/subDir'));
		$this->assertTrue($this->ssh->isFile('uploadData/subDir/upload2.txt'));
		$this->assertEquals('test1', $this->ssh->cat('uploadData/upload.txt'));
		$this->assertEquals('test2', $this->ssh->cat('uploadData/subDir/upload2.txt'));
		$this->ssh->rm('uploadData');
		$this->assertFalse($this->ssh->isDir('uploadData'));
	}

	/** @test */
	public function it_uploads_a_folder_to_another_location() {
		$this->assertTrue($this->ssh->upload(__DIR__.'/uploadData', 'uploadDatanew'));
		$this->assertTrue($this->ssh->isDir('uploadDatanew'));
		$this->assertTrue($this->ssh->isFile('uploadDatanew/upload.txt'));
		$this->assertTrue($this->ssh->isDir('uploadDatanew/subDir'));
		$this->assertTrue($this->ssh->isFile('uploadDatanew/subDir/upload2.txt'));
		$this->assertEquals('test1', $this->ssh->cat('uploadDatanew/upload.txt'));
		$this->assertEquals('test2', $this->ssh->cat('uploadDatanew/subDir/upload2.txt'));
		$this->ssh->rm('uploadDatanew');
		$this->assertFalse($this->ssh->isDir('uploadDatanew'));
	}

	/** @test */
	public function it_uploads_a_file() {
		$this->assertTrue($this->ssh->upload(__DIR__.'/uploadData/upload.txt'));
		$this->assertTrue($this->ssh->isFile('upload.txt'));
		$this->assertEquals('test1', $this->ssh->cat('upload.txt'));
		$this->ssh->rm('upload.txt');
		$this->assertFalse($this->ssh->isFile('upload.txt'));
	}

	/** @test */
	public function it_uploads_a_file_to_another_location() {
		$this->assertTrue($this->ssh->upload(__DIR__.'/uploadData/upload.txt', 'uploadfile.txt'));
		$this->assertTrue($this->ssh->isFile('uploadfile.txt'));
		$this->assertEquals('test1', $this->ssh->cat('uploadfile.txt'));
		$this->ssh->rm('uploadfile.txt');
		$this->assertFalse($this->ssh->isFile('uploadfile.txt'));
	}

	/**
	 * @test
	 * @expectedException \InvalidArgumentException
	 */
	public function it_throws_error_if_path_is_relative() {
		$this->ssh->upload('uploadData/upload.txt', 'uploadfile.txt');
	}

	/** @test */
	public function it_removes_an_uploaded_folder() {
		$this->ssh->upload(__DIR__.'/uploadData');
		$this->assertTrue($this->ssh->exists('uploadData'));
		$this->ssh->rm('uploadData');
		$this->assertFalse($this->ssh->exists('uploadData'));
	}

	/** @test */
	public function it_creates_a_tar_archive_of_a_folder() {
		$this->ssh->upload(__DIR__.'/uploadData');
		$archive = $this->ssh->archive('uploadData.tar')->add('uploadData');
		$this->assertTrue($this->ssh->isArchive('uploadData.tar'));
		$this->assertTrue($archive->hasFile('subDir/upload2.txt'));
		$this->assertFalse($archive->hasFile('uploadData.tar'));

		$this->ssh->rm('uploadData.tar');
		$this->assertFalse($this->ssh->exists('uploadData.tar'));

		$this->ssh->rm('uploadData');
		$this->assertFalse($this->ssh->exists('uploadData'));
	}

	/** @test */
	public function it_adds_another_file_to_an_archive() {
		$this->ssh->upload(__DIR__.'/uploadData');
		$this->ssh->upload(__DIR__.'/uploadData3.txt');
		$archive = $this->ssh->archive('uploadData.tar')->add('uploadData')->add('uploadData3.txt');
		$this->assertTrue($this->ssh->isArchive('uploadData.tar'));
		$this->assertTrue($archive->hasFile('subDir/upload2.txt'));
		$this->assertTrue($archive->hasFile('uploadData3.txt'));
		$this->assertFalse($archive->hasFile('uploadData.tar'));

		$this->ssh->rm('uploadData.tar');
		$this->assertFalse($this->ssh->exists('uploadData.tar'));

		$this->ssh->rm('uploadData');
		$this->assertFalse($this->ssh->exists('uploadData'));

		$this->ssh->rm('upload3.txt');
		$this->assertFalse($this->ssh->exists('upload3.txt'));
	}
	

	/**
	 *  @test
	 *
	 *  @expectedException \Zoomyboy\PhpSsh\Exceptions\FileDoesntExistsRemote
	 */
	public function it_throws_exception_if_file_on_remote_doesnt_exists_for_download() {
		$this->ssh->download('notexists.txt', __DIR__);
	}

	/** @test */
	public function it_compresses_an_archive() {
		$this->ssh->upload(__DIR__.'/uploadData3.txt');

		$archive = $this->ssh->archive('uploadData.tar')->add('uploadData3.txt');
		$this->assertTrue($this->ssh->isArchive('uploadData.tar'));
		$archive->compress();
		$this->assertTrue($this->ssh->isCompressedArchive('uploadData.tar.gz'));

		$this->assertTrue($archive->hasFile('uploadData3.txt'));
		$this->assertFalse($archive->hasFile('uploadData.tar.gz'));
		$this->assertFalse($archive->hasFile('uploadData.tar'));

		$this->ssh->rm('uploadData.tar.gz');
		$this->assertFalse($this->ssh->exists('uploadData.tar.gz'));

		$this->ssh->rm('uploadData.tar');
		$this->assertFalse($this->ssh->exists('uploadData.tar'));

		$this->ssh->rm('uploadData3.txt');
		$this->assertFalse($this->ssh->exists('uploadData3.txt'));
	}

	/**
	 * @test
	 *  @expectedException \Zoomyboy\PhpSsh\Exceptions\FileDoesntExistsRemote
	 */
	public function it_throws_exception_on_download_a_file_that_doesnt_exists_remote() {
		$this->ssh->download('uploadData3doesntexists.txt', __DIR__.'/downloads');
	}

	/** @test */
	public function it_downloads_a_file_to_a_local_dir() {
		$this->ssh->upload(__DIR__.'/uploadData3.txt');
		$this->ssh->download('uploadData3.txt', __DIR__.'/Downloads');
		$this->assertFileExists(__DIR__.'/Downloads/uploadData3.txt');
		unlink(__DIR__.'/Downloads/uploadData3.txt');
	}

	/** @test */
	public function it_downloads_a_file_to_a_local_dir_with_custom_filename() {
		$this->ssh->upload(__DIR__.'/uploadData3.txt');
		$this->ssh->download('uploadData3.txt', __DIR__.'/Downloads/uploadnew.txt');
		$this->assertFileExists(__DIR__.'/Downloads/uploadnew.txt');
		unlink(__DIR__.'/Downloads/uploadnew.txt');
	}
}
