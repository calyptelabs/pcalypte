<?php

namespace calypte;

use calypte\CalypteStore;
use Illuminate\Session\CacheBasedSessionHandler;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use calypte\pcalypte\CalypteConnection;
use Illuminate\Support\Facades\Cache;

/**
 * Provedor dos serviços do Calypte.
 * @author Ribeiro
 *
 */
class CalypteServiceProvider extends ServiceProvider{

	public function boot(){

		//Registra o serviço de cache do calypte
		Cache::extend('calypte', function ($app) {
				
			$config = $app['config']['cache']['stores']['calypte'];
			$host   = $config['host'];
			$port   = $config['port'];
				
			$calypteCon = new CalypteConnection($host, $port);

			return Cache::repository(new CalypteStore($calypteCon));
		});

			//Registra o serviço de sessão do calypte
			Session::extend('calypte', function ($app) {
				return new CacheBasedSessionHandler(
						clone $this->app['cache']->store('calypte'),
						$this->app['config']['session.lifetime']
						);
			});
					
	}

	public function register(){
	}

}