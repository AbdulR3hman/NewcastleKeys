<?php
/**
 * A class that stores various useful pieces of data for access throughout the rest of the system.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2013 Newcastle University
 *
 */
    class Context
    {
/**
 * @var object		an instance of Local
 */
	private $local		= NULL;		# singleton local object
/**
 * @var object		NULL or an object decribing the current logged in User
 */
	private $luser		= NULL;		# Current user bean if we have logins....
/**
 * @var integer		Counter used for generating unique ids
 */
	private $idgen = 0;			# used for generating unique ids
/**
 * @var string		The first component of the current URL
 */
	private $reqaction	= 'home';	# the first segment of	the URL
/**
 * @var array		The rest of the current URL exploded at /
 */
	private $reqrest	= array();	# the rest of the URL
/**
 * Check to see if there is a session and return a specific value form it if it exists
 *
 * @param string	$var	The variable name
 * @param boolean	$fail	If TRUE then exit with an error returnif the value  does not exist
 *
 * @return mixed
 */
        public function sessioncheck($var, $fail = TRUE)
        {
            if (isset($_COOKIE[ini_get('session.name')]))
            {
                session_start();
                if (isset($_SESSION[$var]))
                {
                    return $_SESSION[$var];
                }
            }
            if ($fail)
            {
                $this->noaccess();
            }
            return NULL;
        }
/**
 * Generate a Location header for within this site
 *
 * @parm string		$where	The page to divert to
 */
	public function divert($where)
	{
	    $this->relocate($this->local->base().$where);
	}
/**
 * Generate a Location header
 *
 * @parm string		$where	The URL to divert to
 */
	public function relocate($where)
	{
	    header('Location: '.$where);
	    exit;
	}
/**
 * Generate a 400 Bad Request error return
 *
 * @parm string		$msg	A message to be sent
 */
	public function bad($msg = '')
	{
	    header(StatusCodes::httpHeaderFor(400));
	    if ($msg != '')
	    {
		echo '<p>'.$msg.'</p>';
	    }
	    exit;
	}
/**
 * Generate a 403 Access Denied error return
 *
 * @parm string		$msg	A message to be sent
 */
	public function noaccess($msg = '')
	{
	    header(StatusCodes::httpHeaderFor(403));
	    if ($msg != '')
	    {
		echo '<p>'.$msg.'</p>';
	    }
	    exit;
	}
/**
 * Generate a 404 Not Found error return
 *
 * @parm string		$msg	A message to be sent
 */
	public function notfound($msg = '')
	{
	    header(StatusCodes::httpHeaderFor(404));
	    if ($msg != '')
	    {
		echo '<p>'.$msg.'</p>';
		$this->divert('/404');
	    }
	    exit;
	}
/**
 * Generate a 500 Internal Error error return
 *
 * @parm string		$msg	A message to be sent
 */
	public function internal($msg = '')
	{
	    header(StatusCodes::httpHeaderFor(500));
	    if ($msg != '')
	    {
		echo '<p>'.$msg.'</p>';
	    }
	    exit;
	}
/**
 * Load a bean or fail with a 400 error
 *
 * @parm string		$table	A bean type name
 * @parm integer	$id	A bean id
 *
 * @return object
 */
	public function load($bean, $id)
	{
	    $foo = R::load($bean, $id);
	    if ($foo->getID() == 0)
	    {
			return false;
	    }
	    return $foo;
	}
/**
 * Look in the _GET array for a key and return its trimmed value
 *
 * @param string	$name	The key
 * @param boolean	$fail	If TRUE then generate a 400 if the key does not exist in the array
 *
 * @return mixed
 */
	public function mustgetpar($name, $fail = TRUE)
	{
	    if (filter_has_var(INPUT_GET, $name))
	    {
		return trim($_GET[$name]);
	    }
	    if ($fail)
	    {
		$this->bad();
	    }
	    return NULL;
	}
/**
 * Look in the _POST array for a key and return its trimmed value
 *
 * @param string	$name	The key
 * @param boolean	$fail	If TRUE then generate a 400 if the key does not exist in the array
 *
 * @return mixed
 */
	public function mustpostpar($name, $fail = TRUE)
	{
	    if (filter_has_var(INPUT_POST, $name))
	    {
		return trim($_POST[$name]);
	    }
	    if ($fail)
	    {
		$this->bad();
	    }
	    return NULL;
	}
/**
 * Look in the _GET array for a key and return its trimmed value or a default value
 *
 * @param string	$name	The key
 * @param mixed		$dflt	Returned if the key does not exist
 *
 * @return mixed
 */
	public function getpar($name, $dflt)
	{
	    return filter_has_var(INPUT_GET, $name) ? trim($_GET[$name]) : $dflt;
	}
/**
 * Look in the _POST array for a key and return its trimmed value or a default value
 *
 * @param string	$name	The key
 * @param mixed		$dflt	Returned if the key does not exist
 *
 * @return mixed
 */
	public function postpar($name, $dflt)
	{
	    return filter_has_var(INPUT_POST, $name) ? trim($_POST[$name]) : $dflt;
	}
/**
 * Return the main action part of the URL as set by .htaccess
 *
 * @return string
 */
	public function action()
	{
	    return $this->reqaction;
	}
/**
 * Return the part of the URL after the main action as set by .htaccess
 *
 * @return string
 */
	public function rest()
	{
	    return $this->reqrest;
	}
/**
 * Return a name for this site
 *
 * @string
 */
	public function sitename()
	{
	    return substr($this->local->base(), 1);
	}
/**
 * Generates a new, unique, sequential id value
 *
 * @param string	$id The prefix for the id
 *
 * @return string
 */
	public function newid($str = 'id')
	{
	    $this->idgen += 1;
	    return $str.$this->idgen;
	}
/**
 * Return the current logged in user if any
 *
 * @return object
 */
	public function user()
	{
	    return $this->luser;
	}
/**
 * Do we have a logged in user?
 *
 * @return boolean
 */
	public function hasuser()
	{
	    return is_object($this->luser);
	}
/**
 * Do we have a logged in admin user?
 *
 * @return boolean
 */
	public function hasadmin()
	{
	    //return ($this->hasuser() ? $this->luser->isadmin() : FALSE);
	    return $this->luser->hasadmin();
	}
/**
 * Initialise the context
 *
 * @param boolean	$local	The singleton local object
 */
	 public function __construct($local)
        {
            $this->local = $local;
            $this->luser = $this->sessioncheck('user', FALSE); # see if there is a user variable in the session....
            $uri = $_SERVER['REQUEST_URI'];
            if ($_SERVER['QUERY_STRING'] != '')
            { # there is a query string so get rid it of it from the URI
                list($uri) = explode('?', $uri);
            }
            $req = array_filter(explode('/', $uri));
            if ($this->local->base() != '')
            { # we are in a sub-directory
                array_shift($req); # pop off the directory name...
            }
            if (!empty($req))
            {
                $this->reqaction = strtolower(array_shift($req));
                $this->reqrest = empty($req) ? array('') : array_values($req);
            }
        }
	/* ---Buggy construct (left for legacy)
	public function __construct($local)
	{
	    $this->local = $local;
	    $this->luser = $this->sessioncheck('user', FALSE); # see if there is a user variable in the session....
	    $req = array_filter(explode('/', $_SERVER['REQUEST_URI'])); # array _filter removes empty elements caused by //
	    if ($this->local->base() != '')
	    { # we are in a sub-directory
	        array_shift($req); # pop off the directory name...
	    }
	    if (!empty($req))
	    {
	        $this->reqaction = strtolower(array_shift($req));
	        $this->reqrest = empty($req) ? array('') : $req;
	    }
	}
	*/
    }
?>
