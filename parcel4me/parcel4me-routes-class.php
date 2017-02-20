<?php
   /*
   Description: A key part of impementing the parcel for me checkout is accepting the /p4m 
                routes that the widgets call, this class handles that
   Version: 0.0.1
   Author: ParcelForMe
   Author URI: http://parcelfor.me/
   License: MIT
   */


class Parcel4me_Routes {
 
    public function __construct() {


        add_action( 'parse_request', 'handle_p4m_routes' );


        function handle_p4m_routes( $query ) {

            function string_begins( $string, $query ) {
                return substr($string, 0, strlen($query)) === $query;
            }

            $route = $query->request;

            if ( string_begins( $route, 'p4m/' ) ) {


    /*


  __________     ____  ____      
 /_  __/ __ \   / __ \/ __ \   _ 
  / / / / / /  / / / / / / /  (_)
 / / / /_/ /  / /_/ / /_/ /  _   
/_/  \____/  /_____/\____/  (_)  
                                 

    */
    
                echo '<h1>'.$route.'</h1>';
                echo ' a parcel for me route !!';
                exit();

            }

        }

    }


}

?>