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
	public function __construct( $modelClass = null, $fetchType = self::FETCH_OBJECT )
	{
		if( static::$_database == null ){
			static::$_database = array( $this->_getDatabase() );
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
	 * Connect to databse given in config
	 */
	protected function _getDatabase()
	{
		$config = \Config::get( 'database.mongo' );
		$options = isset( $config[ 'options' ] ) ?
		           $config[ 'options' ] :
		           array();
		$connection = new \MongoClient( $this->_getServer(), $options );		
		return $connection->{$config[ 'database' ]};
	}
	
	/**
	 * Return server connection string build from config
	 * 
	 * @return string
	 */
	protected function _getServer()
	{
		$databaseConfig = \Config::get( 'database.mongo.database' );
		if( empty( $databaseConfig ) ){
			throw new \Exception( 'Please set some database in config' );
		}
			
		$server = 'mongodb://';
		
		$userConfig = \Config::get( 'database.mongo.user' );
		if ( !empty( $userConfig ) ){
			$server .= $userConfig . ':' . \Config::get( 'database.mongo.password' ) .'@';
		}
		$server .= \Config::get( 'database.mongo.host', '127.0.0.1' )
			    . ':' .\Config::get( 'database.mongo.port', 27017 )
				. '/' . \Config::get( 'database.mongo.database' );
	
		return $server;
	}	
}