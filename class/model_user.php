<?php
/**
 * A model class for the RedBean object User
 *  *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2013 Newcastle University
 *
 */
    class Model_Users extends RedBean_SimpleModel
    {
/**
 * Is this user an admin?
 *	@NOTE the model is not working, I used an alternative, more direct way....
 * @return boolean
 */
	public function isadmin()
	{
	   return $this->bean->accesslevel ;//== '0' ? true : false);
	}

/**
 * Set the user's password
 *
 * @param string	$pw	The password
 */
	public function setpw($pw)
	{
	    $this->bean->password = password_hash($pw, PASSWORD_BCRYPT);
	    R::store($this->bean);
	}
/**
 * Check a password
 *
 * @param string	$pw The password
 *
 * @return boolean
 */
	public function pwok($pw)
	{
	    return password_verify($pw, $this->bean->pasw);
	}
    }
?>
