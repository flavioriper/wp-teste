<?php

define('RDSM_ASSETS_URL', plugin_dir_url(__FILE__) . '/assets');
define('RDSM_SRC_DIR', dirname(__FILE__) . '/includes');

// URIs
define('RDSM_LEGACY_API_URL', 'https://app.rdstation.com.br/api/1.3');
define('RDSM_API_URL', 'https://api.rd.services');
define('RDSM_REFRESH_TOKEN_URL', 'https://wp.rd.services/prod/oauth/refresh');

// Endpoints
define('RDSM_CONVERSIONS', '/conversions');
define('RDSM_EVENTS', '/platform/events');
define('RDSM_CONTACTS', '/platform/contacts/');
define('RDSM_TRACKING_CODE', '/marketing/tracking_code');
define('RDSM_CONTACTS_FIELDS', '/platform/contacts/fields');

// File
define('RDSM_LOG_FILE_PATH', plugin_dir_path( __FILE__ ) . '/log');
define('RDSM_LOG_FILE_LIMIT', 1000);
