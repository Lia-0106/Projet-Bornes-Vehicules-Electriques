<?php
require 'database.php';
$db = dbConnect();
if ($db) {
    echo "DB_CONNECTION_OK\n";
} else {
    echo "DB_CONNECTION_FAIL\n";
}
