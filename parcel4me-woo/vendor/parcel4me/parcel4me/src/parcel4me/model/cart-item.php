<?php

namespace P4M\Model;
require_once 'p4m-model.php';

/* 

    CartItem

    see : http://developer.parcelfor.me/docs/documentation/api-integration/models/cartitem/

*/
class CartItem extends P4mModel
{

    public  $CartId;                /* (read only) */
    public  $LineId;                /* must be unique for this item within the cart. If not assigned it will be assigned by P4M */
    public  $Make;
    public  $Sku;
    public  $Desc;
    public  $Qty;
    public  $Price;
    public  $LinkToImage;
    public  $LinkToItem;
    public  $Tags;
    public  $Rating;
    public  $SiteReference;         /* can be used by the Retailer to hold information specific to the Retailer */
    public  $Options;               /* a list of options that the consumer may have selected when adding the item to the cart */

}

?>