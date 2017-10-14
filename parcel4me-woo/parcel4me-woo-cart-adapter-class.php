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
        $username = $p4m_consumer->email;
        $password = wp_generate_password();
        $user_data = array(
                        'user_login'   => $username,
                        'user_pass'    => $password,
                        'user_email'   => $p4m_consumer->email,
                        'first_name'   => $p4m_consumer->givenName,
                        'nickname'     => $p4m_consumer->givenName,
                        'display_name' => $p4m_consumer->givenName,
                        'last_name'    => $p4m_consumer->familyName
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

    function doWooLogout() 
    {
        // empty cart
        $cart = WC()->cart;
        $cart->empty_cart();
        // and redirect to woo logout page
        header("Location: /my-account/customer-logout");
        exit();
    }


    function setCurrentUserDetails( $p4m_consumer ) 
    {

        // update name and email of wordpress user
        $wp_user = wp_get_current_user();
        $wp_user->user_email        = $p4m_consumer->email;
        $wp_user->first_name        = $p4m_consumer->givenName;
        $wp_user->last_name         = $p4m_consumer->familyName;
        $wp_user->first_name        = $p4m_consumer->givenName;
        $wp_user->nickname          = $p4m_consumer->givenName;
        $wp_user->display_name      = $p4m_consumer->givenName;
        $wp_user->last_name         = $p4m_consumer->familyName;
        
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
        $consumer->givenName  = $wp_user->first_name;
        $consumer->familyName = $wp_user->last_name;
        $consumer->email      = $wp_user->user_email;

        if ( ( !property_exists( $consumer, 'extras' ) ) ||
             ( null == $consumer->extras ) )
        {
            $consumer->extras  = new stdClass();
        }  

        if ( !property_exists( $consumer->extras, 'localId' ) ) {
            $consumer->extras->localId = $wp_user->ID;
        }

        // If the user has a billing or shipping address then add these to the P4M consumer

        $billing_address = $this->get_address_for_p4m( 'billing' );
        $shipping_address = $this->get_address_for_p4m( 'shipping' );

        $consumer->addresses = [];
        $address_index = 0;

        if ( $billing_address ) {
            $consumer->addresses[] = $billing_address;
            $consumer->billingAddressId = $address_index;
            $address_index++;
        }

        if ( $shipping_address ) {
            $consumer->addresses[] = $shipping_address;
            $consumer->prefDeliveryAddressId = $address_index;
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
            $address->addressType = 'Address';
            $address->street1     = $woo_customer->get_address();
            $address->street2     = $woo_customer->get_address_2();
            $address->city        = $woo_customer->get_city();
            $address->postCode    = $woo_customer->get_postcode();
            $address->state       = $woo_customer->get_state();
            $address->countryCode = $woo_customer->get_country();

        } elseif ( 'shipping' == $address_type ) {

            // Business Rule - the address MUST have a country code to be valid
            $country = $woo_customer->get_shipping_country();
            if (!country) return false;

            // and if we have a country code then we create an address with whatever details we have
            $address = new P4M\Model\Address();
            $address->addressType = 'Address';
            $address->street1     = $woo_customer->get_shipping_address();
            $address->street2     = $woo_customer->get_shipping_address_2();
            $address->city        = $woo_customer->get_shipping_city();
            $address->postCode    = $woo_customer->get_shipping_postcode();
            $address->state       = $woo_customer->get_shipping_state();
            $address->countryCode = $woo_customer->get_shipping_country();

        } else {
            throw new Exception('Unknown address type : '.$address_type);
        }
       

        /* WooCommerce v3.0 Code (much neater!) :
        // Business Rule - the address MUST have a country code to be valid
        $country = $woo_customer->get_address_prop( 'country', $address_type );
        if (!$country) return false;

        // and if we have a country code then we create an address with whatever details we have
        $address = new P4M\Model\Address();
        $address->addressType = 'Address';
        $address->companyName = $woo_customer->get_address_prop( 'company', $address_type );
        $address->street1     = $woo_customer->get_address_prop( 'address_1', $address_type );
        $address->street2     = $woo_customer->get_address_prop( 'address_2', $address_type );
        $address->city        = $woo_customer->get_address_prop( 'city', $address_type );
        $address->postCode    = $woo_customer->get_address_prop( 'postcode', $address_type );
        $address->state       = $woo_customer->get_address_prop( 'state', $address_type );
        $address->countryCode = $woo_customer->get_address_prop( 'country', $address_type );
        $address->phone       = $woo_customer->get_address_prop( 'phone', $address_type );
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
            $cartItem->make         = $woo_item['data']->post->post_name;
            $cartItem->sku          = $product_sku;
            $cartItem->desc         = $woo_item['data']->post->post_title;
            $cartItem->qty          = $woo_item['quantity'];
            $cartItem->price        = (double)$woo_item['data']->price;
            $cartItem->linkToImage  = ( (has_post_thumbnail( $woo_item['data']->post->ID )) ? (wp_get_attachment_image_src( get_post_thumbnail_id( $woo_item['data']->post->ID ), 'single-post-thumbnail' )[0]) : null );
            $cartItem->linkToItem   = get_permalink( $woo_item['data']->post->ID );
            $cartItem->lineId       = $woo_item['p4m_line_id'];
            $cartItem->removeNullProperties();    
            $items[] = $cartItem;
        }


        // and then the cart object 

        $generated_reference = spl_object_hash( $woo_cart );

        $cart = new P4M\Model\Cart();
        $cart->sessionId    = $this->getCurrentSessionId();
        $cart->reference    = $generated_reference; 
        $cart->date         = gmdate( "D, d M Y T" );
        $cart->currency     = get_woocommerce_currency();
        $cart->shippingAmt  = $woo_cart->shipping_total;
        $cart->tax          = $woo_cart->tax_total;
        $cart->total        = $woo_cart->total;
        $cart->paymentType  = "DB"; // TODO : NOT YET READY : this needs more throught first before implementing
        $cart->discounts    = $this->get_p4m_discounts_from_woo_cart_coupons();
        $cart->items        = $items;
        $cart->removeNullProperties();

        return $cart;
    }


    function setCartOfCurrentUser( $p4m_cart ) {

        $woo_cart = WC()->cart;

        $woo_cart->empty_cart();

        // add in all the order lines
        foreach( $p4m_cart->items as $p4m_item ) {
            $woo_cart->add_to_cart( $p4m_item->sku, $p4m_item->qty,
                                    0, null,
                                    array( 'p4m_line_id'=>$p4m_item->lineId ) );
        }

        // and apply the approprate discounts
        foreach( $p4m_cart->discounts as $p4m_disc ) {
            $woo_cart->remove_coupon( $p4m_disc->code );
            $woo_cart->add_discount( $p4m_disc->code );
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
        $woo_customer->set_shipping_location( $address->countryCode,
                                              $address->state, 
                                              $address->postCode, 
                                              $address->city );
        $woo_customer->set_shipping_address( $address->street1 );
        $woo_customer->set_shipping_address_2( $address->street2 );
        $woo_customer->save_data();

        return true;
    }


    function getCartTotals() {

        $woo_cart = WC()->cart;

        $woo_cart->calculate_totals();
        
        $r = new stdClass();
        $r->tax      = $woo_cart->tax_total;
        $r->shipping = WC()->session->get( 'p4m_shipping_amount' );
        $r->discount = $woo_cart->get_cart_discount_total();
        $r->total    = $woo_cart->cart_contents_total + $r->shipping + $r->tax;

        //error_log(' getCartTotal() = '.json_encode($r));

        return $r;
    }

    function get_p4m_discounts_from_woo_cart_coupons() {
        $woo_cart = WC()->cart;
        $p4m_discounts = [];
        $woo_coupons = $woo_cart->get_coupons();
        foreach($woo_coupons as $woo_coupon) {
            $dis = new P4M\Model\Discount();
            $dis->code           = $woo_coupon->code;
            $dis->description    = $woo_coupon->code;
            $dis->amount         = $woo_cart->get_coupon_discount_amount( $woo_coupon->code );
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
            $product_id = wc_get_product_id_by_sku( $item_update->itemCode ) || $item_update->itemCode;
            // determine the woo cart_item_key for this product 
            $cart_item_key = $woo_cart->find_product_in_cart( $product_id );
            // now set the quantity for that line
            $woo_cart->set_quantity( $cart_item_key, $item_update->qty, $refresh_totals );
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
        $dis->code          = $discountCode;
        $dis->description   = $discountCode;   // in Woo version 3.0, use $woo_coupon->get_description();
        $dis->amount        = $woo_cart->get_coupon_discount_amount( $woo_coupon->code );
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
        $dis->code          = $discountCode;
        $dis->description   = $discountCode;   // in Woo version 3.0, use $woo_coupon->get_description();
        $dis->amount        = $prev_discount_amount;
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

        // we can actually empty out the shopping cart here
        $cart = WC()->cart;
        $cart->empty_cart();


        return $order_id;
        
    }


    public function completePurchase ( $order_id, $purchase_data ) {

        $p4m_cart           = $purchase_data->cart; 
        $transactionId      = $purchase_data->id;
        $transationTypeCode = $purchase_data->transactionTypeCode; 
        $authCode           = $purchase_data->authCode;

        // Prevent timeout
        @set_time_limit(0);

        $order = wc_get_order( $order_id );

        if (property_exists($purchase_data, 'deliverTo') && $purchase_data->deliverTo) {
            $p4m_ad = $purchase_data->deliverTo;
            $address = array(
                'first_name' => $wp_user->first_name,
                'last_name'  => $wp_user->last_name,
                'company'    => $p4m_ad->companyName,
                'email'      => $wp_user->email,
                'phone'      => $p4m_ad->phone,
                'address_1'  => $p4m_ad->street1,
                'address_2'  => $p4m_ad->street2, 
                'city'       => $p4m_ad->city,
                'state'      => $p4m_ad->state,
                'postcode'   => $p4m_ad->postCode,
                'country'    => $p4m_ad->countryCode
            );
            $order->set_address( $address, 'shipping' );

            // set billTo address to the same if it is null
            if ( property_exists($purchase_data, 'billTo') && (null==$purchase_data->billTo) ) {
                $order->set_address( $address, 'billing' );
            }

        }

        if (property_exists($purchase_data, 'billTo') && $purchase_data->billTo) {
            $p4m_ad = $purchase_data->billTo;
            $address = array(
                'first_name' => $wp_user->first_name,
                'last_name'  => $wp_user->last_name,
                'company'    => $p4m_ad->companyName,
                'email'      => $wp_user->email,
                'phone'      => $p4m_ad->phone,
                'address_1'  => $p4m_ad->street1,
                'address_2'  => $p4m_ad->street2, 
                'city'       => $p4m_ad->city,
                'state'      => $p4m_ad->state,
                'postcode'   => $p4m_ad->postCode,
                'country'    => $p4m_ad->countryCode
            );
            $order->set_address( $address, 'billing' );
        }

        $this->addShippingToOrder( $order, WC()->session->get( 'p4m_shipping_amount' ), WC()->session->get( 'p4m_shipping_name' ) );

        $order->calculate_totals();
        $order->payment_complete( $transactionId ); 

        $cart = WC()->cart;
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