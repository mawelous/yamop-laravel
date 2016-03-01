<?php

namespace Mawelous\YamopLaravel;

/**
 * Get data from database.
 * Implements _createPaginator of parent.
 * Sets connection using configuration.
 * 
 * @author Kamil Zieliński <kamilz@mawelous.com>
 *
 */
class Mapper extends \Mawelous\Yamop\Mapper
{
	/**
	 * Sets database
	 * 
	 * @param string $modelClass
	 * @param int $fetchType One of constants
	 */
	public function __construct( $modelClass = null, $fetchType = null )
	{
		if( static::$_database == null ){
			static::$_database = $this->_getDatabases();
		}
	
		parent::__construct( $modelClass, $fetchType );
	}	
	
	/**
	 * (non-PHPdoc)
	 * @see \Mawelous\Yamop\Mapper::_createPaginator()
	 */
	protected function _createPaginator($results, $totalCount, $perPage, $page, $options)
	{
		if( $options ){
			\Paginator::setPageName( $options );
			$paginator =  \Paginator::make( $results, $totalCount, $perPage );
			\Paginator::setPageName( 'page' );
		} else {
			$paginator =  \Paginator::make( $results, $totalCount, $perPage );
		}
		
		return $paginator;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Mawelous\Yamop\Mapper::getPaginator()
	 */
	public function getPaginator( $perPage = 10, $page = null, $options = null )
	{
		if( $page == null ){
			$pageParamName = $options ?: 'page';  
			$page = (int)\Input::get( $pageParamName, 1 );
		}
	
		return parent::getPaginator( $perPage, $page, $options );
	}
	
	/**
	 * Connect to databses given in config
	 * @return array Array od MongoDB
	 */
	protected function _getDatabases()
	{
		$baseConfigPath = 'database.mongo';
		$config = \Config::get( $baseConfigPath );
		
		if( \Config::get( $baseConfigPath . '.database') ){
			$options = \Config::get( $baseConfigPath . '.options', array() );
			$connection = new \MongoClient( $this->_getServerString( $baseConfigPath ), $options );
			$db = \Config::get( $baseConfigPath . '.database' );
			return array ( 'default' => $connection->$db );
		} else {
			$connections = array();
			foreach( $config as $key => $configPart ){
				$configPath = $baseConfigPath . '.' . $key;
				$options = \Config::get( $configPath . '.options', array() );
				$connection = new \MongoClient( $this->_getServerString( $configPath ), $options );
				$db = \Config::get( $configPath . '.database' );
				$connections[ $key ] = $connection->$db;
			}
			return $connections;
		}
	}
	
	/**
	 * Return server connection string build from config
	 * 
	 * @param string $path Config path to one database settings
	 * @return string
	 */
	protected function _getServerString( $path )
	{
		$databaseConfig = \Config::get( $path . '.database' );
		if( empty( $databaseConfig ) ){
			throw new \Exception( 'Please set some database in config' );
		}

		$server = 'mongodb://';

		$userConfig = \Config::get( $path . '.user' );
		if ( !empty( $userConfig ) ){
			$server .= $userConfig . ':' . \Config::get( $path . '.password' ) .'@';
		}
		$hosts = \Config::get( $path . '.host', '127.0.0.1' );
		$port = \Config::get( $path . '.port', 27017 );

		if (!is_array($hosts)) {
			$hosts = [$hosts];
		}

		// Add ports to hosts
		foreach ($hosts as &$host)
		{
			$host = $host . ":" . $port;
		}

		$server .= implode(',', $hosts)
				. '/' . $databaseConfig;

		return $server;
	}	
}