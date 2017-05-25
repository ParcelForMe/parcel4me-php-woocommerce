<?php

namespace P4M\Model;
require_once 'p4m-model.php';

/* 

    Discount

    see : http://developer.parcelfor.me/docs/documentation/api-integration/models/discount/

*/
class Discount extends P4mModel
{

    public  $cartId;                /* (read only) */
    public  $code;
    public  $description;
    public  $amount;
    
}

?>