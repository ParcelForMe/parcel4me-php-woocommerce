<?php
/*

    This class defines the fully pathed file name of the CA Certificate for OID (::fullCertPath())
    and a method to automatically update it if it has changed. 

    For more information see :
    https://blogs.msdn.microsoft.com/azureossds/2015/06/12/verify-peer-certificate-from-php-curl-for-azure-apps/
    https://curl.haxx.se/docs/caextract.html

    Note :
    To access the curl.haxx.se server to fetch the latest cacert.pem requires TLSv1.2
    https://en.wikipedia.org/wiki/OpenSSL
    which means if OpenSSL is used in the stack it must be at least version 1.0.1 

*/

namespace P4M;

class P4M_Shop_CaCert
{


    public static function fullCertPath() {
        return dirname(__FILE__) . "/cert/cacert.pem";
    }


    public static function downloadLatestCertIfChanged() {

        // curl command from : https://curl.haxx.se/docs/caextract.html
        //  $ curl --remote-name --time-cond cacert.pem https://curl.haxx.se/ca/cacert.pem

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://curl.haxx.se/ca/cacert.pem");

        curl_setopt($ch, CURLOPT_TIMEVALUE, filemtime( P4M_Shop_CaCert::fullCertPath() ));
        curl_setopt($ch, CURLOPT_TIMECONDITION, CURLOPT_TIMECOND_IFMODIFIEDSINCE);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);

        $result = curl_exec($ch);
        $info = curl_getinfo($ch);

        if (curl_errno($ch)) {
            $errMsg = 'Error:' . curl_error($ch). ' (If you get a SSL version error, the first check is to ensure your PHP environment supports TLSv1.2)';
            // for extra debugging, uncomment these 2 lines :
            //$curl_info = curl_version();
            //echo $curl_info['ssl_version'];        
            error_log( $errMsg );
            throw new \Exception( $errMsg );
        }
        curl_close ($ch);

        //echo '<hr/>'.$result;
        //echo '<hr/>'.json_encode($info);

        return $result;        

    }



}

?>