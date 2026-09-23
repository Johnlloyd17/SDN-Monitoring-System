<?php
require_once __DIR__ . '/pages/auth_check.php';

require_auth();

header('Location: pages/dashboard/dashboard.php');
exit;