# [parcel4me-php](../README.md) : Interface

parcel4me-php implements the Host Server methods required by the Parcel4Me Widgets.    
<a href="http://developer.parcelfor.me/docs/documentation" target="_blank">See the documentation</a>.

## Purpose

To facilitate implementing *<a href="http://parcelfor.me/" target="_blank"> Parcel4Me</a>'s* one-click checkout and delivery into an existing PHP shopping cart.




## Usage (how to modify an existing PHP shopping cart)


To bring the Parcel4Me functionality into an existing shopping cart 3 steps are required :

1. `require 'parcel4me/p4m-shop.php'` and implement the `P4M\P4M_Shop` abstract class, which means coding each of the methods listed in [p4m-shop-interface](p4m-shop-interface.php)

2. add the Parcel4Me UI widgets into your shopping cart in the approprate places

3. to accept all of the required `p4m/*` API endpoints :   
   *(each of which has a corresponding function already implemented in the P4M_Shop)*

> #### API endpoints to receive on your router
> ##### p4m-login Widget
> 
> * GET  p4m/getP4MAccessToken
> * GET  p4m/isLocallyLoggedIn
> * GET  p4m/localLogin
> * GET  p4m/restoreLastCart
> 
> ##### p4m-checkout Widget
> 
> * GET  p4m/checkout
> * GET  p4m/getP4MCart
> * POST p4m/updShippingService
> * GET  p4m/applyDiscountCode
> * GET  p4m/removeDiscountCode
> * POST p4m/itemQtyChanged
> * POST p4m/purchase
> * GET  p4m/paypalSetup
> * GET  p4m/paypalCancel
> * GET  p4m/purchaseComplete
> 
> ##### p4m-register Widget
> 
> * GET  p4m/signup


## Demo Example

See the [basic demo implementation](../basic-demo/README.md)

