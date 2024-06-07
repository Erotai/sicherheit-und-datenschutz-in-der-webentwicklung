<?php
namespace THM\Security;

class THM_Security_Tests
{
    function test_config_grabber()
    {
        $_SERVER['REQUEST_URI'] = '/wp-config.php';
        ob_start();
        THM\Security\Classifier::init();
        $output = ob_get_clean();

        $this->assertContains('404 Not Found', $output);
    }

    function test_normal_request()
    {
        $_SERVER['REQUEST_URI'] = '/';
        ob_start();
        THM\Security\Classifier::init();
        $output = ob_get_clean();

        $this->assertNotContains('404 Not Found', $output);
    }

    function test_ip_blocking()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'thm_security_logs';

        // Insert dummy data
        $wpdb->insert($table_name, ['ip_address' => '192.168.1.1', 'request_class' => 'config-grabber', 'request_time' => current_time('mysql')]);

        // Simulate multiple bad requests from the same IP
        for ($i = 0; $i < 11; $i++) {
            THM\Security\Classifier::log_request('config-grabber');
        }

        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';
        $_SERVER['REQUEST_URI'] = '/wp-config.php';
        ob_start();
        THM\Security\Classifier::init();
        $output = ob_get_clean();

        $this->assertContains('403 Forbidden', $output);
    }
}