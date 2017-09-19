<?php

namespace P4M\Model;
require_once 'p4m-model.php';

/* 

    WishListItem

    see : http://developer.parcelfor.me/docs/documentation/api-integration/models/wishlistitem/

*/
class WishListItem extends P4mModel
{

    public  $consumerId;            /* (read only) */
    public  $retailerId;            /* (read only) */
    public  $retailerName;          /* (read only) */
    public  $date;                  /* (read only) */
    public  $currency;              /* ISO currency code */
    public  $make; 
    public  $sku;
    public  $desc;
    public  $price;
    public  $linkToImage;
    public  $linkToItem;
    public  $tags;                  /* Product categories (comma separated) */
    public  $siteReference;         /* can be used by the Retailer to hold information specific to the Retailer */
    public  $options;               /* (key=value) a list of options that the consumer may have selected when adding the item to the cart */

}

?>