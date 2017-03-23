# Parcel4me (PHP)


This package adds global one-click checkout and delivery to a PHP shopping cart using Parcel4Me.

![](http://parcelfor.me/images/site/logo--horizontal.svg)

It is a reusable un-opinionated interface that can be used with any existing PHP shopping cart.    



## Existing Implementations

These working implementations demonstrate how this Parcel For Me package can be used to intergrate Parcel For Me into an existing PHP Shopping Cart.

* **Bare-bones demo implementation**  
  [https://github.com/ParcelForMe/parcel4me-php-basedemo](https://github.com/ParcelForMe/parcel4me-php-basedemo)    

* **Wordpress Plugin "ParcelForMe for WooCommerce"**    
  [https://github.com/ParcelForMe/parcel4me-php-woocommerce](https://github.com/ParcelForMe/parcel4me-php-woocommerce)

* **Magento Plugin**    
  [https://github.com/ParcelForMe/parcel4me-php-magento](https://github.com/ParcelForMe/parcel4me-php-magento)
  


## Installation

### Add the package ([&nearr;](https://packagist.org/packages/parcel4me/parcel4me))

    $ composer require parcel4me/parcel4me

*Tested working on PHP version 5.6.7.*



## Usage (how to modify an existing PHP shopping cart)

    
### Implementation

To bring the Parcel4Me functionality into an existing shopping cart 3 steps are required :

1. Require the composer autoloader :

	`//require_once __DIR__.'/vendor/autoload.php';`
	`require_once __DIR__.'/vendor/parcel4me/parcel4me/src/parcel4me/p4m-shop.php';`

    and implement the `P4M\P4M_Shop` abstract class, which means coding each of the methods listed in `p4m-shop-interface.php`.

2. add the Parcel4Me UI widgets into your shopping cart in the approprate places    
     (in the near future these will be available via a CDN)

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
> * GET  p4m/checkout (or differently named endpoint)
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




## About 

This package implements the Host Server methods required by the Parcel4Me Widgets (<a href="http://developer.parcelfor.me/docs/documentation" target="_blank">See the documentation</a>).  It is implemented as an "[Abstract Class](http://php.net/manual/en/language.oop5.abstract.php)" (and "[Interface](http://php.net/manual/en/language.oop5.interfaces.php)") package that can be used for connecting any PHP shopping cart. 

### For more information about parcel4me, see the website : <a href="http://parcelfor.me/" target="_blank">parcelfor.me</a> 

