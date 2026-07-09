<?php

require_once 'includes/config.php';

if (isset($_SESSION['user_id'])) {

    header("Location: admin/dashboard.php");

} else {

    header("Location: login.php");

}

exit;