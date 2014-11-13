<?php


/**
 *@description:
 *  This class saves all hisotry, when is issued and returned.....
 *  As well as dealing with issuing keys, and returning keys......
 *
 *  @author: Abdul Al-Faraj <a.al-faraj@ncl.ac.uk>
 *  @copyright: Newcastle University
 *
 **/

 
 class history extends Siteaction
 {

    //Messages that passed from one function to another to another using ids
    private $msg1 = "key has been issued, please refer to event with ID: ";
    private $msg2 = "Key is already issued, please try again later or use different key";
    private $msg3 = "Key did not issued to the user, please try again.....";
    private $msg4 = "Key was successfuly returned.....";
    private $msg5 = "Returning the Key was unsuccessfull.....";

    /**
    *function responsible for returning keys, which is invoked by the managing key page
    *@param $context
    *@param $local
    *
    *@return Confirmation displayed at the history section
    */
    
    public function returnkeys($context, $local)
    {
        $id = $context->getpar('id', NULL);
        if(isset($id))
        {
            $key = $context->load('keys', $id);
            $user = $context->load('users', $key->user_id);

            $key->issued = "NO";
            unset($key->user);

            #add this activity to the history table
            $event = R::dispense('events');
            $event->type = "RETURN";
            $event->key = $key;
            $event->keyname = $key->name; 
            $event->user = $user;
            $event->username = $user->firstname." ".$user->lastname;

            $event->date = date("Y-m-d");
            $event->time = date("h:i:s");

            #STORING TO DATABASE:
            $keysotre = R::store($key);
            $eventsotre = R::store($event);
            $context->divert("/events?msgid=4");
        }
        else
        {
            $context->divert("/events?msgid=5");
        }
    }
    
    /**
    *function responsible for displaying the manager page to issue keys
    *@param $context
    *@param $local
    *
    *@return varification to the user if added or not
    */
    public function issue($context, $local)
    {
        $keyid = $context->getpar('id', '');
        $key =$context->load('keys', $keyid);

        //------------ Variables
        $userPatch = 0;
        $nextPage = 0;    //The next page requested 
        $minPages = 1;    //The inital number at which pages starts
        $maxPages = 5;    //Maximum page anyone can go to at the first turn
        $limitusers = 10;    //number of users to display per page
        
        $beansCount = R::count("users");    #number of beans within the database.
        
        //Calculate the maximum beans and then devided them on the page to get them on equal number of pages
        $lastPage = round($beansCount/$limitusers);

        $nextPage = $context->getpar('page',''); 

        if($nextPage > 0 && $nextPage!='')
        {
            $userPatch = $limitusers * $nextPage ;
            $maxPages = ($nextPage + 2) >= $lastPage ? $lastPage : ($nextPage + 2);
            $minPages = ($nextPage - 2) < 0 ? 0 : ($nextPage - 2);
        }

        //SQL statment that will display users with limits.
        $sql = "SELECT * FROM `users` LIMIT ".$userPatch.",".$limitusers;
        $rows = R::getAll($sql);
        $users = R::convertToBeans('users',$rows);

        //Passing information to the template
        $local->addval('users', $users);
        $local->addval('minPages', $minPages);
        $local->addval('maxPages', $maxPages);
        $local->addval('keyid', $keyid);
        return 'issuekeys.twig';

    }


    /**
    *function responsible for issuing keys, which is invoked by the form that is render in the previouse function
    *@param $context
    *@param $local
    *
    *@return varification to the user if added or not
    */
    public function issuing($context, $local)
    {

        $keyid = $context->getpar('keyid', '');
        $userid = $context->getpar('userid', '');

        $key = $context->load('keys', $keyid);
        $user = $context->load('users', $userid);

        #check if user and key in the DB
        if( $key && $user && $key->issued == "NO")
        {
            
            #declare the key is issued and assign the user who issued it (two ways to declare one-to-many relations in RedBean, This is first method)
            $key->issued = 'YES';
            $key->user = $user;

            #add this activity to the history table
            $event = R::dispense('events');
            $event->type = "ISSUE";
            $event->key = $key;
            $event->keyname = $key->name; 
            $event->user = $user;
            $event->username = $user->firstname." ".$user->lastname;

            $event->date = date("Y-m-d");
            $event->time = date("h:i:s");

            #STORING TO DATABASE:
            $keysotre = R::store($key);
            $eventsotre = R::store($event);

            $context->divert('/events?msgid=1');
        }
        else
        {
            if($key->issued == "YES"){
                $context->divert('/events?msgid=2');
          }
            else
            {
                $context->divert('/events?msgid=3');
            }
        }
    }

    
    /**
     *    Handles the history
     *
     *    @param  $context   
     *    @param  $local     
     *
     *    @return string    A template name
     */

    public function events($context, $local)
    {
        //Get the id of the the last sett message: 
        $msgid = $context->getpar('msgid',NULL); 

        if (isset($msgid)) 
        {
            switch ($msgid) 
            {
                case '1':
                    $local->addval('state', $this->msg1);
                    break;
                case '2':
                    $local->addval('state', $this->msg2);
                    break;           
                case '3':
                    $local->addval('state', $this->msg3);
                    break;
                case '4':
                    $local->addval('state', $this->msg4);
                    break;
                default:
                    $local->addval('state', $this->msg5);
                    break;            
            }
        }

        //------------ Variab history 
        $beansCount = R::count("events");    #number of beans within the database.
        $patch = 0;     //This is the number at which the mysql carry on displaying beans. starts with last bean number to display the newest first
        $limitevents = 10;    //number of events to display per page

        //Calculate the maximum beans and then devided them on the page to get them on equal number of pages
        $lastPage = round($beansCount/$limitevents);

        //Calculate the inital pages - should be between 1 and last page number divided by 2
        $minPages = 1;    //The inital number at which pages starts
        $maxPages = round($lastPage/2);    //Maximum page anyone can go to at the first run of the page

        $nextPage = $context->getpar('page', NULL); 

        if(isset($nextPage))
        {
            //The following tests make sure that the pagination bare stays in a 5 page limits as follows:
            //       CurrentPage-2 CurrentPage-1 CurrentPage CurrentPage+1 CurrentPage+2
            $patch = ($nextPage <= 1) ? 0 : ($nextPage * $limitevents);
            $maxPages = ($nextPage + 2) >= $lastPage ? $lastPage : ($nextPage + 2);
            $minPages = ($nextPage - 2) <= 0 ? 1 : ($nextPage - 2);
        }
        
      //SQL statment that will display events with limits. Ordered by the newest
        $sql = "SELECT * FROM `events` ORDER BY date,time DESC LIMIT ".$patch.",".$limitevents ;
        $rows = R::getAll($sql);
        //$events = R::convertToBeans('events' , $rows);        //caused PARSE error, so I commented


        //Passing information to the template
        $local->addval('events', $rows);
        $local->addval('minPages', $minPages);
        $local->addval('maxPages', $maxPages);
        return 'history.twig';
    }

    
    /**
     *    Handles all the keys' and staffs' history
     *
     *    @param object $context    The context object for the site
     *    @param object $local      The local object for the site
     *
     *    @return string    A template name
     */
    public function handle($context, $local)
    {
        $action = $context->action();
        return $this->$action($context, $local);
    }
 }
 