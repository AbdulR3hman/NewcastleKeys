<?php
/**
 * A class that contains code to handle any /admin related requests.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2013 Newcastle University
 *
 */
class Admin extends Siteaction
{
/**
 * Handle various admin operations /admin/xxxx
 *
 * @param object	$context	The context object for the site
 * @param object	$local		The local object for the site
 *
 * @return string	A template name
 */
public function handle($context, $local)
{
	if ($context->user()->accesslevel == 1)
	    { # not logged in or not an admin
	    	$context->noaccess('You must be an administrator');
	    }

        //------------ Variab history 
        $beansCount = R::count("errors");    #number of beans within the database.
        $patch = 0;     //This is the number at which the mysql carry on displaying beans. starts with last bean number to display the newest first
        $limiterrors = 10;    //number of events to display per page

        //Calculate the maximum beans and then devided them on the page to get them on equal number of pages
        $lastPage = round($beansCount/$limiterrors);

        //Calculate the inital pages - should be between 1 and last page number divided by 2
        $minPages = 1;    //The inital number at which pages starts
        $maxPages = round($lastPage/2);    //Maximum page anyone can go to at the first run of the page

        $nextPage = $context->getpar('page', NULL); 

        if(isset($nextPage))
        {
            //The following tests make sure that the pagination bare stays in a 5 page limits as follows:
            //       CurrentPage-2 CurrentPage-1 CurrentPage CurrentPage+1 CurrentPage+2
        	$patch = ($nextPage <= 1) ? 0 : ($nextPage * $limiterrors);
        	$maxPages = ($nextPage + 2) >= $lastPage ? $lastPage : ($nextPage + 2);
        	$minPages = ($nextPage - 2) <= 0 ? 1 : ($nextPage - 2);
        }
        
      //SQL statment that will display events with limits. Ordered by the newest
        $sql = "SELECT * FROM `errors` ORDER BY date DESC LIMIT ".$patch.",".$limiterrors ;
        $rows = R::getAll($sql);
        //$events = R::convertToBeans('events' , $rows);        //caused PARSE error, so I commented


        //Passing information to the template
        $local->addval('errors', $rows);
        $local->addval('minPages', $minPages);
        $local->addval('maxPages', $maxPages);
        return 'admin.twig';
    }
}
?>
