<?php

namespace P4M\Model;
require_once 'p4m-model.php';

/* 

    Cart

    see : http://developer.parcelfor.me/docs/documentation/api-integration/models/cart/

*/
class Cart extends P4mModel
{

    public  $consumerId;              /* (read only) */
    public  $id;                      /* (read only) */
    public  $sessionId;               /* Consumer's session Id on retailer's site */
    public  $retailerId;              /* (read only) */
    public  $retailerName;            /* (read only) */
    public  $reference;               /* Retailer reference (usually order no.) */
    public  $addressId;               /* must be the Id of an existing consumer address or collection point, unless the consumer has selected a new collection point, in which case calls to the API must indicate this, and the new collection point details must be passed during the purchase call */
    public  $billingAddressId;        /* must be the Id of an existing consumer address, not a collection point */
    public  $date;                    /* UTC date */ 
    public  $currency;
    public  $shippingAmt            = 0.0;
    public  $tax                    = 0.0;
    public  $total                  = 0.0;
    public  $serviceId;
    public  $serviceName;             /* e.g. standard, next day, etc */
    public  $expDeliveryDate;
    public  $dateDelivered;
    public  $carrier;
    public  $consignmentId;
    public  $carrierToken;            /* Used to grant access to the carrier of the delivery */
    public  $status;                  /* Ordered, Despatched, etc */
    public  $retailerRating         = 0;
    public  $carrierRating          = 0;
    public  $paymentType;             /* set to "DB" (Debit) to collect payment with purchase. Set to "PA" (payment authorisation) to authorise the purchase only, in which case the payment must be processed later via a back office "capture". */
    public  $payMethodId;             /* The selected card token used for payment */
    public  $paymentId;               /* (read only)	 P4M transaction Id */
    public  $authCode;                /* (read only)	 Used in back office transactions */
    public  $purchaseConfirmedTS;     /* (read only)	 Date and time purchased was confirmed by the PSP */
    public  $items;                 // = [];	                /* List (CartItems) */
    public  $discounts;             // = [];                /* List (Discounts)	*/

}

?>