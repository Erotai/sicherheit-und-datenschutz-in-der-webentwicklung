<?php

namespace THM\Security;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action('wp_footer', ['THM\Security\Comment', 'wp_footer'], 10, 0);
add_action('admin_notices', ['THM\Security\Comment', 'admin_notices'], 10, 0);
add_filter('get_comment_author',['THM\Security\Comment', 'change_author_name'], 10, 1);
add_filter('get_comment_author_url',['THM\Security\Comment', 'change_author_url'], 10, 1);
class Comment
{
    /**
     * Add a text to the footer
     */
    public static function wp_footer()
    {
        echo '
            <p>
                <b>SDW:</b> Comment Filter Test 
            </p>
        ';
    }

    public static function admin_notices()
    {
        echo '
            <div class="notice notice-success is-dismissible">
                <p>
                    SDW: CommentFilter-Plugin wurde erfolgreich geladen!
                </p>
            </div>
        ';
    }
    public static function change_author_name ($comment_author)
    {
        return $comment_author = 'Anonymous';
    }

    public static function change_author_url ($comment_author_url)
    {
        return $comment_author_url = "https://astrobackyard.com/";
    }

}
?>