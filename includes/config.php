<?php

date_default_timezone_set('Asia/Kolkata');

define('SITE_NAME', 'Company CRM');

define('BASE_URL', 'http://localhost/company-crm/');

define('DB_HOST', 'localhost');
define('DB_NAME', 'company_crm');
define('DB_USER', 'root');
define('DB_PASS', '');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}