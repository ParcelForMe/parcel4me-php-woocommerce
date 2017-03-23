<?php

namespace P4M\Model;
require_once 'p4m-model.php';

/* 

    WishListItem

    see : http://developer.parcelfor.me/docs/documentation/api-integration/models/wishlistitem/

*/
class WishListItem extends P4mModel
{

    public  $ConsumerId;            /* (read only) */
    public  $RetailerId;            /* (read only) */
    public  $RetailerName;          /* (read only) */
    public  $Date;                  /* (read only) */
    public  $Currency;              /* ISO currency code */
    public  $Make; 
    public  $Sku;
    public  $Desc;
    public  $Price;
    public  $LinkToImage;
    public  $LinkToItem;
    public  $Tags;                  /* Product categories (comma separated) */
    public  $SiteReference;         /* can be used by the Retailer to hold information specific to the Retailer */
    public  $Options;               /* (key=value) a list of options that the consumer may have selected when adding the item to the cart */

}

?>