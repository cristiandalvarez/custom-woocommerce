<?php
// Loads mautic api
include __DIR__ . '/vendor/autoload.php';
use Mautic\Auth\ApiAuth;
use Mautic\MauticApi;


function mautic_api_auth (){
	$settings = array(
	    'AuthMethod'       => 'BasicAuth', 
	    'userName'         => 'api_wordpress_portal',
	    'password'         => '',
	    'apiUrl'           => 'https://datos.campamentomcsa.com'
	);

	$initAuth = new ApiAuth();
	$auth = $initAuth->newAuth($settings, $settings['AuthMethod']);
	if ($auth) {
	   $api = new MauticApi();
	   return array($api,$auth,$settings['apiUrl']);
	} else {
	   echo "Something went wrong!";
	}
}
