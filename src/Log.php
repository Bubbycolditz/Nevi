<?php

    namespace Bubbycolditz\Nevi;

    class Log {

        private static function get_user_agent(){

            return $_SERVER['HTTP_USER_AGENT'];

        }

        public static function get_ip(){

            $mainIp = "";

            if(getenv('HTTP_CLIENT_IP')){

                $mainIp = getenv('HTTP_CLIENT_IP');

            } else if(getenv('HTTP_X_FORWARDED_FOR')) {

                $mainIp = getenv('HTTP_X_FORWARDED_FOR');

            } else if(getenv('HTTP_X_FORWARDED')){

                $mainIp = getenv('HTTP_X_FORWARDED');

            } else if(getenv('HTTP_FORWARDED_FOR')){

                $mainIp = getenv('HTTP_FORWARDED_FOR');

            } else if(getenv('HTTP_FORWARDED')){

                $mainIp = getenv('HTTP_FORWARDED');

            } else if(getenv('REMOTE_ADDR')){

                $mainIp = getenv('REMOTE_ADDR');

            } else {

                $mainIp = "UNKNOWN";

            }

            return $mainIp;

        }

        public static function get_os(){

            $user_agent = self::get_user_agent();
            $os_platform = "Unknown OS Platform";
            $os_array = array(
                '/windows nt 10/i' => 'Windows 10',
                '/windows nt 6.3/i' => 'Windows 8.1',
                '/windows nt 6.2/i' => 'Windows 8',
                '/windows nt 6.1/i' => 'Windows 7',
                '/windows nt 6.0/i' => 'Windows Vista',
                '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
                '/windows nt 5.1/i' => 'Windows XP',
                '/windows xp/i' => 'Windows XP',
                '/windows nt 5.0/i' => 'Windows 2000',
                '/windows me/i' => 'Windows ME',
                '/win98/i' => 'Windows 98',
                '/win95/i' => 'Windows 95',
                '/win16/i' => 'Windows 3.11',
                '/macintosh|mac os x/i' => 'Mac OS X',
                '/mac_powerpc/i' => 'Mac OS 9',
                '/linux/i' => 'Linux',
                '/ubuntu/i' => 'Ubuntu',
                '/iphone/i' => 'iPhone',
                '/ipod/i' => 'iPod',
                '/ipad/i' => 'iPad',
                '/android/i' => 'Android',
                '/blackberry/i' => 'BlackBerry',
                '/webos/i' => 'Mobile'
            );

            foreach($os_array as $regex => $value){

                if(preg_match($regex, $user_agent)){

                    $os_platform = $value;

                }
            }
            return $os_platform;
        }

        public static function get_browser(){

            $user_agent = self::get_user_agent();

            $browser = "Unknown Browser";

            $browser_array = array(
                '/msie/i' =>  'Internet Explorer',
                '/Trident/i' =>  'Internet Explorer',
                '/firefox/i' =>  'Firefox',
                '/safari/i' =>  'Safari',
                '/chrome/i' =>  'Chrome',
                '/edge/i' =>  'Edge',
                '/opera/i' =>  'Opera',
                '/netscape/i' =>  'Netscape',
                '/maxthon/i' =>  'Maxthon',
                '/konqueror/i' =>  'Konqueror',
                '/ubrowser/i' =>  'UC Browser',
                '/mobile/i' =>  'Handheld'
            );

            foreach($browser_array as $regex => $value){

                if(preg_match($regex, $user_agent)){

                    $browser = $value;

                }
            }

            return $browser;

        }

        /**

         * Prints an error message

         * @param string $errorType The type of error [**success**, **warning**, **danger**]
         * @param string $message The message wanting to be dispalyed
         * @param bool $dismissible Toggle dismissible alert boxes
         * @return string The entire alert dialog box onto the page

         */
        public function errorMessage($errorType, $message, $dismissible = true){

            return $errorType = match($errorType){
                "success" => "<div class='alert alert-success ".($dismissible == true ? "alert-dismissible" : "")." fade show' role='alert'><p><i class='far fa-circle-check fa-fw'></i> $message</p>".($dismissible == true ? "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>" : "")."</div>",
                "warning" => "<div class='alert alert-warning ".($dismissible == true ? "alert-dismissible" : "")." fade show' role='alert'><p><i class='far fa-circle-exclamation'></i> $message</p>".($dismissible == true ? "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>" : "")."</div>",
                "danger" => "<div class='alert alert-danger ".($dismissible == true ? "alert-dismissible" : "")." fade show' role='alert'><p><i class='far fa-circle-xmark'></i> $message</p>".($dismissible == true ? "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>" : "")."</div>",
                default => "<div class='alert alert-danger ".($dismissible == true ? "alert-dismissible" : "")." fade show' role='alert'><p><i class='far fa-circle-xmark'></i> <b style='color:red;'>WARNING:</b> This error message does not have a proper error type! <code>\$errorType = \"$errorType\"</code><br> Please change it!!!! <b>(This message still returned the message \"$message\")</b></p>".($dismissible == true ? "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>" : "")."</div>"
            };
        }

        /**

         * Logs the user's actions

         * @param string $page The page the user is currently at
         * @param string $action The type of action the user is completing
         * @param string $status The status of the log [**succeeded**, **failed**]
         * @param string $customMessage The specfic value that is shown along with the action and page
         * @return null

         */
        public function logAction($page, $action, $status, $customMessage = false){

            global $fullDateTime, $db, $user;

            $page = match($page){
                "categories" => "category",
                "services" => "service",
                "events", "viewInfo" => "event",
                "users" => "user",
                "teams" => "team",
                "worshippers" => "worshipper",
                "login" => "login",
                "logout" => "logout",
                "speakers" => "speaker",
                "permissions" => "permissions",
                "roles" => "roles",
                "settings" => "settings"
            };

            $actionNew = match($action){
                "create" => "Create",
                "modify" => "Modify",
                "delete" => "Delete",
                "view" => "View",
                "login", "logout" => "Log",
                "email" => "Email"
            };

            $description = match($page) {
                "login" => "$actionNew user in",
                "logout" => "$actionNew user out",
                default => "$actionNew $page <b>$customMessage</b>"
            };

            $db->pdoInsertQuery("logs", ['dateTime', 'userID', 'userIP', 'userOS', 'userBrowser', 'page', 'actionType', 'activityStatus', 'description'], ["$fullDateTime", "$user[id]", Log::get_ip(), Log::get_os(), Log::get_browser(), "$page", "$action", "$status", "$description"]);

        }
    }