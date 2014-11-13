<?php

/**
 *    @description:
 *    deals with all the requireis of the staff.
 *    this page handles adding, removing or editing staff members...
 *    Also refered to as Key Holders
 *    @author: Abdul Al-Faraj <a.al-faraj@ncl.ac.uk>
 *    @copyright: Newcastle University
 *    @date: 15/12/13
 **/

 class staff extends Siteaction{
    //State codes which will indicates for bootstrap which type of alert to use
    private $StateCode;

    /**
    *    Add Staff to a database
    *    @param $context
    *    @param $local
    *
    *    @return varification to the user if added or not
    */
    public function addmembers($context, $local)
    {
        $userpassed =  array(
            'id'    => $context->getpar('id', ''),
            'firstname' => $context->postpar('firstname',''), 
            'lastname' => $context->postpar('lastname',''), 
            'email' => $context->postpar('email',''), 
            'accesslevel' => $context->postpar('accesslevel','') , 
            'password' => $context->postpar('password',''), 
            );
        
        $state = $this->add_or_updateUSER($userpassed, true);   //true for new user
        $local->addval('state', $state);
        $local->addval('statecode',$this->StateCode );
        return 'addstaff.twig';
    }

    /**
    *    Display staff members (Maintaining 5 pages of paginiations)
    *    @param $context
    *    @param $local
    *
    *    @return list of members
    */
    public function managestaff($context, $local)
    {
        //------------ Variables
        $userPatch = 0;
        $nextPage = 0;    //The next page requested 
        $minPages = 1;    //The inital number at which pages starts
        $maxPages = 5;    //Maximum page anyone can go to at the first turn
        $limitusers = 5;    //number of users to display per page
        
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
        $local->addval('accesslevel', $context->user()->accesslevel);

        return 'managestaff.twig';
        
    }

    /**
    *    Delete staff members (Using bootstrap's model techinques)
    *    @param $context
    *    @param $local
    *
    *    @return list of members
    */
    public function deleteuser($context, $local)
    {
        $id = $context->getpar('id','');
        $user = $context->load('users', $id);
        R::trash($user);
        $local->addval('state', 'User has been deleted');
        $context->divert('/managestaff');
    }

    /**
    *    Returns the key holder that will be edited
    *    @param $context
    *    @param $local
    *
    *    @return staff member (key holdr)
    */
    public function edituser($context, $local)
    {

        $id = $context->getpar('id','');
        $user = $context->load('users', $id) ;

       if( !$user ){
            $local->addval( 'state', 'User does not exist');
            $local->addval( 'statecode', 0);
            return 'editstaff.twig';
       }

        $firstname = $user->firstname;
        $lastname = $user->lastname;
        $email = $user->email;
        $accesslevel = $user->accesslevel;

        //Return the information about the user which will be handled
        $local->addval( 'user' , $user );
        return 'editstaff.twig';
    }

    /**
    *    Edit a key holder.
    *    @param $context
    *    @param $local
    *
    *    @return staff member (key holdr)
    */
    public function editinguser($context, $local)
    {
        $id = $context->getpar('id','');
        $user = $context->load('users', $id) ;

        if(!$user){
            $local->addval( 'state', 'User does not exist');
            $local->addval( 'statecode', 0);
            return 'editstaff.twig';
        }

        $userpassed =  array(
            'id'    => $context->getpar('id', ''),
            'firstname' => $context->postpar('firstname',''), 
            'lastname' => $context->postpar('lastname',''), 
            'email' => $context->postpar('email',''), 
            'accesslevel' => $context->postpar('accesslevel','') , 
            'password' => $context->postpar('password',''), 
            );

        $state = $this->add_or_updateUSER($userpassed, false);
        $local->addval('state', $state);
        $local->addval('statecode',$this->StateCode );
        $local->addval('user', R::load('users',$userpassed['id']));

        return 'editstaff.twig';
    }

    /**
    *    Allows adding or amending current users (key holders), 
    *
    *   @param  $user bean
    *   @param  $newUSER which indicates weather this is a new bean or amending old bean
    *
    *   @return $state
    */
    private function add_or_updateUSER($user, $newUSER){

        /**
        *    Check if the password passes the validation
        *    password should have at least one digit
        *    password should start with a letter
        *    password rang between 7 and 15 characters
        *    Also will check for email validation
        */
        $passwordpattern1 = "/^[a-zA-z][A-Za-z]{7,14}/"; //should start with a letter and range betwen 7,14
        $passwordpattern2 = "/[0-9]+/";   //check to have at least 1 digit
        preg_match($passwordpattern1, $user['password'], $match1);
        preg_match($passwordpattern2, $user['password'], $match2);

        //Look for a matching email in the database

        # Need to check the email before adding it, and this is only for new users as the updating user would have it's email already in the DB
        $matchingEmails = R::find('users',' email = ? ', 
                array( $user['email'] )
            );
        if ( !empty($matchingEmails) && $newUSER )
        {
            $this->StateCode = 0;
            return $state = "User is not added, the email used is already registered";
        }
        elseif(filter_var($user['email'], FILTER_VALIDATE_EMAIL) && !empty($match1) && !empty($match2))
        {
            $member = ($newUSER == true? R::dispense('users') : R::load('users', $user[id]));
            $member->firstname = $user['firstname'];
            $member->lastname = $user['lastname'];
            $member->email = $user['email'];
            $member->pasw = password_hash($user['password'],PASSWORD_BCRYPT );
            $member->accesslevel = $user['accesslevel'];
            $id_member = R::store($member);
            $this->StateCode = 1; 
            return $state = "Successfully added member to the database with ID: ".$id_member;
        }
        else
        {
            return $state = "User couldn't not be added/amended.... Please follow the given formate";
            $this->StateCode = 0; 
        }      
    }


/**
 *    Handles all the staff and manage them
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
 