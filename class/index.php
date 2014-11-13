<?php

/**
 *    @description:
*                              )       \   /      (
*                            /|\      )\_/(     /|\
*                           / | \    (/\|/\)   / | \                      *
*    |`.____________________/__|__o____\`|'/___o__|__\___________________.'|
*    |                           '^`    \|/   '^`                          |
*    |                                   V                                 |
*    |                 Index Page which checks if we have a user           |
*    |                 If user found, then directed to the mian            |
*    |                 page, if not user is asked to sign in               |
*    |                                                                     |
*    | ._________________________________________________________________. |
*    |'               l    /\ /     \\            \ /\   l                `|
*                    l  /   V       ))            V   \ l                 *
*                    l/            //                  \I
*                           
*    @author: Abdul Al-Faraj <a.al-faraj@ncl.ac.uk>
*    @copyright: Newcastle University
*
*
*
*
*
*   @notes: Future Improvments
*
*        I would use the model styles for dealing with database, AS I prefer MVC pattern to design such website, however due to time
*        I could not be able to do so. Models are most importantly useful to abstract the database from the control, in my other projects
*        Models are most useful to return a certain set of beans, so if I want beans with ordered first name or by birthday I would not do that
*        within the Controllers (in this framework it is the classes). I would do them in a model. 
*
*        Other improvement I would suggest for future is seperating the classes from the model to create a more structured frame for the website
*        This can help significantly if there are many models and controllers (which is usually the case).
*
*        But because of time, I have created my best code with the limited time.*
*
*        Abdul Al-Faraj     <a.al-faraj@ncl.ac.uk>
*        
**/
 
 
 class index extends Siteaction
 {
         
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
    * Handles all the staff and manage them
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
 