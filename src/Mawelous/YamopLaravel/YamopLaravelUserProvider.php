<?php

namespace Mawelous\YamopLaravel;

use Illuminate\Hashing\HasherInterface;
use Illuminate\Auth\UserProviderInterface;
use Illuminate\Auth\UserInterface;

/**
 * User provider.
 * Allow to use YamopLaravel for authentication.
 * 
 * @author Kamil Zieliński <kamilz@mawelous.com>
 *
 */
class YamopLaravelUserProvider implements UserProviderInterface {

	/**
	 * The hasher implementation.
	 *
	 * @var \Illuminate\Hashing\HasherInterface
	 */
	protected $hasher;

	/**
	 * The Yamop user model.
	 *
	 * @var string
	 */
	protected $model;

	/**
	 * Create a new database user provider.
	 *
	 * @param  \Illuminate\Hashing\HasherInterface  $hasher
	 * @param  string  $model
	 * @return void
	 */
	public function __construct(HasherInterface $hasher, $model)
	{
		$this->model = $model;
		$this->hasher = $hasher;
	}

	/**
	 * Retrieve a user by their unique identifier.
	 *
	 * @param  mixed  $identifier
	 * @return \Illuminate\Auth\UserInterface|null
	 */
	public function retrieveByID( $identifier )
	{
		return $this->_getMapper()->findById( $identifier );
	}

	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param  array  $credentials
	 * @return \Illuminate\Auth\UserInterface|null
	 */
	public function retrieveByCredentials( array $credentials )
	{
		// First we will add each credential element to the query.
		// Then we can execute the query and, if we found a user, return it in a
		// "model" that will be utilized by the Guard instances.
		$query = array();

		foreach ($credentials as $key => $value)
		{
			if ( ! str_contains($key, 'password') ){
				$query[ $key ] = $value;
			}
		}
		
		return $this->_getMapper()->findOne( $query );
	}

	/**
	 * Validate a user against the given credentials.
	 *
	 * @param  \Illuminate\Auth\UserInterface  $user
	 * @param  array  $credentials
	 * @return bool
	 */
	public function validateCredentials( UserInterface $user, array $credentials )
	{
		$plain = $credentials['password'];

		return $this->hasher->check($plain, $user->getAuthPassword());
	}

	/**
	 * Create a new instance of the mapper for model.
	 *
	 * @return \Mawelous\YamopLaravel\Mapper
	 */
	protected function _getMapper()
	{
		$class = '\\'.ltrim($this->model, '\\');
		
		return new Mapper( $class );
	}

}
