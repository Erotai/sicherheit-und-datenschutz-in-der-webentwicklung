<?php

namespace THM\Security;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_filter('get_comment_author', ['THM\Security\UsernameEnumeration', 'get_comment_author'], 10, 3); 
add_filter('rest_endpoints', ['THM\Security\UsernameEnumeration', 'rest_endpoints']);
add_action('template_redirect', ['THM\Security\UsernameEnumeration', 'template_redirect']);

class UsernameEnumeration
{
    /**
     * Removes login names from comments.
     * 
     * Affected URLs:
     * --- http://localhost/hello-world/#comment-1
     */
    public static function get_comment_author($author, $comment_ID, $comment)
    {
        if (username_exists($author)) $author = '';
        return $author;
    }

    /**
     * Disables the Users REST API
     * 
     * Affected URLs:
     * --- http://localhost/wp-json/wp/v2/users
     * --- http://localhost/wp-json/wp/v2/users/1
     */
    public static function rest_endpoints($endpoints)
    {
        foreach ($endpoints as $route => $endpoint)
        {
            if (0 === stripos($route, '/wp/v2/users'))
            {
                unset($endpoints[$route]);
            }
        }
        return $endpoints;
    }

    /**
     * Disables the author pages
     * 
     * Affected URLs:
     * --- http://localhost/author/admin
     * --- http://localhost/?author=1
     */
    public static function template_redirect()
    {
        if (is_author())
        {
            global $wp_query;
            $wp_query->set_404();
        }
    }
}

?>