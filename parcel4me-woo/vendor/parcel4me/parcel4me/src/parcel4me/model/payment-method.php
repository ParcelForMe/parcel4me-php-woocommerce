<?php

namespace P4M\Model;
require_once 'p4m-model.php';

/* 

    Discount

    see : http://developer.parcelfor.me/docs/documentation/api-integration/models/discount/
    
*/
class PaymentMethod extends P4mModel
{

    public  $Id;                    /* Token identifying the card */
    public  $AccountType;           /* "Card", "BankAccount" */
    public  $Issuer;                /* Visa, Mastercard, Amex, etc. */
    public  $Name;                  /* name on the card */
    public  $Description;           /* string showing some card digits */
    public  $MoreDetail;            /* expiry details*/

}

?>