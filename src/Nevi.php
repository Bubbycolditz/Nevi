<?php

    /**
     * Nevi - PHP Library used for basic PHP functions, but simplified.
     * PHP Version 8.0
     *
     * @see https://github.com/Bubbycolditz/Nevi
     *
     * @author  Brian T. Colditz <brian@bcolditz.tech>
     */

    namespace Bubbycolditz\Nevi;

    require_once "Database.php";
    require_once "Auth.php";
    require_once "User.php";
    require_once "Log.php";

    class Nevi {

        function time_ago($time){

            $time_difference = time() - $time;

            if($time_difference < 1){ return "less than 1 second ago"; }

            $condition = array(
                12 * 30 * 24 * 60 * 60  =>  'year',
                30 * 24 * 60 * 60       =>  'month',
                7 * 24 * 60 * 60        =>  'week',
                24 * 60 * 60            =>  'day',
                60 * 60                 =>  'hour',
                60                      =>  'minute',
                1                       =>  'second'
            );

            foreach($condition as $secs => $str) {

                $d = $time_difference / $secs;

                if( $d >= 1 ) {

                    $t = round( $d );
                    return '' . $t . ' ' . $str . ( $t > 1 ? 's' : '' ) . ' ago';

                }
            }
        }
    }