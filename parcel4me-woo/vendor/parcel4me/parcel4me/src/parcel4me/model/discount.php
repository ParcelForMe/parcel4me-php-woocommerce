<?php

namespace P4M\Model;
require_once 'p4m-model.php';

/* 

    Discount

    see : http://developer.parcelfor.me/docs/documentation/api-integration/models/discount/

*/
class Discount extends P4mModel
{

    public  $CartId;                /* (read only) */
    public  $Code;
    public  $Description;
    public  $Amount;
    
}

?>