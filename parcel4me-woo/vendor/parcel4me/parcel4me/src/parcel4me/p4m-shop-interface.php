<?php

namespace P4M;

interface P4M_Shop_Interface {
    
    /**
        return currently logged on user if the user is logged into the shopping cart system, else false
    */
    public function userIsLoggedIn();


    /**
        create a new local user in the shopping cart DB and return the new local user object, else throw error
        (it is expected that the new user id will be on the returned object as ->id)
    */
    public function createNewUser( $p4m_consumer );


    /**
        return true if it is a valid user id and false if not 
    */
    public function isValidUserId( $localUserId );


    /**
        return local user record if found with this email address 
        else return false 
    */
    public function fetchLocalUserByEmail( $localUserEmailAddress );


    /**
        do the local (ie. as per shopping cart logic) login process for this user 
    */
    public function loginUser( $localUserId );


    /**
        do the local logout process for the currently logged on user
    */
    public function logoutCurrentUser();


    /**
        return a populated consumer JSON object as defined by the model :
        http://developer.parcelfor.me/docs/documentation/api-integration/models/consumer/
    */
    public function getCurrentUserDetails();


    /**
        set the local user details based on the p4m consumer object 
    */
    public function setCurrentUserDetails( $p4m_consumer );


    /**
        return the users current shopping cart as a JSON object as defined here :
        http://developer.parcelfor.me/docs/documentation/api-integration/models/cart/
    */
    public function getCartOfCurrentUser();


    /**
        set the local shopping cart based on the p4m shopping cart details, passed in this format:
        http://developer.parcelfor.me/docs/documentation/api-integration/models/cart/
    */
    public function setCartOfCurrentUser( $p4m_cart );


    /**
        update the shipping and tax on the current local cart   
    */
    public function updateShipping( $shippingServiceName, $amount, $dueDate, $address );


    /**
        return an object with the following fields from the current local cart :
            ->tax
            ->shipping 
            ->discount 
            ->total
    */
    public function getCartTotals();


    /**
        apply this discount/coupon code and update the totals on the local cart
        return discount details object, which includes
            ->code (same as passed in)
            ->description 
            ->amount 
        (if the coupon code is not valid, throw an exception)
    */
    public function updateWithDiscountCode( $discountCode );


    /**
        remove the discount code and update the totals on the local cart 
        return discount details object
    */
    public function updateRemoveDiscountCode( $discountCode );


    /**
        pass in an array of item codes and new quantities,
        like this :
             [ {"itemCode": "item1", "qty": 10.12}, {"itemCode": "item2", "qty": 12.34}, ...]
        return an array of "Discount"s
    */
    public function updateCartItemQuantities( $itemsUpdateArray );

    
    /**
        create the local order and return the order id
    */
    public function createOrder();
    

    /**
        close the cart and do any other required processing
        Important properties of the $purchase_data :
            ->cart	The P4M Cart including items and discounts
            ->id	The transaction Id
            ->transactionTypeCode	"DB" debit, "PA" payment authorization
            ->authCode	PSP authorization code used for refunds, etc
            ->deliverTo   P4M Address for delivery
            ->billTo      P4M Address for billing
    */
    public function completePurchase ( $order_id, $purchase_data );


    /**
        handle the error - returned as a string
    */
    public function handleError( $message );



    /**

        NOTE : your implementation should call $this->updateCaCertificateIfChanged() ~ monthly

    */

}


?>