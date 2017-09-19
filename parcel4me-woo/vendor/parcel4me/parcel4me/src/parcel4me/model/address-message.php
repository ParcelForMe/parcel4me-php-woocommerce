<?php

namespace P4M\Model;
require_once 'p4m-model.php';

/* 

    Address Message

    see : hhttp://developer.parcelfor.me/docs/documentation/api-integration/models/addressmessage/

*/
class AddressMessage extends P4mModel
{

    public  $address;               /* an "Address" object */
    public  $isPrefDeliveryAddr;    /* Boolean	 True if this is the new preferred delivery address */
    public  $isBillingAddr;         /* Boolean	 True if this is the new preferred delivery address */

}

?>