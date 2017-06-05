<?php

namespace P4M\Model;
require_once 'p4m-model.php';

/* 

    Address Message

    see : hhttp://developer.parcelfor.me/docs/documentation/api-integration/models/addressmessage/

*/
class AddressMessage extends P4mModel
{

    public  $Address;               /* an "Address" object */
    public  $IsPrefDeliveryAddr;    /* Boolean	 True if this is the new preferred delivery address */
    public  $IsBillingAddr;         /* Boolean	 True if this is the new preferred delivery address */

}

?>