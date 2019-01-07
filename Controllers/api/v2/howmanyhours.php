<?php
/**
 * Minds Newsfeed API
 *
 * @version 1
 * @author Ben Hayward
 */
namespace Minds\Controllers\api\v2;

use Minds\Core;
use Minds\Core\Session;
use Minds\Core\Security;
use Minds\Entities;
use Minds\Helpers; 
use Minds\Interfaces;
use Minds\Api\Factory;
use Minds\Core\Sockets;

class howmanyhours implements Interfaces\Api
{

    /**
     * Returns the Unix timestamp of the logged in users creation date.
     * @param array $pages
     *
     * API:: /v2/howmanyhours
     */
    public function get($pages)
    {
        Factory::isLoggedIn(); //Exits if a user is not already authenticated
        $me = Core\Session::getLoggedInUser(); //Get the current user from the session
        return Factory::response(['seconds' => $me->time_created]);  


    public function post($pages)
    {
       
    }

    public function delete($pages)
    {
       
    }

    public function put($pages)
    {
        
    }
}
