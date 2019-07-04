<?php

namespace Zoomyboy\PhpSsh;

class ServiceProvider extends \Illuminate\Support\ServiceProvider {
	public function register() {
		$this->loadMigrationsFrom(__DIR__.'/Migrations');

		if (app()->environment() === 'testing') {
			$this->loadMigrationsFrom(__DIR__.'/../tests/Migrations');
		}
	}

	public function boot() {

	}
}
