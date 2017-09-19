<?php

namespace P4M\Model;
require_once 'p4m-model.php';

/* 

    Discount

    see : http://developer.parcelfor.me/docs/documentation/api-integration/models/discount/
    
*/
class PaymentMethod extends P4mModel
{

    public  $id;                    /* Token identifying the card */
    public  $accountType;           /* "Card", "BankAccount" */
    public  $issuer;                /* Visa, Mastercard, Amex, etc. */
    public  $name;                  /* name on the card */
    public  $description;           /* string showing some card digits */
    public  $moreDetail;            /* expiry details*/

}

?>