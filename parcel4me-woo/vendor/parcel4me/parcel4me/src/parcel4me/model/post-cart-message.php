<?php

namespace P4M\Model;
require_once 'p4m-model.php';

/* 

    Post Cart Message

    see : http://developer.parcelfor.me/docs/documentation/api-integration/models/postcartmessage/

*/
class PostCartMessage extends P4mModel
{

    public  $Cart;                  /* a "Cart" object */
    public  $SessionId;             /* Consumer's session on the retailer's site */
    public  $ClearItems;            /* Boolean	 Clear existing cart items before adding new ones */
    public  $DeliverToNewDropPoint; /* Boolean	 Allows the Cart's address Id to be blank or non-existent */

}

?>