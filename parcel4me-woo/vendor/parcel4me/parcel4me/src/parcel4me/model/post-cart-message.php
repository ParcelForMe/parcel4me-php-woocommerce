<?php

namespace P4M\Model;
require_once 'p4m-model.php';

/* 

    Post Cart Message

    see : http://developer.parcelfor.me/docs/documentation/api-integration/models/postcartmessage/

*/
class PostCartMessage extends P4mModel
{

    public  $cart;                  /* a "Cart" object */
    public  $sessionId;             /* Consumer's session on the retailer's site */
    public  $clearItems;            /* Boolean	 Clear existing cart items before adding new ones */
    public  $deliverToNewDropPoint; /* Boolean	 Allows the Cart's address Id to be blank or non-existent */

}

?>