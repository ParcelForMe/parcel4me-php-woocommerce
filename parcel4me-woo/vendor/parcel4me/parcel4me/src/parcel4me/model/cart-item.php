<?php

namespace P4M\Model;
require_once 'p4m-model.php';

/* 

    CartItem

    see : http://developer.parcelfor.me/docs/documentation/api-integration/models/cartitem/

*/
class CartItem extends P4mModel
{

    public  $cartId;                /* (read only) */
    public  $lineId;                /* must be unique for this item within the cart. If not assigned it will be assigned by P4M */
    public  $make;
    public  $sku;
    public  $desc;
    public  $qty;
    public  $price;
    public  $linkToImage;
    public  $linkToItem;
    public  $tags;
    public  $rating;
    public  $siteReference;         /* can be used by the Retailer to hold information specific to the Retailer */
    public  $options;               /* a list of options that the consumer may have selected when adding the item to the cart */

}

?>