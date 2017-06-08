<?php
/*

    This class defines the fully pathed file name of the CA Certificate for OID (::localCertPath())
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

    public static function localCertPath() {
        return dirname(__FILE__) . "/cert/cacert.pem";
    }


    private static function remoteCertPath() {
        return "https://curl.haxx.se/ca/cacert.pem";
    }

    private static function writeToLog( $msg = '' ) {
        // as this process is likely to be run automatically, we want to log what's happening
        error_log( ' [OIDC CA Cert] : '.$msg );
    }


    public static function downloadLatestCertIfChanged() {

        // curl command from : https://curl.haxx.se/docs/caextract.html
        //  $ curl --remote-name --time-cond cacert.pem https://curl.haxx.se/ca/cacert.pem

        P4M_Shop_CaCert::writeToLog( 'About to check for changes to '.P4M_Shop_CaCert::remoteCertPath() );

        // first though, get the header and check the date to see if there is a new one ..
        $headers = get_headers( P4M_Shop_CaCert::remoteCertPath() , 1 );
        $remote_mod_date = strtotime( $headers['Last-Modified'] );

        $local_mod_date = filemtime( P4M_Shop_CaCert::localCertPath() );

        if ( (!$local_mod_date) || ($local_mod_date >= $remote_mod_date) ) {
            P4M_Shop_CaCert::writeToLog( 'Local version up to date ('.P4M_Shop_CaCert::localCertPath().' : '. 
                                         date("Y-m-d H:i", $local_mod_date).') ('.P4M_Shop_CaCert::remoteCertPath().' : '. 
                                         date("Y-m-d H:i", $remote_mod_date).')' );
            return true;
        } else {

            P4M_Shop_CaCert::writeToLog( 'Local version is older ('.P4M_Shop_CaCert::localCertPath().' : '. 
                                         date("Y-m-d H:i", $local_mod_date).') ('.P4M_Shop_CaCert::remoteCertPath().' : '. 
                                         date("Y-m-d H:i", $remote_mod_date).'), updating now .. ' );

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, P4M_Shop_CaCert::remoteCertPath());
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $result = curl_exec($ch);
            $info = curl_getinfo($ch);

            if (curl_errno($ch)) {
                // for extra debugging, uncomment these 2 lines :
                //$curl_info = curl_version();
                //echo $curl_info['ssl_version'];     
                $errMsg = 'Error:' . curl_error($ch). ' (If you get a SSL version error, the first check is to ensure your PHP environment supports TLSv1.2)';
                P4M_Shop_CaCert::writeToLog( $errMsg );
                throw new \Exception( $errMsg );
            }

            curl_close ($ch);

            //echo '<hr/>'.$result;
            //echo '<hr/>'.json_encode($info);

            if ( $result ) {
                // we have the new certificate so save it
                $bytes_written = file_put_contents( P4M_Shop_CaCert::localCertPath(), $result );
                if ($bytes_written < 1) {
                    $errMsg = 'Cert data not saved. Check write permissions on '.P4M_Shop_CaCert::localCertPath();
                    P4M_Shop_CaCert::writeToLog( $errMsg );
                    throw new \Exception( $errMsg );
                }
                P4M_Shop_CaCert::writeToLog( 'Certificate successfully updated.' );
                return $bytes_written;
            } else {
                P4M_Shop_CaCert::writeToLog( 'No result returned from : '.P4M_Shop_CaCert::remoteCertPath() );
                return null;
            }

        }

    }


}

?>