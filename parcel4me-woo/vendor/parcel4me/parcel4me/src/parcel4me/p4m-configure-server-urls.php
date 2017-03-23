<?php
/*

    This config is specifically for the P4M server,
    it would only be changed for switching between
    a sandpit and live environment for example. 
    
*/

namespace P4M;

require_once 'settings.php';

function configure_server_urls( $ENV ) {

    $P4M_SRV_PORT = "44333";
    $P4M_API_PORT = "44321";
    $P4M_BASE_SERVER = 'https://'.$ENV.'.parcelfor.me';

    Settings::setPublic( 'Server:P4M_OID_SERVER', $P4M_BASE_SERVER . ':' . $P4M_SRV_PORT );
    Settings::setPublic( 'Server:P4M_API_SERVER', $P4M_BASE_SERVER . ':' . $P4M_API_PORT . '/api/v2' );

    Settings::setPublic( 'Server:GFS_SERVER',  "https://identity.justshoutgfs.com" );

}

?>