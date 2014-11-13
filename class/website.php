<?php
/**
 * A singleton class that can be used for generic information provision.
 * The sole instance of the class is passed into all twig templates and so
 * gives a single place to access data from.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2013 Newcastle University
 *
 */
    class Website
    {
	private static $idgen = 0;			# used for generating unique ids
/**
 * Used to find beans with possible pagination
 *
 * @param string		$bean	The type of bean
 * @param integer		$start	The start page number
 * @param integer		$count  The number of items wanted
 * @param string		$wval	Any other where conditions
 *
 * @return array
 */
        private function getter($bean, $start = 0, $count = '', $wval = '')
        {
            if ($count != 0)
            {
                if ($wval == '')
                {
                    $wval = '1';
                }
                $wval .= ' limit '.($start < 0 ? 0 : $start).','.$count;
            }
            return R::find($bean, $wval);
        }
/**
 * Generates a new, unique id value
 *
 * @param string	$id The prefix for the id
 * @return string
 */
	public function newid($str = 'id')
	{
	    self::$idgen += 1;
	    return $str.self::$idgen;
	}
/**
 * Returns the site name as specified in lib/config.php
 *
 * @return string
 */
	public function name()
	{
	    return SITE;
	}
/**
 * Returns user beans, possibly controlled by pagination
 *
 * @param integer		$start	The start page number
 * @param integer		$count  The number of items wanted
 *
 * @return array
 */
	public function users($start = 0, $count = 0)
	{
	    return $this->getter('user', $start, $count, '');
	}
    }
?>
