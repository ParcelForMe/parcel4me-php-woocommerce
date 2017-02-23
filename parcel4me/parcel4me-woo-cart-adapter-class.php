<?php 
   /*
   Description: The Parcel4Me Woo Shop is the interface beteen parcel4me and WooCommerce
   Version: 0.0.1
   Author: ParcelForMe
   Author URI: http://parcelfor.me/
   License: MIT
   */

require_once __DIR__.'/vendor/autoload.php';

require_once __DIR__.'/vendor/parcel4me/parcel4me/src/parcel4me/p4m-shop.php';
// i'm not sure why I can't load it like this : require_once __DIR__.'/vendor/autoload.php';
// contributers who know composer autoloader better than me are very welcome to help with that bit. 

class Parcel4me_Woo_Cart_Adapter extends P4M\P4M_Shop {


    /*


  __________     ____  ____      
 /_  __/ __ \   / __ \/ __ \   _ 
  / / / / / /  / / / / / / /  (_)
 / / / /_/ /  / /_/ / /_/ /  _   
/_/  \____/  /_____/\____/  (_)  
                                 

    */

    function userIsLoggedIn() {
        //return false;
        return true;
    }

    function createNewUser( $p4m_consumer ) {
        /*
            logic here to create a new user record
            in the shopping cart database
        */
        $user = new stdClass();
        $user->first = 'First';
        $user->last  = 'Last';
        $user->email = 'new_person@mailinator.com';
        $user->id    = 1234567;

        return $user;
    }

    function loginUser( $localUserId ) {
        /*
            logic to log the user out of the shopping cart 
        */
        return true;
    }

    function logoutCurrentUser() {
        /*
            logic to logout the current user from the shopping cart 
        */
        return true;
    }

    function setCurrentUserDetails( $p4m_consumer ) {
        /* 
            logic to copy fields from the p4m_consumer onto the current local user 
        */
        return true;
    }
    

    function getCurrentUserDetails() {
        /* 
            some logic goes here to fetch the 
            details of the current user 
        */
        $user = new stdClass();
        $user->first = 'First';
        $user->last  = 'Last';
        $user->email = 'new_person@mailinator.com';

        
        $p4m_address = new P4M\Model\Address();
        $p4m_address->AddressType   = 'Address';
        $p4m_address->Street1       = '21 Pine Street';
        $p4m_address->State         = 'Qld';
        $p4m_address->CountryCode   = 'AU';
        $p4m_address->removeNullProperties();

        // Convert the user from the shopping cart DB into a 
        // P4M Consumer
        $consumer = new P4M\Model\Consumer();
        $consumer->GivenName  = $user->first;
        $consumer->FamilyName = $user->last;
        $consumer->Email      = $user->email;
        $consumer->Addresses  = array ( $p4m_address ); 
        $consumer->removeNullProperties();

        return $consumer;
    }

    function getCartOfCurrentUser() {
        /*
            some logic goes here to fetch my cart from 
            my shopping cart DB and put the details into 
            this $cart object 
        */

        // Convert the shopping cart from the shopping cart DB into a 
        // P4M Cart

        $cartItem = new P4M\Model\CartItem();
        $cartItem->Desc         = "A great thing I am buying";
        $cartItem->Qty          = 1;
        $cartItem->Price        = 100;
        $cartItem->LinkToImage  = "http://cdn2.wpbeginner.com/wp-content/uploads/2015/12/pixabay.jpg";
        $cartItem->removeNullProperties();

        $cart = new P4M\Model\Cart();
        $cart->SessionId    = $this->getCurrentSessionId();
        $cart->PaymentType  = "DB";
        $cart->Items        = [ $cartItem ];
        $cart->Currency     = "USD";
        $cart->Reference    = "12345"; //.rand(); // This is REQUIRED (and needs to change for subsequent Paypal payments)
        $cart->removeNullProperties();

        return $cart;
    }


    function setCartOfCurrentUser( $p4m_cart ) {
        /* 
            some logic goes here to set local shopping cart DB
            based on the passed in p4m shopping cart object 
        */

        return true;
    }
    

    function setAddressOfCurrentUser( $which_address, $p4m_address ) {
        /*
            logic here to find the address in the local DB
            and update it, or add if not exists
        */

        return true;
    }


    function updateShipping( $shippingServiceName, $amount, $dueDate ) {
        /*
            some logic goes here to set these shipping amounts and 
            possibly recalculate the tax on the current shopping cart 
        */

        return true;
    }


    function getCartTotals() {
        /*
            some logic goes here to fetch these values from
            the current shopping cart 
        */

        $r = new stdClass();
        $r->Tax      = 10.00;
        $r->Shipping = 20.00;
        $r->Discount = 5.00;
        $r->Total    = 112.22;

        return $r;
    }


    function updateWithDiscountCode( $discountCode ) {
        /* 
            some logic goes here to check if this discount code is valid,
            if not throw an error, if so then apply it to the cart and return the discount details 
        */

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
    

        public function updateCartItemQuantities( $itemsUpdateArray ) {
            /*
            some logic to update the quantities on the cart lines
        */

        $dis = new P4M\Model\Discount();
        $dis->Code           = 'valid_code';
        $dis->Description    = 'A demo valid coupon code!';
        $dis->Amount         = 0.01;

        $disArray = [ $dis ];

        return $disArray;
        }


        public function completePurchase ( $p4m_cart, $transactionId, $transationTypeCode, $authCode ) {
            /*
            some logic to update the db to show that the purchase has happened and clear the cart 
        */

            return true;
        }
        

    function handleError($message) {
        $error_url = 'http://' . $_SERVER['HTTP_HOST'] . '/error/' . urlencode($message);
        header("Location: {$error_url}");
        exit();
    }


}
?>