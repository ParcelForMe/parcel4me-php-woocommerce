<?php

namespace P4M\Model;
require_once 'p4m-model.php';

/* 

    Post Purchase Message

    see : http://developer.parcelfor.me/docs/documentation/api-integration/models/postpurchasemessage/

*/
class PostPurchaseMessage extends P4mModel
{

    public  $CartId;
    public  $CVV;
    public  $NewDropPoint;          /* (optional) Address object (This Address object must be of type "Collect") */

}

?>