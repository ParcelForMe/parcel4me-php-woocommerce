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

        if ( 0 == $wp_user->ID ) {
            // No logged in user 
            return false;
        }

        $consumer = new P4M\Model\Consumer();
        $consumer->GivenName  = $wp_user->first_name;
        $consumer->FamilyName = $wp_user->last_name;
        $consumer->Email      = $wp_user->user_email;

        if ( ( !property_exists( $consumer, 'Extras' ) ) ||
             ( null == $consumer->Extras ) )
        {
            $consumer->Extras  = new stdClass();
        }  

        if ( !property_exists( $consumer->Extras, 'LocalId' ) ) {
            $consumer->Extras->LocalId = $wp_user->ID;
        }

        // If the user has a billing or shipping address then add these to the P4M consumer

        $billing_address = $this->get_address_for_p4m( 'billing' );
        $shipping_address = $this->get_address_for_p4m( 'shipping' );

        $consumer->Addresses = [];
        $address_index = 0;

        if ( $billing_address ) {
            $consumer->Addresses[] = $billing_address;
            $consumer->BillingAddressId = $address_index;
            $address_index++;
        }

        if ( $shipping_address ) {
            $consumer->Addresses[] = $shipping_address;
            $consumer->PrefDeliveryAddressId = $address_index;
            $address_index++;
        }


        $consumer->removeNullProperties();

        return $consumer;
    }


// -----------------------------------------------------------------
// Code below here is WooCommerce specific -------------------------


    function get_address_for_p4m( $address_type ) {
        // WooCommerce keeps a 'billing' and 'shipping' address
        // https://docs.woocommerce.com/wc-apidocs/source-class-WC_Customer.html#418-438

        $woo_customer = WC()->customer;
        if ( (!$woo_customer) || (empty($woo_customer)) ) return false;

        if ( 'billing' == $address_type ) {

            // Business Rule - the address MUST have a country code to be valid
            $country = $woo_customer->get_country();
            if (!country) return false;

            // and if we have a country code then we create an address with whatever details we have
            $address = new P4M\Model\Address();
            $address->AddressType = 'Address';
            $address->Street1     = $woo_customer->get_address();
            $address->Street2     = $woo_customer->get_address_2();
            $address->City        = $woo_customer->get_city();
            $address->PostCode    = $woo_customer->get_postcode();
            $address->State       = $woo_customer->get_state();
            $address->CountryCode = $woo_customer->get_country();

        } elseif ( 'shipping' == $address_type ) {

            // Business Rule - the address MUST have a country code to be valid
            $country = $woo_customer->get_shipping_country();
            if (!country) return false;

            // and if we have a country code then we create an address with whatever details we have
            $address = new P4M\Model\Address();
            $address->AddressType = 'Address';
            $address->Street1     = $woo_customer->get_shipping_address();
            $address->Street2     = $woo_customer->get_shipping_address_2();
            $address->City        = $woo_customer->get_shipping_city();
            $address->PostCode    = $woo_customer->get_shipping_postcode();
            $address->State       = $woo_customer->get_shipping_state();
            $address->CountryCode = $woo_customer->get_shipping_country();

        } else {
            throw new Exception('Unknown address type : '.$address_type);
        }
       

        /* WooCommerce v3.0 Code (much neater!) :
        // Business Rule - the address MUST have a country code to be valid
        $country = $woo_customer->get_address_prop( 'country', $address_type );
        if (!$country) return false;

        // and if we have a country code then we create an address with whatever details we have
        $address = new P4M\Model\Address();
        $address->AddressType = 'Address';
        $address->CompanyName = $woo_customer->get_address_prop( 'company', $address_type );
        $address->Street1     = $woo_customer->get_address_prop( 'address_1', $address_type );
        $address->Street2     = $woo_customer->get_address_prop( 'address_2', $address_type );
        $address->City        = $woo_customer->get_address_prop( 'city', $address_type );
        $address->PostCode    = $woo_customer->get_address_prop( 'postcode', $address_type );
        $address->State       = $woo_customer->get_address_prop( 'state', $address_type );
        $address->CountryCode = $woo_customer->get_address_prop( 'country', $address_type );
        $address->Phone       = $woo_customer->get_address_prop( 'phone', $address_type );
        */

        $address->removeNullProperties();

        return $address;
    }


    function getCartOfCurrentUser() {
            
        $woo_cart = WC()->cart;

        // convert Woo Cart into a P4M Cart 

        // first create each item 

        $items = array();
        $woo_cart_item_details = $woo_cart->get_cart();
        foreach($woo_cart_item_details as $key => $woo_item) {

            //$truncated_desc = strlen($woo_item['data']->post->post_content) > 120 ? substr($woo_item['data']->post->post_content,0,90)."..." : $woo_item['data']->post->post_content; 

            // get product SKU if set, else use product_id
            $args     = array( 'post_type' => 'product', 'product_id' => $woo_item['product_id'] );
            $product = wc_get_product( $woo_item['product_id'] );
            $product_sku = $product->get_sku();
            if (!$product_sku) $product_sku = $woo_item['product_id'];

            $cartItem = new P4M\Model\CartItem();
            $cartItem->Make         = $woo_item['data']->post->post_name;
            $cartItem->Sku          = $product_sku;
            $cartItem->Desc         = $woo_item['data']->post->post_title;
            $cartItem->Qty          = $woo_item['quantity'];
            $cartItem->Price        = (double)$woo_item['data']->price;
            $cartItem->LinkToImage  = ( (has_post_thumbnail( $woo_item['data']->post->ID )) ? (wp_get_attachment_image_src( get_post_thumbnail_id( $woo_item['data']->post->ID ), 'single-post-thumbnail' )[0]) : null );
            $cartItem->LinkToItem   = get_permalink( $woo_item['data']->post->ID );
            $cartItem->LineId       = $woo_item['p4m_line_id'];
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
        $cart->Discounts    = $this->get_p4m_discounts_from_woo_cart_coupons();
        $cart->Items        = $items;
        $cart->removeNullProperties();

        return $cart;
    }


    function setCartOfCurrentUser( $p4m_cart ) {

        $woo_cart = WC()->cart;

        $woo_cart->empty_cart();

        // add in all the order lines
        foreach( $p4m_cart->Items as $p4m_item ) {
            $woo_cart->add_to_cart( $p4m_item->Sku, $p4m_item->Qty,
                                    0, null,
                                    array( 'p4m_line_id'=>$p4m_item->LineId ) );
        }

        // and apply the approprate discounts
        foreach( $p4m_cart->Discounts as $p4m_disc ) {
            $woo_cart->remove_coupon( $p4m_disc->Code );
            $woo_cart->add_discount( $p4m_disc->Code );
        }

        $woo_cart->calculate_totals();

        return true;
    }


    function updateShipping( $shippingServiceName, $amount, $dueDate, $address) {

        // set the shipping method to the Parcel For Me shipping method
        WC()->session->set( 'chosen_shipping_methods', array( 'p4m_shipping_method' ) );

        // create a custom field for the shipping amount 
        WC()->session->set( 'p4m_shipping_amount', $amount );
        WC()->session->set( 'p4m_shipping_name', $shippingServiceName );
        WC()->session->set( 'p4m_shipping_due', $dueDate );

        // set the shipping details so that Woo is able to calculate (via p4m_shipping_plugin) the shipping, and tax
        $woo_customer = WC()->customer;
        $woo_customer->set_shipping_location( $address->CountryCode,
                                              $address->State, 
                                              $address->PostCode, 
                                              $address->City );
        $woo_customer->set_shipping_address( $address->Street1 );
        $woo_customer->set_shipping_address_2( $address->Street2 );
        $woo_customer->save_data();

        return true;
    }


    function getCartTotals() {

        $woo_cart = WC()->cart;

        $woo_cart->calculate_totals();
        
        $r = new stdClass();
        $r->Tax      = $woo_cart->tax_total;
        $r->Shipping = WC()->session->get( 'p4m_shipping_amount' );
        $r->Discount = $woo_cart->get_cart_discount_total();
        $r->Total    = $woo_cart->cart_contents_total + $r->Shipping + $r->Tax;

        //error_log(' getCartTotal() = '.json_encode($r));

        return $r;
    }

    function get_p4m_discounts_from_woo_cart_coupons() {
        $woo_cart = WC()->cart;
        $p4m_discounts = [];
        $woo_coupons = $woo_cart->get_coupons();
        foreach($woo_coupons as $woo_coupon) {
            $dis = new P4M\Model\Discount();
            $dis->Code           = $woo_coupon->code;
            $dis->Description    = $woo_coupon->code;
            $dis->Amount         = $woo_cart->get_coupon_discount_amount( $woo_coupon->code );
            $dis->removeNullProperties();
            $p4m_discounts[] = $dis;
        }
        return $p4m_discounts;
    }

    public function updateCartItemQuantities( $itemsUpdateArray ) {

        $woo_cart = WC()->cart;
        $refresh_totals = true;

        foreach( $itemsUpdateArray as $item_key=>$item_update ) {
            // note that the product SKU that p4m knows will either be a woo SKU (if set), or else the product_id (see code in getCartOfCurrentUser)
            $product_id = wc_get_product_id_by_sku( $item_update->ItemCode ) || $item_update->ItemCode;
            // determine the woo cart_item_key for this product 
            $cart_item_key = $woo_cart->find_product_in_cart( $product_id );
            // now set the quantity for that line
            $woo_cart->set_quantity( $cart_item_key, $item_update->Qty, $refresh_totals );
        }

        $p4m_discounts = $this->get_p4m_discounts_from_woo_cart_coupons();

        return $p4m_discounts;
    }


    function updateWithDiscountCode( $discountCode ) {

        // fetch and check coupon is valid 
        $woo_coupon = new WC_Coupon( $discountCode );
        if ( !$woo_coupon->exists ) {
            throw new Exception('Unknown discount code : '.$discountCode ); 
        }

        // apply coupon discount to cart
        $woo_cart = WC()->cart;
        $woo_cart->add_discount( $discountCode );
        $woo_cart->calculate_totals();
        
        // now return the P4M discount object
        $dis = new P4M\Model\Discount();
        $dis->Code          = $discountCode;
        $dis->Description   = $discountCode;   // in Woo version 3.0, use $woo_coupon->get_description();
        $dis->Amount        = $woo_cart->get_coupon_discount_amount( $woo_coupon->code );
        $dis->removeNullProperties();

        return $dis;
    }


    function updateRemoveDiscountCode( $discountCode ) {

        // fetch and check coupon is valid 
        $woo_coupon = new WC_Coupon( $discountCode );
        if ( !$woo_coupon->exists ) {
            throw new Exception('Unknown discount code : '.$discountCode ); 
        }

        // apply coupon discount to cart
        $woo_cart = WC()->cart;
        $prev_discount_amount = $woo_cart->get_coupon_discount_amount( $woo_coupon->code );
        $woo_cart->remove_coupon( $discountCode );
        $woo_cart->calculate_totals();
        
        // now return the P4M discount object
        $dis = new P4M\Model\Discount();
        $dis->Code          = $discountCode;
        $dis->Description   = $discountCode;   // in Woo version 3.0, use $woo_coupon->get_description();
        $dis->Amount        = $prev_discount_amount;
        $dis->removeNullProperties();
        
        return $dis;
    }
    

    public function createOrder() {

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

        return $order_id;

    }


    public function completePurchase ( $order_id, $purchase_data ) {

        $p4m_cart           = $purchase_data->Cart; 
        $transactionId      = $purchase_data->Id;
        $transationTypeCode = $purchase_data->TransactionTypeCode; 
        $authCode           = $purchase_data->AuthCode;

        // Prevent timeout
        @set_time_limit(0);

        $order = wc_get_order( $order_id );

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

        $this->addShippingToOrder( $order, WC()->session->get( 'p4m_shipping_amount' ), WC()->session->get( 'p4m_shipping_name' ) );

        $order->calculate_totals();
        $order->payment_complete( $transactionId ); 

        $cart->empty_cart();

        return true;
    }
        

    function addShippingToOrder( $order, $shipping_amount, $shipping_name ) {

        // I spent a lot of time attempting to do this via the shipping method class,
        // but was unable to figure that out, and so this is the shipping solution 

        $shipping_tax = array(); // TO DO when P4M supports it
        $shipping_rate = new WC_Shipping_Rate( '', 'Parcel For Me : '.$shipping_name, $shipping_amount, $shipping_tax, 'p4m_shipping_method' );
        $order->add_shipping($shipping_rate);

        /* v3.0 solution will be something like this :
        // https://docs.woocommerce.com/wc-apidocs/class-WC_Order_Item_Shipping.html
        $woo_shipping = new WC_Order_Item_Shipping();
        $woo_shipping->set_method_title( $shipping_name );
        $woo_shipping->set_method_id( 'p4m_shipping_method' );
        $woo_shipping->set_total( $shipping_amount );

		$order->add_item( $woo_shipping );
        */
        
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