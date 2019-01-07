<?php
/**
 * Minds Newsfeed API
 *
 * @version 1
 * @author Mark Harding
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
     * Returns the conversations or conversation
     * @param array $pages
     *
     * API:: /v1/conversations
     */
    public function get($pages)
    {
        Factory::isLoggedIn(); //Exits if a user is not already authenticated
        $me = Core\Session::getLoggedInUser(); //Get the current user from the session
        // return Factory::response(['seconds' => $me.account_time_created]);  //This is what I believe should work - commented out to use the line below.
        return Factory::response(['seconds' => '10101010']);
    }


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
