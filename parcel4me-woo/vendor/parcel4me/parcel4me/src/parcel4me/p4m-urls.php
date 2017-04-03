<?php

namespace P4M;

require 'settings.php';


class P4M_Shop_Urls
{


    public static function endPoint($endPointStr, $urlParams = '') {


        $endPoints = array();

        // OAuth2 (aka. Open Id Connect) (aka. Id Server) endpoints

        $p4m_oid_server = Settings::getPublic('Server:P4M_OID_SERVER');
        $endPoints['oauth2_base_url']           = $p4m_oid_server;
        $endPoints['signup']                    = $p4m_oid_server . '/ui/signup';
        $endPoints['connect_token']             = $p4m_oid_server . '/connect/token';
        $endPoints['authorize']                 = $p4m_oid_server . '/connect/authorize';
        $endPoints['logout']                    = $p4m_oid_server . '/connect/endsession';
        $endPoints['jwks']                      = $p4m_oid_server . '/.well-known/openid-configuration/jwks';

        // Parcel 4 Me API endpoints

        $p4m_api_server = Settings::getPublic('Server:P4M_API_SERVER');
        $endPoints['alreadyRegistered']         = $p4m_api_server . '/alreadyRegistered';
        $endPoints['registerConsumer']          = $p4m_api_server . '/registerConsumer';
        $endPoints['consumer']                  = $p4m_api_server . '/consumer';
        $endPoints['consumerExtras']            = $p4m_api_server . '/consumerExtras';
        $endPoints['restoreLastCart']           = $p4m_api_server . '/restoreLastCart';
        $endPoints['paypalSetup']               = $p4m_api_server . '/paypalSetup';
        $endPoints['cart']                      = $p4m_api_server . '/cart';
        $endPoints['purchase']                  = $p4m_api_server . '/purchase';
        $endPoints['refund']                    = $p4m_api_server . '/refund';
        

        // Global Freight Solutions (GFS) endpoints
                
        $endPoints['gfs_connect_token']         = Settings::getPublic('Server:GFS_SERVER') . '/connect/token';


        $ep = $endPoints[$endPointStr];
        return $ep . $urlParams;

    }


}

?>