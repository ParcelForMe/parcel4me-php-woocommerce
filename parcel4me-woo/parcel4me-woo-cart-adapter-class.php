<?php 
   /*
   Description: The Parcel4Me Woo Shop is the interface beteen parcel4me and WooCommerce
   Version: 0.0.1
   Author: ParcelForMe
   Author URI: http://parcelfor.me/
   License: MIT
   */

if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}


require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/vendor/parcel4me/parcel4me/src/parcel4me/p4m-shop.php';
// i'm not sure why I can't load it like this : require_once __DIR__.'/vendor/autoload.php';
// contributers who know composer autoloader better than me are very welcome to help with that bit. 

class Parcel4me_Woo_Cart_Adapter extends P4M\P4M_Shop {


    function userIsLoggedIn() 
    {
        return is_user_logged_in();
    }


    function createNewUser( $p4m_consumer ) 
    {
        $username = $p4m_consumer->Email;
        $password = wp_generate_password();
        $user_data = array(
                        'user_login'   => $username,
                        'user_pass'    => $password,
                        'user_email'   => $p4m_consumer->Email,
                        'first_name'   => $p4m_consumer->GivenName,
                        'nickname'     => $p4m_consumer->GivenName,
                        'display_name' => $p4m_consumer->GivenName,
                        'last_name'    => $p4m_consumer->FamilyName
        );
        $wp_userid = wp_insert_user( $user_data );

        if ( is_wp_error( $wp_userid ) ) {
            error_log('P4M: Unable to create new user; '.$username);
            return false;
        } else {
            $wp_user   = get_userdata( $wp_userid );
            $wp_user->id = $wp_userid; // p4m is expecting to see an ->id field (WP will also have the same as ->ID)
            return $wp_user;
        }
    }


    function isValidUserId( $localUserId ) 
    {
        return !!get_userdata( $user_id );
    }


    function fetchLocalUserByEmail( $localUserEmailAddress ) {
        return get_user_by( 'email', $localUserEmailAddress );
    }


    function loginUser( $localUserId ) 
    {
        wp_set_auth_cookie( $localUserId );
        return true;
    }


    function logoutCurrentUser() 
    {
        wp_logout();
        return true;
    }


    function setCurrentUserDetails( $p4m_consumer ) 
    {

        // update name and email of wordpress user
        $wp_user = wp_get_current_user();
        $wp_user->user_email        = $p4m_consumer->Email;
        $wp_user->first_name        = $p4m_consumer->GivenName;
        $wp_user->last_name         = $p4m_consumer->FamilyName;
        $wp_user->first_name        = $p4m_consumer->GivenName;
        $wp_user->nickname          = $p4m_consumer->GivenName;
        $wp_user->display_name      = $p4m_consumer->GivenName;
        $wp_user->last_name         = $p4m_consumer->FamilyName;
        
        $update_result = wp_update_user( $wp_user );

        // TODO: NOT IMPORTANT : maybe update addresses and payment methods

        if ( is_wp_error( $update_result ) ) {
            error_log('P4M: Unable to update current user');
            return false;
        } else {
            return true;
        }
    }
    

    function getCurrentUserDetails() 
    {

        $wp_user = wp_get_current_user();

        if ( 0 == $current_user->ID ) {
            // No logged in user 
            return false;
        }

        // TODO : IMPORTANT ! maybe get addresses from woo commerce if possible

        $consumer = new P4M\Model\Consumer();
        $consumer->GivenName  = $wp_user->first_name;
        $consumer->FamilyName = $wp_user->last_name;
        $consumer->Email      = $wp_user->user_email;

        if ( !property_exists( $consumer, 'Extras' ) ) {
            $consumer->Extras  = new stdClass();
        }  
        if ( !property_exists( $consumer->Extras, 'LocalId' ) ) {
            $consumer->Extras->LocalId = $current_user->ID;
        }

        $consumer->removeNullProperties();

        return $consumer;
    }


    // -----------------------------------------------------------------
    // Code below here is WooCommerce specific -------------------------


    function getCartOfCurrentUser() {
            
        $woo_cart = WC()->cart;

        // convert Woo Cart into a P4M Cart 

        // first create each item 

        $items = array();
        $woo_cart_item_details = $woo_cart->get_cart();
        foreach($woo_cart_item_details as $key => $woo_item) {

            //$truncated_desc = strlen($woo_item['data']->post->post_content) > 120 ? substr($woo_item['data']->post->post_content,0,90)."..." : $woo_item['data']->post->post_content; 

            $cartItem = new P4M\Model\CartItem();
            $cartItem->Make         = $woo_item['data']->post->post_name;
            $cartItem->Sku          = $woo_item['product_id'];
            $cartItem->Desc         = $woo_item['data']->post->post_title;
            $cartItem->Qty          = $woo_item['quantity'];
            $cartItem->Price        = (double)$woo_item['data']->price;
            $cartItem->LinkToImage  = ( (has_post_thumbnail( $woo_item['data']->post->ID )) ? (wp_get_attachment_image_src( get_post_thumbnail_id( $woo_item['data']->post->ID ), 'single-post-thumbnail' )[0]) : null );
            $cartItem->LinkToItem   = get_permalink( $woo_item['data']->post->ID );
            $cartItem->removeNullProperties();    

            $items[] = $cartItem;
        }

        // and then the cart object 

        $generated_reference = spl_object_hash( $woo_cart );

        $cart = new P4M\Model\Cart();
        $cart->SessionId    = $this->getCurrentSessionId();
        $cart->Reference    = $generated_reference; 
        $cart->Date         = gmdate( "D, d M Y T" );
        $cart->Currency     = get_woocommerce_currency();
        $cart->ShippingAmt  = $woo_cart->shipping_total;
        $cart->Tax          = $woo_cart->tax_total;
        $cart->Total        = $woo_cart->total;
        $cart->PaymentType  = "DB"; // TODO : NOT YET READY : this needs more throught first before implementing
        // $cart->Discounts    = ?? $woo_cart->$coupons ?? // TODO : coupon logic to do later
        $cart->Items        = $items;
        $cart->removeNullProperties();

        return $cart;
    }


    function setCartOfCurrentUser( $p4m_cart ) {

        $woo_cart = WC()->cart;

        $woo_cart->empty_cart();

        foreach( $p4m_cart->Items as $p4m_item ) {
            $woo_cart->add_to_cart( $p4m_item->Sku, $p4m_item->Qty );
        }

        return true;
    }
    

    function updateShipping( $shippingServiceName, $amount, $dueDate ) {

        $woo_cart = WC()->cart;

        // TODO : fix this -- it doesn't work -- woo requires that we create an actual shipping method and set that on the cart

        $woo_cart->shipping_total = $amount;

        return true;
    }


    function getCartTotals() {

        $woo_cart = WC()->cart;

        $r = new stdClass();
        $r->Tax      = $woo_cart->get_taxes_total( false, false );
        $r->Shipping = $woo_cart->shipping_total;
        $r->Discount = $woo_cart->get_cart_discount_total();
        $r->Total    = $woo_cart->cart_contents_total;

        return $r;
    }


    public function updateCartItemQuantities( $itemsUpdateArray ) {

        $woo_cart = WC()->cart;
        $refresh_totals = true;

        foreach( $itemsUpdateArray as $item_update ) {
            $woo_cart->set_quantity( $item_update['ItemCode'], $item_update['Qty'], $refresh_totals );
        }


        // TODO : get the discounts attached to the cart and return them
        /*
        $dis = new P4M\Model\Discount();
        $dis->Code           = 'valid_code';
        $dis->Description    = 'A demo valid coupon code!';
        $dis->Amount         = 0.01;

        $disArray = [ $dis ];
        */
        $disArray = [];

        return $disArray;
    }


    function updateWithDiscountCode( $discountCode ) {
        /* 
            some logic goes here to check if this discount code is valid,
            if not throw an error, if so then apply it to the cart and return the discount details 
        */

        // TODO !

        $dis = new P4M\Model\Discount();

        if ($discountCode != 'valid_code') // special discount code "valid_code" works, else fails
        {
            throw new Exception('Unknown discount code.'); 
        }

        $dis->Code          = $discountCode;
        $dis->Description   = 'A demo valid coupon code!';
        $dis->Amount        = 0.01;

        return $dis;
    }


    function updateRemoveDiscountCode( $discountCode ) {
        /* 
            some logic goes here to remove this discount code from the cart 
            throw error if it is not on there
        */

        // TODO !

        $dis = new P4M\Model\Discount();

        if ($discountCode != 'valid_code') // special discount code "valid_code" works, else fails
        {
            throw new Exception('Unknown discount code.'); 
        }

        $dis->Code          = $discountCode;
        $dis->Description   = 'A demo valid coupon code!';
        $dis->Amount        = 0.01;

        return $dis;
    }
    

    public function completePurchase ( $purchase_data ) {
        
        $p4m_cart           = $purchase_data->Cart; 
        $transactionId      = $purchase_data->Id;
        $transationTypeCode = $purchase_data->TransactionTypeCode; 
        $authCode           = $purchase_data->AuthCode;

        $wp_user = wp_get_current_user();

        $cart = WC()->cart;

        if ( $cart->is_empty() ) {
            throw new Exception( sprintf( __( 'Sorry, your session has expired. <a href="%s" class="wc-backward">Return to shop</a>', 'woocommerce' ), esc_url( wc_get_page_permalink( 'shop' ) ) ) );
        }

        // Prevent timeout
        @set_time_limit(0);
        
        $checkout = WC()->checkout();

        $order_id = $checkout->create_order();
        $order = wc_get_order( $order_id );

        update_post_meta($order_id, '_customer_user', get_current_user_id()); // set user on the order

        update_post_meta( $order_id, '_payment_method', 'p4m_payment_gateway' );
        update_post_meta( $order_id, '_payment_method_title', 'Parcel For Me Payment' );


        if (property_exists($purchase_data, 'DeliverTo') && $purchase_data->DeliverTo) {
            $p4m_ad = $purchase_data->DeliverTo;
            $address = array(
                'first_name' => $wp_user->first_name,
                'last_name'  => $wp_user->last_name,
                'company'    => $p4m_ad->CompanyName,
                'email'      => $wp_user->email,
                'phone'      => $p4m_ad->Phone,
                'address_1'  => $p4m_ad->Street1,
                'address_2'  => $p4m_ad->Street2, 
                'city'       => $p4m_ad->City,
                'state'      => $p4m_ad->State,
                'postcode'   => $p4m_ad->PostCode,
                'country'    => $p4m_ad->CountryCode
            );
            $order->set_address( $address, 'shipping' );

            // set BillTo address to the same if it is null
            if ( property_exists($purchase_data, 'BillTo') && (null==$purchase_data->BillTo) ) {
                $order->set_address( $address, 'billing' );
            }

        }

        if (property_exists($purchase_data, 'BillTo') && $purchase_data->BillTo) {
            $p4m_ad = $purchase_data->BillTo;
            $address = array(
                'first_name' => $wp_user->first_name,
                'last_name'  => $wp_user->last_name,
                'company'    => $p4m_ad->CompanyName,
                'email'      => $wp_user->email,
                'phone'      => $p4m_ad->Phone,
                'address_1'  => $p4m_ad->Street1,
                'address_2'  => $p4m_ad->Street2, 
                'city'       => $p4m_ad->City,
                'state'      => $p4m_ad->State,
                'postcode'   => $p4m_ad->PostCode,
                'country'    => $p4m_ad->CountryCode
            );
            $order->set_address( $address, 'billing' );
        }

        $order->calculate_totals();
        $order->payment_complete( $transactionId ); 

        $cart->empty_cart();

        return true;
    }
        

    function handleError($message) {
        echo '<div class="error p4m-error">'.$message.'</div>';
    }


    function returnProductInformation ( $sku ) {
        
        // TODO : IMPORTANT : ALSO THIS IS A NEW ENDPOINT AND NEEDS TO BE IMPLMENTED ALL THE WAY DOWN .. \

        /*
         
         . validate the access token 
         . lookup the product in the retailer DB 
         . return as much info as possible based on upcoming p4m product model 

        */
    }


}
?>