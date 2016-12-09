# php_default_api

How to use:

/myproject/settings.php - settings file
/myproject/api.class.php - api file
/myproject/php-default-api

## settings file

    <?php

    // system settings (required!)
    $debugmode 		= false;
    $errorReporting 	= false;
    $allowedIps		= array();
    $allowedSets 		= array('ANGULAR_POST', 'GET', 'POST');
    $serverclass 		= 'api';

    // settings for the api
    $settings = array(
    );

    ?>

## api.class.php

A class inheriting from php-default-api/server.class.php 