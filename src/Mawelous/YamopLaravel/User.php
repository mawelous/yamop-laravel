<?php
namespace Mawelous\YamopLaravel;

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

/**
 * Implements interfaces need for authentication
 * and password reminder.
 * 
 * @author Kamil Zieliński <kamilz@mawelous.com>
 *
 */
class User extends Model implements UserInterface, RemindableInterface { 
	
	/**
	 * Set hashed password
	 * 
	 * @param string $password
	 */
	public function setPassword( $password )
	{
		$this->password = Hash::make( $password );
	}
	
	/**
	 * Get the unique identifier for the user.
	 * UserInterface implementation.
	 *
	 * @return MongoId
	 */
	public function getAuthIdentifier()
	{
		return $this->_id;
	}
	
	/**
	 * Get the password for the user.
	 * UserInterface implementation.
	 *
	 * @return string
	 */
	public function getAuthPassword()
	{
		return $this->password;
	}
	
	/**
	 * Get the e-mail address where password reminders are sent.
	 *
	 * @return string
	 */
	public function getReminderEmail()
	{
		return $this->email;
	}	
	
}