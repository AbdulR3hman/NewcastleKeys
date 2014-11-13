<?php
/**
 * A class to handle the /login and /logout action. There is no constructor.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2013 Newcastle University
 *
 */
    class Userlogin extends Siteaction
    {
/**
 * Handle a logout
 *
 * Clear all the session material if any and then divert to the /login page
 *
 * Code taken directly from the PHP session_destroy manual page
 *
 * @link	http://php.net/manual/en/function.session-destroy.php
 *
 * @param object	$context	The context object for the site
 */
	public function logout($context, $local)
	{
	    $_SESSION = array(); # Unset all the session variables.

	    # If it's desired to kill the session, also delete the session cookie.
	    # Note: This will destroy the session, and not just the session data!
	    if (ini_get('session.use_cookies'))
	    {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
		    $params["path"], $params["domain"],
		    $params["secure"], $params["httponly"]
		);
	    }
	    if (session_status() == PHP_SESSION_ACTIVE)
	    { # no session started yet
	        session_destroy(); # Finally, destroy the -session.
            }
	    $context->divert('/');
	}
/**
 * Handle a login
 *
 * @param object	$context	The context object for the site
 * @param object	$local		The local object for the site
 *
 * @return string	A template name
 */
	public function login($context, $local)
	{
	    if ($context->hasuser())
	    { # already logged in
		$local->addval('logerr', 'Please log out before trying to login');
	    }
	    else
	    {
		if (($lg = $context->postpar('email', '')) != '')
		{
		    $pw = $context->postpar('password', '');
		    if ($pw != '') 
		    {
			$user = R::findOne('users',
			    (filter_var($lg, FILTER_VALIDATE_EMAIL) !== FALSE ? 'email' : 'login').'=?', array($lg));
		
			if (is_object($user) && password_verify($pw, $user->pasw))
			{
			    if (session_status() != PHP_SESSION_ACTIVE)
			    { # no session started yet
				session_start();
			    }
			    $_SESSION['user'] = $user;
			    $context->divert('/keys'); # success - divert to home page
			}
		    }
		}
	    }
	    return 'index.twig';
	}
/**
 * Handle a /login or /logout call
 *
 * @param object	$context	The context object for the site
 * @param object	$local		The local object for the site
 *
 * @return string	A template name
 */
	public function handle($context, $local)
	{
	    $action = $context->action();
	    return $this->$action($context, $local);
	}
    }
?>
