<?php

namespace P4M\Model;
require_once 'p4m-model.php';

/* 

    Address

    see : http://developer.parcelfor.me/docs/documentation/api-integration/models/address/

*/
class Address extends P4mModel
{

    public  $ConsumerId;            /* (read only)	 */
    public  $Id;                    /*  must be unique for each address for the consumer. If not assigned when added it will be assigned by P4M */
    public  $AddressType;           /*		"Address" or "Collect" */
    public  $Label;                 /*		e.g. Home, Work, etc */
    public  $CompanyName;	 	 
    public  $Street1;	 	 
    public  $Street2;	 	 
    public  $City;	 	 
    public  $PostCode;	 	 
    public  $State;	 	 
    public  $Country;	 	 
    public  $CountryCode;            /* ISO country code e.g. "UK", "US", "FR", etc */
    public  $Contact;               /*	Name of best contact person at address */
    public  $Phone;                 /* Phone at address or mobile of contact person */
    public  $Latitude;	   
    public  $Longitude;	 
    public  $DropPointProviderId;   /*	Integer (read only)	Assigned */
    public  $DropPointId;           /* 	 (read only)	Assigned */
    public  $CollectPrefOrder;      /*	(should be read only) Integer	Stores the preferred order for "Collect" addresses */

}

?>