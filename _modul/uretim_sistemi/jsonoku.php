<?php
    // Get JSON as a string
    $json_str = file_get_contents('php://input');

    // Decode JSON into an array
    $json_arr = json_decode($json_str, true);

    // Print array
    print_r($json_arr);

    file_put_contents('jsonverisi.txt', $json_str);

    $file_contents = file_get_contents('jsonverisi.txt');

    // Print the contents
    echo $file_contents;
?>