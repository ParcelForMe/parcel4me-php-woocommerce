<?php
   /*
   Description: A key part of impementing the parcel for me checkout is accepting the /p4m 
                routes that the widgets call, this class handles that
                NB: expects $p4m_shopping_cart_adapter global instance of p4m woo cart object
   Version: 0.0.1
   Author: ParcelForMe
   Author URI: http://parcelfor.me/
   License: MIT
   */

if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}


add_action( 'parse_request', 'handle_p4m_routes' );

function handle_p4m_routes( $query ) 
{


    function string_begins( $string, $query ) {
        return substr($string, 0, strlen($query)) === $query;
    }

    $route = $query->request;

    if ( string_begins( $route, 'p4m/' ) ) 
    {

        $p4m_shopping_cart_adapter = $GLOBALS['parcel4me_woo']->p4m_shopping_cart_adapter;

        switch($route) {
            
            // GET
            case 'p4m/signup' :                 $p4m_shopping_cart_adapter->signUp();                    break;
            case 'p4m/getP4MAccessToken' :      $p4m_shopping_cart_adapter->getP4MAccessToken();         break;
            case 'p4m/isLocallyLoggedIn' :      $p4m_shopping_cart_adapter->isLocallyLoggedIn();         break;                
            case 'p4m/localLogin' :             $p4m_shopping_cart_adapter->localLogin();                break;                
            case 'p4m/restoreLastCart' :        $p4m_shopping_cart_adapter->restoreLastCart();           break;
            case 'p4m/getP4MCart' :             $p4m_shopping_cart_adapter->getP4MCart();                break;
            case 'p4m/paypalSetup' :            $p4m_shopping_cart_adapter->paypalSetup();               break;
            case 'p4m/paypalCancel' :           $p4m_shopping_cart_adapter->paypalCancel();              break;

            // POST
            case 'p4m/updShippingService' :     $p4m_shopping_cart_adapter->updShippingService();        break;
            case 'p4m/applyDiscountCode' :      $p4m_shopping_cart_adapter->applyDiscountCode();         break;
            case 'p4m/removeDiscountCode' :     $p4m_shopping_cart_adapter->removeDiscountCode();        break;
            case 'p4m/itemQtyChanged' :         $p4m_shopping_cart_adapter->itemQtyChanged();            break;
            case 'p4m/purchase' :               $p4m_shopping_cart_adapter->purchase();                  break;

            default:
                // NB : This endpoint required if implementing 3D secure transactions : 'p4m/purchaseComplete/([a-z0-9_-]+)'
                echo 'Hello unhandled GET endpoint : ' . htmlentities($p4mEndpoint);

        }

        die();

    }

}


?>