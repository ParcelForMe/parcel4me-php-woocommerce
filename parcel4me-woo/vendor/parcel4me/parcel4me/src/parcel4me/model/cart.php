<?php

namespace P4M\Model;
require_once 'p4m-model.php';

/* 

    Cart

    see : http://developer.parcelfor.me/docs/documentation/api-integration/models/cart/

*/
class Cart extends P4mModel
{

    public  $ConsumerId;              /* (read only) */
    public  $Id;                      /* (read only) */
    public  $SessionId;               /* Consumer's session Id on retailer's site */
    public  $RetailerId;              /* (read only) */
    public  $RetailerName;            /* (read only) */
    public  $Reference;               /* Retailer reference (usually order no.) */
    public  $AddressId;               /* must be the Id of an existing consumer address or collection point, unless the consumer has selected a new collection point, in which case calls to the API must indicate this, and the new collection point details must be passed during the purchase call */
    public  $BillingAddressId;        /* must be the Id of an existing consumer address, not a collection point */
    public  $Date;                    /* UTC date */ 
    public  $Currency;
    public  $ShippingAmt            = 0.0;
    public  $Tax                    = 0.0;
    public  $Total                  = 0.0;
    public  $ServiceId;
    public  $ServiceName;             /* e.g. standard, next day, etc */
    public  $ExpDeliveryDate;
    public  $DateDelivered;
    public  $Carrier;
    public  $ConsignmentId;
    public  $CarrierToken;            /* Used to grant access to the carrier of the delivery */
    public  $Status;                  /* Ordered, Despatched, etc */
    public  $RetailerRating         = 0;
    public  $CarrierRating          = 0;
    public  $PaymentType;             /* set to "DB" (Debit) to collect payment with purchase. Set to "PA" (payment authorisation) to authorise the purchase only, in which case the payment must be processed later via a back office "capture". */
    public  $PayMethodId;             /* The selected card token used for payment */
    public  $PaymentId;               /* (read only)	 P4M transaction Id */
    public  $AuthCode;                /* (read only)	 Used in back office transactions */
    public  $PurchaseConfirmedTS;     /* (read only)	 Date and time purchased was confirmed by the PSP */
    public  $Items;                 // = [];	                /* List (CartItems) */
    public  $Dicounts;              // = [];                /* List (Discounts)	*/

}

?>