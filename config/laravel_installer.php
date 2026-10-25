<?php

// config for AmbroseTheBuild/LaravelInstaller
return [
    'required_extensions' => [
        'openssl',
        'pdo',
        'mbstring',
        'tokenizer',
        'xml',
        'ctype',
        'json',
        'bcmath',
        "zip",
    ],
    'folders' => [
        'storage/logs',
        'bootstrap/cache',
        'storage/framework',
    ],

    'show_creator_section' => false, // set to false to hide the creator section
];
