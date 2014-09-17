<?php
	include_once('CAS.php');
	phpCAS::client(CAS_VERSION_2_0, 'websso.wwu.edu', 443, '/cas');
	phpCAS::setNoCasServerValidation();
	phpCAS::forceAuthentication();
	if (isset($_REQUEST['logout'])) phpCAS::logout();
?>

<meta http-equiv='Refresh' content='1; URL=main.php'>
<html>
        <head>
                <title>phpCAS Simple Client</title>
        </head>
        <body>
                <h1>Successful Authentication!</h1>
                <p>Your login is <strong><?php  echo phpCAS::getUser(); ?></strong></p>
                <p>The phpCAS version is <strong><?php  echo phpCAS::getVersion(); ?></strong></p>
                <p>You will be redirected in one second...</p>
        </body>
</html>
