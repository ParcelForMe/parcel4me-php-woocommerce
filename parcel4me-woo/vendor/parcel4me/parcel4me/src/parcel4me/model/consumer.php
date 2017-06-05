<?php

namespace P4M\Model;
require_once 'p4m-model.php';

/*
    
    Consumer
    
    see : http://developer.parcelfor.me/docs/documentation/api-integration/models/consumer/

*/
class Consumer extends P4mModel 
{
    
    public  $id;                      /*	 (read only) Assigned by P4M */
    public  $locale;                  /*	 (read only) Identifies where the consumer's data is stored */
    public  $salutation;              /*	 Mr, Ms, etc */
    public  $givenName;
    public  $middleName;
    public  $familyName;
    public  $email;
    public  $mobilePhone;
    public  $preferredCurrency;       /* "GBP", "EUR", etc */
    public  $language;                /* "en", "fr", "de", etc */
    public  $dob;                     /* date , not string */
    public  $gender;
    public  $height;
    public  $weight;
    public  $waist;
    public  $preferredCarriers;       /* (read only) */
    public  $prefDeliveryAddressId;   /* links to the addresses array below ? */
    public  $billingAddressId;
    public  $defaultPaymentMethodId;
    public  $deliveryPreferences;     /* useMyDeliveryAddress, useMyDropPoints, useRetailerDropPoint */
    public  $preferSoonestDelivery  = false;
    public  $profilePicUrl;
    public  $profilePicHash;          /* Can be used to check if the consumer's profile pic has changed */
    public  $addresses;            //  = [];              /* this is an array,   see : http://developer.parcelfor.me/docs/documentation/api-integration/models/address/ */
    public  $paymentMethods;       //   = [];              /* (read only)  this is an array */
    public  $extras;              //   = [];              /* The Extras field contains a list of key/value pairs specific to the calling Retailer, and is available for them to store any additional information that may be needed */

}


?>