<?php

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * WHMCS User Count in Client Summary
 *
 * A little hack to show the number of users associated with an account on the "Users" tab on the client
 * summary page.
 *
 * @package    WHMCS
 * @author     Lee Mahoney <lee@leemahoney.dev>
 * @copyright  Copyright (c) Lee Mahoney 2022
 * @license    MIT License
 * @version    1.0.0
 * @link       https://leemahoney.dev
 */



if (!defined('WHMCS')) {
    die('You cannot access this file directly.');
}

function client_summary_user_count($vars) {

    # Define the pages
    $pages = ['clientssummary', 'clientsprofile', 'clientscontacts', 'clientsservices', 'clientsdomains', 'clientsbillableitems', 'clientsinvoices', 'clientsquotes', 'clientstransactions', 'clientsemails', 'clientsnotes', 'clientslog'];

    # Initialize the variables needed
    $userid = null;
    $match  = false;

    # Seeing as WHMCS puts the users and tickets tabs as rewritable urls.. hotfix for that. Will match regardless of the "Friendly URLs" option as the all have /admin/client/x/users or /admin/client/x/tickets in them.
    if (preg_match("/\/client\/([1-9])\/users\b/", $_SERVER['REQUEST_URI'], $matches) || preg_match("/\/client\/([1-9])\/tickets\b/", $_SERVER['REQUEST_URI'], $matches)) {
        
        # Pattern above extracts the user id and we also have a match
        $userid = $matches[1];
        $match  = true;

    } else if (in_array($vars['filename'], $pages)) {
        
        # If there is just a normal .php file we can grab the user id as it is passed as ?userid=x and we have a match.
        $userid = $_GET['userid'];
        $match = true;

    }

    # Only run the query and JavaScript if we do have a match seeing as this is run on all admin pages.
    if ($match) {

        # Get all users associated with the client, don't need anything from this, just need to count it.
        $users = Capsule::table('tblusers_clients')->select('client_id')->where('client_id', $userid)->get();
        
        $script =   '<script type="text/javascript">
                        $(document).ready(function() {
                            $("#clientTab-3").append(" (' . count($users) . ')");   
                            
                            $("#doRemove-ok").on("click", function () {
                                location.reload();
                            });    
                        });
                    </script>';

        return $script;

    }


}

add_hook('AdminAreaHeadOutput', 1, 'client_summary_user_count');