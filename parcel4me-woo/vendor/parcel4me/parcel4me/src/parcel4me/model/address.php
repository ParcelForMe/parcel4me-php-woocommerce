<?php

namespace P4M\Model;
require_once 'p4m-model.php';

/* 

    Address

    see : http://developer.parcelfor.me/docs/documentation/api-integration/models/address/

*/
class Address extends P4mModel
{

    public  $consumerId;            /* (read only)	 */
    public  $id;                    /*  must be unique for each address for the consumer. If not assigned when added it will be assigned by P4M */
    public  $addressType;           /*		"Address" or "Collect" */
    public  $label;                 /*		e.g. Home, Work, etc */
    public  $companyName;	 	 
    public  $street1;	 	 
    public  $street2;	 	 
    public  $city;	 	 
    public  $postCode;	 	 
    public  $state;	 	 
    public  $country;	 	 
    public  $countryCode;            /* ISO country code e.g. "UK", "US", "FR", etc */
    public  $contact;               /*	Name of best contact person at address */
    public  $phone;                 /* Phone at address or mobile of contact person */
    public  $latitude;	   
    public  $longitude;	 
    public  $dropPointProviderId;   /*	Integer (read only)	Assigned */
    public  $dropPointId;           /* 	 (read only)	Assigned */
    public  $collectPrefOrder;      /*	(should be read only) Integer	Stores the preferred order for "Collect" addresses */

}

?>