<?php
/*
Plugin Name: SDW-05
Description: Sicherheit und Datenschutz in der Webentwicklung - Kapitel 5
Version: 1.0.0
Author: Technische Hochschule Mittelhessen
Author URI: https://www.thm.de
*/

namespace THM\Security;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once(dirname(__FILE__) . '/modules/classifier.php');
require_once(dirname(__FILE__) . '/modules/username-enumeration.php');
require_once(dirname(__FILE__) . '/modules/log.php');
require_once(dirname(__FILE__) . '/modules/demo.php');
require_once(dirname(__FILE__) . '/modules/comment-author.php');

?>