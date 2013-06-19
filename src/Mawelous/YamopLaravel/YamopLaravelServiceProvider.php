<?php namespace Mawelous\YamopLaravel;

use Illuminate\Support\ServiceProvider;

/**
 * Service provider for YamopLaravel.
 * Register yamop auth driver.
 * 
 * @author Kamil Zieliński <kamilz@mawelous.com>
 *
 */
class YamopLaravelServiceProvider extends ServiceProvider {
	
	/**
	 * Bootstrap the application events.
	 * Adds YampoLaravelUserProvider
	 *
	 * @return void
	 */
	public function boot() {
		
		$this->app['auth']->extend( 'yamop', function( $app ) {
			$provider = new YamopLaravelUserProvider( $app[ 'hash' ], $app[ 'config' ]->get( 'auth.model' ) );
			return new \Illuminate\Auth\Guard( $provider, $app[ 'session' ] );
		});		
	}	

	/**
	 * Register the service provider.
	 * Doesn't need to do anything.
	 * We want Mapper to start connection only when it's called
	 *
	 * @return void
	 */
	public function register()
	{
	}


}