<?php
/*

    This config is specifically for the P4M server,
    it would only be changed for switching between
    a sandpit and live environment for example. 
    
*/

namespace P4M;

require_once 'settings.php';

function configure_server_urls( $ENV ) {

    $OID_SERVER = 'https://'.$ENV.'-ids.parcelfor.me';
    $API_SERVER = 'https://'.$ENV.'-api.parcelfor.me';

    Settings::setPublic( 'Server:P4M_OID_SERVER', $OID_SERVER );
    Settings::setPublic( 'Server:P4M_API_SERVER', $API_SERVER . '/api/v2' );

    Settings::setPublic( 'Server:GFS_SERVER',  "https://identity.justshoutgfs.com" );

}

?>