<?php

if (!defined('WPINC')) {
    die('File loaded directly. Exiting.');
}

/**
 * Plugin Name: Tidio Chat
 * Plugin URI: http://www.tidio.com
 * Description: Tidio Live Chat - live chat boosted with chatbots for your online business. Integrates with your website in less than 20 seconds.
 * Version: 6.0.1
 * Author: Tidio Ltd.
 * Author URI: http://www.tidio.com
 * Text Domain: tidio-live-chat
 * Domain Path: /languages/
 * License: GPL2
 */

define('TIDIOCHAT_VERSION', '6.0.1');
define('AFFILIATE_CONFIG_FILE_PATH', get_template_directory() . '/tidio_affiliate_ref_id.txt');

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

use TidioLiveChat\Encryption\Service\EncryptionServiceFactory;
use TidioLiveChat\IntegrationState;
use TidioLiveChat\TidioLiveChat;

function initializeTidioLiveChat()
{
    if (!empty($_GET['tidio_chat_version'])) {
        echo TIDIOCHAT_VERSION;
        exit;
    }

    $container = new \TidioLiveChat\Container();
    $tidioLiveChat = new TidioLiveChat($container);
    $tidioLiveChat->load();
}

add_action('init', 'initializeTidioLiveChat');

$encryptionService = (new EncryptionServiceFactory())->create();
register_activation_hook(__FILE__, [new IntegrationState($encryptionService), 'turnOnAsyncLoading']);
