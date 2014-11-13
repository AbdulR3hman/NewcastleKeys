<?php


/**
 *@description:
 *  deals with all the requireis of the keys.
 *  this page handles adding, removing or editing and assiging keys...
 *  Basically it is res. for all the functions that deals with keys
 *  @author: Abdul Al-Faraj <a.al-faraj@ncl.ac.uk>
 *  @copyright: Newcastle University
 *
 **/

 
 class keys extends Siteaction{
    
    /**
    *function responsible for adding keys
    *@param $context
    *@param $local
    *
    *@return varification to the user if added or not
    */
    public function home($context, $local)
    {

        if (!$context->hasuser()) 
        {
            return 'index.twig';
        }else
        {
            return 'keys.twig';
        }
    }

    /**
    *    This function responsible to render the correct form that adds keys
    *    @param $context
    *    @param $local
    *
    *    @return TEMPLATE
    **/
    public function addkeys($context, $local)
    {
        $locks = R::findAll('locks');
        $local->addval('locks', $locks);
        return 'addkey.twig';

    }
    
    /**
    *    This function responsible for adding key, it is invoken from the template that was rendered in the previouse function
    *    @param $context
    *    @param $local
    *
    *    @return TEMPLATE with correct confirmation message
    **/
    public function addingkeys($context, $local)
    {
        $key = R::dispense('keys');
        $state = "";

        $keyName = $context->postpar('keyname', '');
        $keyID = $context->postpar('keyID', '');
        $keyType = $context->postpar('keytype', '');
        $locker = $context->postpar('locker', '0');

        $key->barcode = $keyID;
        $key->name = $keyName;
        $key->type = $keyType;
        $key->locker = $locker;
        $key->issued = "NO"; //by defualt keys are not issued
        $key->lost = "NO"; //by defualt keys are not lost
        $storing = R::store($key);
        if($storing != -1){
            $state = 'successfully added a key';
        }

        $locks = R::findAll('locks');
        $local->addval('locks', $locks);
        $local->addval('state', $state);
        $local->addval('statecode', 1);
        return 'addkey.twig';
    }       

    /**
    * function to return the info about a current key, so that it will be edit using the edit function
    *@param $context
    *@param $local
    *
    *@return TEMPLATE with the correct key information
    */
    public function editingkeys($context, $local)
    {
        $id = $context->getpar('id', '');
        $key = $context->load('keys', $id);

       if( !$key )
       {
            $local->addval( 'state', 'Key does not exist');
            $local->addval( 'statecode', 0);
            return 'editkey.twig';
       }
        $locks = R::findAll('locks');
        $local->addval('locks', $locks);
        $local->addval( 'key', $key);
        return 'editkey.twig';
    }

    /**
    * function which is invoken through the previouse form and it the wanted key
    *@param $context
    *@param $local
    *
    *@return varification to the user if about the state of the editing
    */
    public function editkeys($context, $local)
    {

        //No need to check if the bean is indeed in the DB, because it it done using the previous function
        $id = $context->getpar('id', '');
        $key = $context->load('keys', $id) ;

        $keyName = $context->postpar('keyname', '');
        $keyID = $context->postpar('keyID', '');
        $keyType = $context->postpar('keytype', '');
        $locker = $context->postpar('locker', '0');

        $key->barcode = $keyID;
        $key->name = $keyName;
        $key->type = $keyType;
        $key->locker = $locker;
        $storing = R::store($key);


        $locks = R::findAll('locks');
        $local->addval('locks', $locks);
        $local->addval('state', "Amended key with ID: ". $keyID. " successfully");
        $local->addval('statecode', 1);
        $local->addval( 'key', $key);
        return 'editkey.twig';    
        
    }

    


    /**
    * This retreive all the keys, This function gives the user the ability to manage the keys based on thier user level
    *@param $context
    *@param $local
    *
    *@return varification to the user if added or not
    */
    public function displaykeys($context, $local)
    {
        //------------ Variables
        $keyPatch = 0;
        $nextPage = 0;   //The next page requested 
        $minPages = 1;      //The inital number at which pages starts
        $maxPages = 0;      //Maximum page anyone can go to at the first turn
        $limitKeys = 10;    //number of keys to display per page
        
        $beansCount = R::count("keys"); //number of beans within the database.
        //Calculate the maximum beans and then devided them on the page to get them on equal number of pages
        $maxPages = round($beansCount/$limitKeys);
        $nextPage = $context->getpar('page',''); 

        if($nextPage > 0){
            $keyPatch = $limitKeys * $nextPage ;
        }

        //SQL statment that will display keys with limits and offset.
        $sql = "SELECT * FROM `keys` LIMIT ".$keyPatch.",".$limitKeys;
        $rows = R::getAll($sql);
        $keys = R::convertToBeans('keys',$rows);

        $local->addval('keys', $keys);
       $local->addval('accesslevel', $context->user()->accesslevel);
        $local->addval('minPages', $minPages);
        $local->addval('maxPages', $maxPages);
        return 'managekeys.twig';

    }
   
   /**
   *  Deletes a key from database
   *
   * @param $context
   * @param $local
   * @return divert back to keys page
   */
    public function deletekey($context, $local)
    {
        $id = $context->getpar('id','');
        $key = R::load('keys', $id);
        R::trash($key);
         $context->divert('/displaykeys');
    }
    
    /**
   *  Adds a locks types to the database
   *
   * @param $context
   * @param $local
   * @return verification to the user
   */
    public function addlocks($context, $local)
    {
        $lockname = $context->getpar('lockname','');
        $lock = R::dispense('locks');
        $lock->name = $lockname;
        $id_lock = R::store($lock);
        $local->addval('state', 'Successfully added lock with id: '.$id_lock);
        $local->addval('statecode', 1);
        return 'addlock.twig';
    }

/**
 *    Handles all the keys and manage them
 *
 *    @param object	$context	The context object for the site
 *    @param object	$local		The local object for the site
 *
 *    @return string	A template name
 */
	public function handle($context, $local)
	{
	    $action = $context->action();
	    return $this->$action($context, $local);
	}
 }
 