<?php
/*
Plugin Name: RequestManager
Description: Plugin for Tracking, BLocking malicious Requests and hiding Usernames!
Version: 1.0.0
Author: Technische Hochschule Mittelhessen
Author URI: https://www.thm.de
*/

namespace THM\Security;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action('admin_notices', ['THM\Security\RequestManager', 'admin_notices'], 10, 0);

define('MAIN_FILE', __FILE__);

require_once(dirname(__FILE__) . '/modules/classifier.php');
require_once(dirname(__FILE__) . '/modules/username-enumeration.php');
require_once(dirname(__FILE__) . '/modules/log.php');
require_once(dirname(__FILE__) . '/modules/leaks.php');

class RequestManager
{

    /**
     * Display a dismissable notice in the admin area
     */
    public static function admin_notices()
    {
        echo '
            <div class="notice notice-success is-dismissible">
                <p>
                    Plugin: Request-Manager wurde erfolgreich geladen!
                </p>
            </div>
        ';
    }

}

?>