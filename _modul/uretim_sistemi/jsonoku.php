<?php
    // Get JSON as a string
    $json_str = file_get_contents('php://input');

    // Decode JSON into an array
    $json_arr = json_decode($json_str, true);

    // Print array
    print_r($json_arr);
?>