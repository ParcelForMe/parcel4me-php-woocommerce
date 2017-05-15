<?php

namespace P4M;

require_once 'p4m-shop-interface.php';
require_once 'p4m-urls.php';
require_once 'p4m-models.php';
require_once 'settings.php';
require_once 'p4m-configure-server-urls.php';
require_once 'p4m-cacert.php';


const DEBUG_SHOW_ALL_API_CALLS = false;


abstract class P4M_Shop implements P4M_Shop_Interface
{


    // Your Shopping Cart must implement the following :

    abstract public function userIsLoggedIn();
    abstract public function createNewUser( $p4m_consumer );
    abstract public function isValidUserId( $localUserId );
    abstract public function fetchLocalUserByEmail( $localUserEmailAddress );
    abstract public function loginUser( $localUserId );
    abstract public function logoutCurrentUser();
    abstract public function getCurrentUserDetails();
    abstract public function setCurrentUserDetails( $p4m_consumer );
    abstract public function getCartOfCurrentUser();
    abstract public function setCartOfCurrentUser( $p4m_cart );
    abstract public function updateShipping( $shippingServiceName, $amount, $dueDate, $address );
    abstract public function getCartTotals();
    abstract public function updateWithDiscountCode( $discountCode );
    abstract public function updateRemoveDiscountCode( $discountCode );
    abstract public function updateCartItemQuantities( $itemsUpdateArray );
    abstract public function completePurchase ( $purchase_data );
    abstract public function handleError( $message );



    // Your Shopping Cart may implement the following :


    public $HOME_URL                = '/';
    public $PAYMENT_COMPLETE_URL    = '/';


    public function getCurrentSessionId() {
        // this may be overridden if the shopping cart uses a session id other than the PHP session id internally
        return session_id();
    }


    public function somethingWentWrong($message) {
        error_log("somethingWentWrong(" . $message . ") ");
        $this->handleError($message);  
    }


    public function paypalCancel() {
        // http://developer.parcelfor.me/docs/documentation/parcel-for-me-widgets/p4m-checkout-widget/paypalcancel/

        // these params are available :       
        //  $_GET['pasref']	PSP transaction reference
        //  $_GET['token'] Paypal transaction token
        //  $_GET['PayerID']	Paypal payer Id

        // close this popped up window
        echo '<script>window.close();</script>';
    }



    // Available public functions 

    public function isLoggedIntoParcel4me() {
        return ( array_key_exists("p4mToken", $_COOKIE) && $_COOKIE["p4mToken"] );
    }


    public function processPaymentRefund( $transactionId, $amount ) {

        // Obtain a credentials token 
        $oidc = new \OpenIDConnectClient( P4M_Shop_Urls::endPoint('oauth2_base_url'),
                                          Settings::getPublic('OpenIdConnect:ClientId'),
                                          Settings::getPublic('OpenIdConnect:ClientSecret') );
        $oidc->providerConfigParam(array('token_endpoint'=>P4M_Shop_Urls::endPoint('connect_token')));
        $oidc->addScope('p4mRetail');
        $oidc->addScope('p4mApi');

        $oidc->setCertPath( P4M_Shop_CaCert::localCertPath() );  
        
        $clientCredentials = $oidc->requestClientCredentialsToken();

        // check that it has the properties "access_token" and "token_type"
        if ( (!property_exists($clientCredentials, 'token_type')) ||
                (!property_exists($clientCredentials, 'access_token')) 
        ) {
            $this->somethingWentWrong('Invalid OAUTH2 Client Credentials returned :'.json_encode($clientCredentials));
        }

        $this->setBearerToken($clientCredentials->access_token);
        $rob = $this->apiHttp( 'POST',  P4M_Shop_Urls::endPoint('refund', '/'.$transactionId.'/'.$amount ));

        // returns true or an error message
        if ($rob->Success) {
            return true;
        } else {
            return $rob->Error;
        } 

    }


    public function updateCaCertificateIfChanged() {
        try {
            P4M_Shop_CaCert::downloadLatestCertIfChanged();
        } catch (\Exception $e) {
            $this->somethingWentWrong( $e->getMessage() );
        }
    }


    public function reverseTransaction( $transactionId ) {
        error_log('reverseTransation() is not currently implemented in p4m-shop.php!');
    }


    // Constructor
    // Pass in associative arry with these properties :
    //   p4m_client_id, p4m_secret, gfs_client_id, gfs_secret, 
    //   redirect_url_checkout, redirect_url_payment_complete,
    //   environment


    public function __construct( $settings = null) {

        if ( null == $settings )                                                throw new \Exception("No settings parameter passed to P4M_Shop constructor");
        if (!array_key_exists( 'p4m_client_id', $settings ))                    throw new \Exception("p4m_client_id setting not found");
        if (!array_key_exists( 'p4m_secret', $settings ))                       throw new \Exception("p4m_secret setting not found");
        if (!array_key_exists( 'gfs_client_id', $settings ))                    throw new \Exception("gfs_client_id setting not found");
        if (!array_key_exists( 'gfs_secret', $settings ))                       throw new \Exception("gfs_secret setting not found");
        if (!array_key_exists( 'redirect_url_checkout', $settings ))            throw new \Exception("redirect_url_checkout setting not found");
        if (!array_key_exists( 'redirect_url_payment_complete', $settings ))    throw new \Exception("redirect_url_payment_complete setting not found");
        if (!array_key_exists( 'environment', $settings ))                      throw new \Exception("environment setting not found");

        $site_base_url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}/";

        Settings::setPublic( 'OpenIdConnect:ClientId',     $settings['p4m_client_id'] );
        Settings::setPublic( 'OpenIdConnect:ClientSecret', $settings['p4m_secret'] );
        Settings::setPublic( 'OpenIdConnect:RedirectUrl',  $site_base_url.'p4m/getP4MAccessToken' );
        Settings::setPublic( 'GFS:ClientId',               $settings['gfs_client_id'] );
        Settings::setPublic( 'GFS:ClientSecret',           $settings['gfs_secret'] );
        Settings::setPublic( 'Environment',                $settings['environment'] );
        Settings::setPublic( 'RedirectUrl:Checkout',       $settings['redirect_url_checkout'] );
        Settings::setPublic( 'RedirectURl:PaymentDone',    $settings['redirect_url_payment_complete'] );

        configure_server_urls ( Settings::getPublic( 'Environment' ) );


    } 


    // Internal Class Functions : 


    private function redirectTo($pageLocation) {

        if (headers_sent()) {
            echo " <script> window.location = \"$pageLocation\"; </script> ";
            exit();
        } else {
            header("Location: {$pageLocation}");
            exit();
        }
    }


    private $bearerToken;
    private function setBearerToken($token) {
        $this->bearerToken = $token;
    }

    private function returnJsonError($message) {
        echo '{"Success": false, "Error": "'.$message.'" }';
        exit();
    }


    private function apiHttp_withoutErrorHandler($method, $endpoint, $data = null) {
        /*
        This does an HTTP request to the API, and calls somethingWentWrong() if the result does not contain a .Success property 
        It passes $this->$bearerToken as the auth header bearer token so call setBearerToken() first
        */

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "accept: application/json",
                "authorization: " . 'Bearer ' . $this->bearerToken,
                "cache-control: no-cache",
                "content-type: application/json"
            )
        ));

        /*
            This logic and the associated .pem file are to fix a cRUL error that occurs 
            when logging on in an Azure website :
                "SSL certificate problem: unable to get local issuer certificate"
            The solution is described here : 
                https://blogs.msdn.microsoft.com/azureossds/2015/06/12/verify-peer-certificate-from-php-curl-for-azure-apps/
                (which includes the link to http://curl.haxx.se/docs/caextract.html)
        */
        curl_setopt($curl, CURLOPT_CAINFO, P4M_Shop_CaCert::localCertPath());
        
        if ( DEBUG_SHOW_ALL_API_CALLS ) {
            error_log( '* REQUEST * -> '.$method . ' ' . $endpoint );
            error_log( json_encode($data) );
        }


        $response = curl_exec($curl);
        $err  = curl_error($curl);
        $info = curl_getinfo($curl);


        if ( DEBUG_SHOW_ALL_API_CALLS ) {
            error_log( '* RESPONSE * -> '.json_encode($response) );
            error_log( '* err * -> '.json_encode($err) );
            error_log( '* info * -> '.json_encode($info) );
        }

        curl_close($curl);

        if ($err) {
            throw new \Exception("Error calling API : # " . $err . " <!-- ".$endpoint." -->");
        } elseif ($info && array_key_exists('http_code', $info) && $info['http_code']!=200) {
            throw new \Exception("Error calling API : Returned {$info['http_code']}. ({$info['url']})");
        } elseif ($response=='') {
            throw new \Exception("Error calling API : returned blank (token could be expired)");
        } else {

            $rob = new \stdClass();
            $rob = json_decode($response);

            if ( (!is_object($rob)) || (!property_exists($rob, 'Success')) ) {
                throw new \Exception("Error calling API : No 'Success' property of response received");
            } 

        }

        // if we are here then the response has a .Success property, 
        // the calling function can check that and handle true or false success results

        return $rob; // return the response as an object  

    }


    private function apiHttp($method, $endpoint, $data = null) {

        // do a http request, but for any error use the "somethingWentWrong" method 

        try {
            $result = $this->apiHttp_withoutErrorHandler($method, $endpoint, $data);
        } catch (\Exception $e) {
            $result = $this->somethingWentWrong($e);
        }

        return $result;

    }




    // Public functions called by the cart when /p4m/ endpoints are accessed :

    public function signUp() {
        // http://developer.parcelfor.me/docs/documentation/parcel-for-me-widgets/p4m-register-widget/signup/

        if (!$this->userIsLoggedIn()) {
            $uiUrl = P4M_Shop_Urls::endPoint('signup');
            header("Location: {$uiUrl}"); 
            exit();
        }


        // Obtain a credentials token 

        $oidc = new \OpenIDConnectClient( P4M_Shop_Urls::endPoint('oauth2_base_url'),
                                          Settings::getPublic('OpenIdConnect:ClientId'),
                                          Settings::getPublic('OpenIdConnect:ClientSecret') );
        $oidc->providerConfigParam(array('token_endpoint'=>P4M_Shop_Urls::endPoint('connect_token')));
        $oidc->addScope('p4mRetail');
        $oidc->addScope('p4mApi');

        $oidc->setCertPath( P4M_Shop_CaCert::localCertPath() );  
        
        $clientCredentials = $oidc->requestClientCredentialsToken();


        // check that it has the properties "access_token" and "token_type"
        if ( (!property_exists($clientCredentials, 'token_type')) ||
                (!property_exists($clientCredentials, 'access_token')) 
        ) {
            $this->somethingWentWrong('Invalid OAUTH2 Client Credentials returned :'.json_encode($clientCredentials));
        }
        

        // Get the data to send to signup this consumer 
        $consumer = $this->getCurrentUserDetails();
        $cart     = $this->getCartOfCurrentUser();
        $consumerAndCartMessage = json_encode( array (
                'Consumer'  =>  $consumer,
                'Cart'      =>  $cart
        ));


        // Send the register consumer API request 
        $this->setBearerToken($clientCredentials->access_token);
        $rob = $this->apiHttp('POST',  P4M_Shop_Urls::endPoint('registerConsumer'), $consumerAndCartMessage);

        if (!$rob->Success) {

            if ( strpos($rob->Error, "registered")>-1 ) {

                $redirect_url = P4M_Shop_Urls::endPoint('alreadyRegistered', "?firstName=".$rob->consumer->GivenName."&email=".$rob->consumer->Email);
                $this->redirectTo($redirect_url);

            } else {
                $this->somethingWentWrong("Error registering with P4M : " . $rob->Error);
            }

        } else {

            $redirect_url = P4M_Shop_Urls::endPoint('registerConsumer', '/'.$rob->ConsumerId);
            $this->redirectTo($redirect_url);

        }

    }


    public function getP4MAccessToken() {
        // http://developer.parcelfor.me/docs/documentation/parcel-for-me-widgets/p4m-login-widget/getp4maccesstoken/#

        if ($_COOKIE["p4mState"] != $_REQUEST['state']) {
            $this->somethingWentWrong('Authentication error (p4mState)');
        }

        try {
            $oidc = new \OpenIDConnectClient(P4M_Shop_Urls::endPoint('connect_token'),
                                            Settings::getPublic('OpenIdConnect:ClientId'),
                                            Settings::getPublic('OpenIdConnect:ClientSecret') );
            $oidc->providerConfigParam(array('token_endpoint'=>P4M_Shop_Urls::endPoint('connect_token')));
            $oidc->providerConfigParam(array('jwks_uri'=>P4M_Shop_Urls::endPoint('jwks')));
            $oidc->setProviderURL(Settings::getPublic( 'Server:P4M_OID_SERVER' ));
        
            $oidc->setCertPath( P4M_Shop_CaCert::localCertPath() ); 

            $response = $oidc->authenticate();

            if (!$response) {
                $this->somethingWentWrong('OIDC auth returned false');
            }

        } catch (\OpenIDConnectClientException $oidcE) {
            $this->somethingWentWrong('OIDC Exception :'.$oidcE->getMessage());
        } catch (\Exception $e) {
            $this->somethingWentWrong('Exception doing OIDC auth:'.$e->getMessage());
        }

        // set the p4m cookie for this retailer's site
        $accessToken  = $oidc->getAccessToken();
        $cookieExpire = strtotime('+'.$response->expires_in.' seconds');
        $path         = '/';
        setcookie( "p4mToken",
                   $accessToken,
                   $cookieExpire,
                   $path );
            
        // close this popped up window
        echo '<script>window.close();</script>';

    }


    public function isLocallyLoggedIn() {
        // http://developer.parcelfor.me/docs/documentation/parcel-for-me-widgets/p4m-login-widget/islocallyloggedin/

        if ($this->userIsLoggedIn()) {
            
            setcookie( "p4mLocalLogin", true, 0, '/' );
            echo '{ "Success": true, "Error": null }';

        } else {

            setcookie( "p4mLocalLogin", false, 0, '/' );
            echo '{ "Success": false, "Error": "Not logged in" }';

        }

    }


    public function localLogin() {
        // http://developer.parcelfor.me/docs/documentation/parcel-for-me-widgets/p4m-login-widget/locallogin/

        setcookie( "p4mLocalLogin", false, 0, '/' );

        // Send the API request 
        $this->setBearerToken($_COOKIE["p4mToken"]);
        try {
            $consumerUrlParams = '?checkHasOpenCart=true';
            $rob = $this->apiHttp_withoutErrorHandler('GET',  P4M_Shop_Urls::endPoint('consumer', $consumerUrlParams));
        } catch ( \Exception $e ) {
            $this->returnJsonError( $e->getMessage() );
        }

        if (!$rob->Success) {
            echo '{ "Success": false, "Error": "Unsuccessful fetching consumer ('.$rob->Error.')" }';
        } else {

            $consumer = $rob->Consumer;

            if ($consumer) {

                $cookieExpire = strtotime('+1 years');
                $path         = '/';

                setcookie( "p4mAvatarUrl",              $consumer->ProfilePicUrl,                       $cookieExpire, $path );
                setcookie( "p4mGivenName",              $consumer->GivenName,                           $cookieExpire, $path );
                setcookie( "p4mOfferCartRestore",       ( $rob->HasOpenCart ? "true" : "false" ),       $cookieExpire, $path );
                setcookie( "p4mLocalLogin",             "true",                                         $cookieExpire, $path );
                if (isset($consumer->PrefDeliveryAddress)) {
                    setcookie( "p4mDefaultPostCode",        $consumer->PrefDeliveryAddress->PostCode,       $cookieExpire, $path );
                    setcookie( "p4mDefaultCountryCode",     $consumer->PrefDeliveryAddress->CountryCode,    $cookieExpire, $path );
                }

            }


            /*
                Handle these possible scenereos 
                     Local User	    P4M User	                Action
                     -------------- --------------------------- ----------------------------------------------------
                1	 Not logged in	Has no local Id 	        Create and login a new local user using the P4M details
                                                                Store the local Id in P4M Consumer.Extras["LocalId"]
                2a	 Not logged in	Has a VALID local Id 	    Login using the P4M local Id, update local details 
                2b   Not logged in  Id is invalid               Check if user exists with that email,
                                                                    If not : Create user and login 
                                                                    If so  : Update id that p4m stores for this user 
                                                                and login 
                3	 Logged in	    Has no local Id 	        Logout current user, proceed for 1
                4	 Logged in	    Has a different local Id 	Logout current user, proceed for 2 
                5	 Logged in	    Has matching local Id 	    Update local details from P4M if required 
            */

            $hasLocalId = ( isset($consumer->Extras) && isset($consumer->Extras->LocalId) && $consumer->Extras->LocalId);
            $loggedInUser = $this->userIsLoggedIn();
            if (!$loggedInUser) {

                if ( (!$hasLocalId) || (!$this->isValidUserId($consumer->Extras->LocalId)) ) {
                    // case 1 OR case 2b

                    $localUser = $this->fetchLocalUserByEmail( $consumer->Email );
                    if ( !$localUser ) $localUser = $this->createNewUser( $consumer ); 
                        
                    if ( !$localUser ) $this->returnJsonError("Failed to create new local user");
                    if ( !isset($localUser->id) ) $this->returnJsonError('No "id" field on local (non logged in) user');

                    try {
                        $setExtra = json_encode( array('LocalId' => $localUser->id) );
                        $rob = $this->apiHttp_withoutErrorHandler('POST',  P4M_Shop_Urls::endPoint('consumerExtras'), $setExtra);
                    } catch (\Exception $e) {
                        $this->returnJsonError( $e->getMessage()) ;
                    }

                    if ( !$rob->Success ) $this->returnJsonError( $rob->Error );

                    $this->loginUser( $localUser->id );
                } else {
                    // case 2a
                    $this->loginUser( $consumer->Extras->LocalId );
                }

            } else {

                $local_consumer = $this->getCurrentUserDetails();

                if (!$hasLocalId) {
                    // case 3
                    $this->logoutCurrentUser();

                    $localUser = $this->fetchLocalUserByEmail( $consumer->Email );
                    if ( !$localUser ) $localUser = $this->createNewUser( $consumer ); 

                    if ( !isset($localUser->id) ) $this->returnJsonError('No "id" field on local (logged in) user');

                    try {
                        $setExtra = json_encode( array('LocalId' => $localUser->id) );
                        $rob = $this->apiHttp_withoutErrorHandler('POST',  P4M_Shop_Urls::endPoint('consumerExtras'), $setExtra);
                    } catch (\Exception $e) {
                        $this->returnJsonError($e->getMessage());
                    }

                } elseif ( (property_exists($consumer, 'Extras')) && 
                           (property_exists($consumer->Extras, 'LocalId')) && 
                           (is_object($local_consumer)) &&
                           (property_exists($local_consumer, 'Extras')) &&
                           (property_exists($local_consumer->Extras, 'LocalId')) &&
                           ($consumer->Extras->LocalId != $local_consumer->Extras->LocalId) ) 
                {
                    // case 4
                    $this->logoutCurrentUser();
                    $this->loginUser( $consumer->Extras->LocalId );
                } else {
                    // case 5
                    $this->setCurrentUserDetails( $consumer );
                }

            }

            if (array_key_exists('currentPage', $_GET)) {
                $redirectTo = '"'.$_GET['currentPage'].'"';
            } else {
                $redirectTo = 'null';
            }
            echo '{ "RedirectUrl": '.$redirectTo.', "Success": true, "Error": null }';

        }


    }


    public function restoreLastCart() {
        // http://developer.parcelfor.me/docs/documentation/parcel-for-me-widgets/p4m-login-widget/restorelastcart/


        // Send the restoreLastCart API request 
        // Note that this endpoint will updated the saved P4M shopping cart with the passed in session id
        $localSessionId = $this->getCurrentSessionId();
        $endpoint       = P4M_Shop_Urls::endPoint('restoreLastCart') . '/' . $localSessionId;
        $this->setBearerToken($_COOKIE["p4mToken"]);

        $rob = $this->apiHttp('GET', $endpoint);

        if (!$rob->Success) {

            echo '{"Success": false, "Error": "'.$rob->Error.'" }';

        } else {

            $this->setCartOfCurrentUser( $rob->Cart );

            // delete the "p4mOfferCartRestore" cookie by setting it to have already expired
            if (isset($_COOKIE['p4mOfferCartRestore'])) {
                setcookie( "p4mOfferCartRestore", null, -1, '/');
            }

            echo  '{"Success": true, "Error": null }';

        }

    }


    public function checkoutRedirect() {
        // check if logged onto parcel for me, if so redirect to redirect_url_checkout, if not do nothing
        if ( $this->isLoggedIntoParcel4me() ) {
            $this->redirectTo(Settings::getPublic( 'RedirectUrl:Checkout' ));
        }
    }


    private function createJsCookie($name, $value, $expire) {
        echo " <script>
                document.cookie = '{$name}={$value}; expires={$expire}; path=/;';
              </script> ";
    }


    public function checkout() {
        // http://developer.parcelfor.me/docs/documentation/parcel-for-me-widgets/p4m-checkout-widget/checkout/

        $currentCart = $this->getCartOfCurrentUser();

        if ( (!isset($currentCart->Items)) || (empty($currentCart->Items)) ) {
            $this->redirectTo($this->HOME_URL);
        }

        if ( (!array_key_exists('gfsCheckoutToken', $_COOKIE)) || ($_COOKIE['gfsCheckoutToken']=='') ) {

            try {
                $oidc = new \OpenIDConnectClient(Settings::getPublic('Server:GFS_SERVER'),
                                                 Settings::getPublic('GFS:ClientId'),
                                                 Settings::getPublic('GFS:ClientSecret') );
                $oidc->providerConfigParam(array('token_endpoint'=>P4M_Shop_Urls::endPoint('gfs_connect_token')));
                $oidc->addScope('read');
                $oidc->addScope('checkout-api');

                $oidc->setCertPath( P4M_Shop_CaCert::localCertPath() ); 

                $response = $oidc->requestClientCredentialsToken();

                if (!$response) {
                    $this->somethingWentWrong('GFS server returned false (or blank) :'.(string)$response);
                } else if (!is_object($response)) {
                    throw new \Exception('Non object returned from GFS server :'.(string)$response);
                } else if (!property_exists($response, 'access_token')) {
                    throw new \Exception('Response from GFS server has no access_token:'.(string)$response);
                }

            } catch (\OpenIDConnectClientException $oidcE) {
                $this->somethingWentWrong('OIDC Exception :'.$oidcE->getMessage());
            } catch (\Exception $e) {
                $this->somethingWentWrong('Exception doing OIDC auth:'.$e->getMessage());
            }


            $accessToken  = $response->access_token;
            $encodeToken = base64_encode($accessToken);
            $cookieExpire = strtotime('+'.$response->expires_in.' seconds');
            $this->createJsCookie( "gfsCheckoutToken",
                            $encodeToken,
                            gmdate( "D, d M Y H:i:s T", $cookieExpire )
                          );
            $_COOKIE['gfsCheckoutToken'] = $encodeToken;

        }

        /*

            This function has no output !

            In the method that calls this you must
            now output the checkout widget !
        
        */
        
    }


    public function getP4MCart() {
        // http://developer.parcelfor.me/docs/documentation/parcel-for-me-widgets/p4m-checkout-widget/getp4mcart/

        $resultObject = new \stdClass();

        try {
            $cartObject = $this->getCartOfCurrentUser();
            $resultObject->Success = true;
            $resultObject->Cart    = $cartObject;            
        } catch (\Exception $e) {
            $resultObject->Success = false;
            $resultObject->Error   = $e->getMessage();
        }

        $resultJson = json_encode($resultObject, JSON_PRETTY_PRINT);
        echo $resultJson;

    }


    public function updShippingService() {
        // http://developer.parcelfor.me/docs/documentation/parcel-for-me-widgets/p4m-checkout-widget/updshippingservice/

        // update the local cart with the new shipping amt
        // recalculate cart totals (tax, discount, etc)

        $postBody = file_get_contents('php://input');
        $postBody = json_decode($postBody);
        
        $resultObject = new \stdClass();

        try {
            $this->updateShipping( $postBody->Service, $postBody->Amount, $postBody->DueDate, $postBody->Address );
            $totalsObject = $this->getCartTotals();

            $resultObject->Success  = true;
            $resultObject->Tax      = $totalsObject->Tax;
            $resultObject->Shipping = $totalsObject->Shipping;
            $resultObject->Discount = $totalsObject->Discount;
            $resultObject->Total    = $totalsObject->Total;
        } catch (\Exception $e) {
            $resultObject->Success = false;
            $resultObject->Error   = $e->getMessage();
        }

        $resultJson = json_encode($resultObject, JSON_PRETTY_PRINT);
        echo $resultJson; 

    }


    public function applyDiscountCode() {
        // http://developer.parcelfor.me/docs/documentation/parcel-for-me-widgets/p4m-checkout-widget/applydiscountcode/

        $postBody = file_get_contents('php://input');
        $postBody = json_decode($postBody);

        $resultObject = new \stdClass();

        if ( null == $postBody->discountCode ) {
            $workaround_err_message = "applyDiscountCode was called with a null discountCode -- this is a widget error and incorrect but we allow it and return Success : true";
            $resultObject->Success       = true;
            $resultObject->WidgetMessage = $workaround_err_message;
            error_log($workaround_err_message);
        } else {

            try {
                $discountCodeDetails = $this->updateWithDiscountCode( $postBody->discountCode );
                $totalsObject = $this->getCartTotals();

                $resultObject->Success      = true;
                $resultObject->Tax          = $totalsObject->Tax;
                $resultObject->Shipping     = $totalsObject->Shipping;
                $resultObject->Discount     = $totalsObject->Discount;
                $resultObject->Total        = $totalsObject->Total;
                $resultObject->Code         = $discountCodeDetails->Code;
                $resultObject->Description  = $discountCodeDetails->Description;
                $resultObject->Amount       = $discountCodeDetails->Amount;
            } catch (\Exception $e) {
                $resultObject->Success = false;
                $resultObject->Error   = $e->getMessage();
            }

        }

        $resultJson = json_encode($resultObject, JSON_PRETTY_PRINT);
        echo $resultJson;

    }


    public function removeDiscountCode() {
        // http://developer.parcelfor.me/docs/documentation/parcel-for-me-widgets/p4m-checkout-widget/removediscountcode/

        $postBody = file_get_contents('php://input');
        $postBody = json_decode($postBody);

        $resultObject = new \stdClass();

        try {
            $discountCodeDetails = $this->updateRemoveDiscountCode( $postBody->discountCode );
            $totalsObject = $this->getCartTotals();

            $resultObject->Success      = true;
            $resultObject->Tax          = $totalsObject->Tax;
            $resultObject->Shipping     = $totalsObject->Shipping;
            $resultObject->Discount     = $totalsObject->Discount;
            $resultObject->Total        = $totalsObject->Total;
            $resultObject->Code         = $discountCodeDetails->Code;
            $resultObject->Description  = $discountCodeDetails->Description;
            $resultObject->Amount       = $discountCodeDetails->Amount;
        } catch (\Exception $e) {
            $resultObject->Success = false;
            $resultObject->Error   = $e->getMessage();
        }

        $resultJson = json_encode($resultObject, JSON_PRETTY_PRINT);
        echo $resultJson;

    }


    public function itemQtyChanged() {
        // http://developer.parcelfor.me/docs/documentation/parcel-for-me-widgets/p4m-checkout-widget/itemqtychanged/

        $postBody = file_get_contents('php://input');
        $postBody = json_decode($postBody);
        
        $resultObject = new \stdClass();

        try {
            $discountsArray = $this->updateCartItemQuantities( $postBody );
            $totalsObject = $this->getCartTotals();

            $resultObject->Success   = true;
            $resultObject->Tax       = $totalsObject->Tax;
            $resultObject->Shipping  = $totalsObject->Shipping;
            $resultObject->Discount  = $totalsObject->Discount;
            $resultObject->Total     = $totalsObject->Total;
            $resultObject->Discounts = $discountsArray;
        } catch (\Exception $e) {
            $resultObject->Success = false;
            $resultObject->Error   = $e->getMessage();
        }

        $resultJson = json_encode($resultObject, JSON_PRETTY_PRINT);
        echo $resultJson; 

    }


    public function purchase() {
        // http://developer.parcelfor.me/docs/documentation/parcel-for-me-widgets/p4m-checkout-widget/purchase/

        $thisPostBody = file_get_contents('php://input');
        $thisPostBody = json_decode($thisPostBody);
        
        $resultObject = new \stdClass();

        // validate that the cart total from the widget is correct to prevent cart tampering in the browser
        $localCartTotals = $this->getCartTotals();
        if ($thisPostBody->cartTotal != $localCartTotals->Total) {
            $resultObject->Success = false;
            $resultObject->Error   = "Invalid cart total";
            error_log('Invalid cartTotal, p4m says : '.$thisPostBody->cartTotal.', local db says : '.$localCartTotals->Total);
        } else {

            try {

                $this->setBearerToken($_COOKIE["p4mToken"]);
                $p4mPostBody = json_encode( 
                                array ( 
                                    'cartId'        => $thisPostBody->cartId,
                                    'CVV'           => $thisPostBody->cvv
                                ) 
                            );
                if (property_exists($thisPostBody, 'newDropPoint')) {
                    $p4mPostBody->NewDropPoint = $thisPostBody->newDropPoint;
                }
                $rob = $this->apiHttp_withoutErrorHandler('POST', P4M_Shop_Urls::endPoint('purchase'), $p4mPostBody );

                $resultObject->Success   = true;
                
                if ( (!property_exists($rob, 'ACSUrl')) || (!$rob->ACSUrl) ) {

                    $this->completePurchase( $rob );

                    $resultObject->RedirectUrl = Settings::getPublic( 'RedirectURl:PaymentDone' );
                
                } else {

                    $resultObject->ASCUrl           = $rob->ACSUrl;
                    $resultObject->PaReq            = $rob->PaReq;
                    $resultObject->ACSResponseUrl   = $rob->ACSResponseUrl;
                    $resultObject->P4MData          = $rob->P4MData;

                }

    
            } catch (\Exception $e) {
                $resultObject->Success     = false;
                $resultObject->Error       = $e->getMessage();
                error_log('Error in p4m-shop.php purchase() '.$e->getMessage());
            }

        }

        $resultJson = json_encode($resultObject, JSON_PRETTY_PRINT);
        echo $resultJson; 

    }

    
    public function paypalSetup() {
        // http://developer.parcelfor.me/docs/documentation/parcel-for-me-widgets/p4m-checkout-widget/paypalsetup/

        $cartId	    = $_GET['cartId'];
        $cartTotal	= $_GET['cartTotal'];

        $resultObject = new \stdClass();

        // validate that the cart total from the widget is correct to prevent cart tampering in the browser
        $localCartTotals = $this->getCartTotals();
        if ($cartTotal != $localCartTotals->Total) {
            $resultObject->Success = false;
            $resultObject->Error   = "Invalid cart total";
            error_log('Invalid cart total, p4m says : '.$cartTotal.', local db says : '.$localCartTotals->Total);
        } else {

            // Send the p4m server paypal setup API request 
            $this->setBearerToken($_COOKIE["p4mToken"]);
            try {
                $postParam = json_encode( array ( 'cartId'  => $cartId ) );
                $rob = $this->apiHttp_withoutErrorHandler('POST', P4M_Shop_Urls::endPoint('paypalSetup'), $postParam );
                $resultObject->Success = true;
                $resultObject->Token   = $rob->Token;            
            } catch (\Exception $e) {
                $resultObject->Success = false;
                $resultObject->Error   = $e->getMessage();
            }

        }

        $resultJson = json_encode($resultObject, JSON_PRETTY_PRINT);
        echo $resultJson; 

    }


    
    public function purchaseComplete($cartId) {
        // http://developer.parcelfor.me/docs/documentation/parcel-for-me-widgets/p4m-checkout-widget/purchasecomplete/

        // This endpoint is called when a 3D Secure transaction has completed. 
        // It allows the host server to request the cart from P4M and store the cart, delivery and billing address details.

        $this->setBearerToken($_COOKIE["p4mToken"]);
        try {
            $rob = $this->apiHttp_withoutErrorHandler('GET', 
                        P4M_Shop_Urls::endPoint('cart', '/'.$cartId.'?wantAddress=true')
            );

            if ($rob->Success) {

                $this->completePurchase( $rob );
                
                $this->redirectTo(Settings::getPublic( 'RedirectURl:PaymentDone' ));
            } else {
                $this->somethingWentWrong('non-success getting p4m cart after calling purchaseComplete');
            }

        } catch (\Exception $e) {
            $this->somethingWentWrong($e->getMessage());
        }

    }

    

    
    public function testOidcConnection( $which, $clientId, $clientSecret ) {
        // This method is for testing if an OIDC connection is successful or not,
        // The first parameter should be 'p4m' or 'gfs',
        // Return "success" or a string describing the problem
        $success_value = "success";

        if ('p4m'==$which) {

            try { // as per call in getP4MAccessToken() 

                $oidc = new \OpenIDConnectClient(P4M_Shop_Urls::endPoint('oauth2_base_url'),
                                                 $clientId,
                                                 $clientSecret);
                $oidc->providerConfigParam(array('token_endpoint'=>P4M_Shop_Urls::endPoint('connect_token')));
                $oidc->addScope('p4mRetail');
                $oidc->addScope('p4mApi');

                $oidc->setCertPath( P4M_Shop_CaCert::localCertPath() ); 
   
                $response = $oidc->requestClientCredentialsToken();

                if ( (!$response) || (!is_object($response)) || (!property_exists($response, 'access_token')) ) {
                    return "Problem: ".(json_encode($response));
                } else {
                    return $success_value;
                }
            } catch (\Exception $e) {
                return "Problem:: ".$e->getMessage();
            }

        } else if ('gfs'==$which) {

            try { // as per call in checkout()
                $oidc = new \OpenIDConnectClient(Settings::getPublic('Server:GFS_SERVER'),
                                                $clientId,
                                                $clientSecret);
                $oidc->providerConfigParam(array('token_endpoint'=>P4M_Shop_Urls::endPoint('gfs_connect_token')));
                $oidc->addScope('read');
                $oidc->addScope('checkout-api');

                $oidc->setCertPath( P4M_Shop_CaCert::localCertPath() ); 

                $response = $oidc->requestClientCredentialsToken();

                if ( (!$response) || (!is_object($response)) || (!property_exists($response, 'access_token')) ) {
                    return "Problem: ".(json_encode($response));
                } else {
                    return $success_value;
                }
            } catch (\Exception $e) {
                return "Problem:: ".$e->getMessage();
            }

        } else {
            return 'Invalid which OIDC : '.$which;
        }

    }



}



?>