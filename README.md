# php_default_api

How to use:

/myproject/index.php
/myproject/settings.php - settings file
/myproject/api.class.php - api file
/myproject/php-default-api - this repository
/myproject/.htaccess - modRewrite instruction if url parameters should be used


## index.php

    <?php include("php_default_api/index.php"); ?>

## settings.php

    <?php

    // system settings (required!)
    $debugmode              = false;
    $errorReporting 	= false;
    $allowedIps             = array();
    $allowedSets 		= array('ANGULAR_POST', 'GET', 'POST');
    $serverclass 		= 'myPhantasticApi';

    // settings for the api
    $settings = array();

    ?>

## api.class.php

A class inheriting from php-default-api/server.class.php 

    class myPhantasticApi extends server {
        
        // can be called at <myUrl>/?task=myEndpoint?some=parameters
        function myEndpoint() {
            // do stuff with $this->data
            // put return values into $this->return
            // raise errors as new Excpetion("error text")
        }
    
        function finish() {}
        function finish() {}
    }
    
## .htaccess

    RewriteEngine on
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule . index.php [L]