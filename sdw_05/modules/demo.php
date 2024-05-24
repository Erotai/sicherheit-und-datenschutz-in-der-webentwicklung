<?php

namespace THM\Security;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action('wp_footer', ['THM\Security\Demo', 'wp_footer'], 10, 0);
add_filter('get_the_author_display_name', ['THM\Security\Demo', 'author_display_name'], 10, 3); 
add_action('admin_notices', ['THM\Security\Demo', 'admin_notices'], 10, 0);

class Demo
{
    /**
     * Add a text to the footer
     */
    public static function wp_footer()
    {
        echo '
            <p>
                <b>SDW:</b> "Diese Seite wurde in 123 ms gerendert, der maximale Speicherbedarf lag bei 123 MB."
            </p>
        ';
    }


    /**
     * Change the display name of the author
     */
    public static function author_display_name($display_name, $user_id, $original_user_id)
    {
        if ($user_id === '1')
        {
            $display_name = 'Fake Autor';
        }
        return $display_name;
    }


    /**
     * Display a dismissable notice in the admin area
     */
    public static function admin_notices()
    {
        echo '
            <div class="notice notice-success is-dismissible">
                <p>
                    SDW: Demo-Plugin wurde erfolgreich geladen!
                </p>
            </div>
        ';
    }
}

?>