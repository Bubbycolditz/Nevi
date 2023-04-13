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

    use Exception;
    use JetBrains\PhpStorm\NoReturn;
    use PDO;
    use PDOException;

    class Nevi {

        private $pdo;

        public function __construct($host, $dbName, $username, $password) {

            try {

                $this->pdo = new PDO("mysql:host=$host;dbname=$dbName", $username, $password);

            } catch (PDOException $e) {

                die("Could not connect to the database. Please check your configuration. The following error has occured:<br><br>$e");

            }
        }

        /**

         * Creates a MySQL query with the given expression

         * @param string $table The table wanting to select from.
         * @param string $selector The value(s) to grab from the table.
         * @param string $expression The expression statement to grab values from the table.
         * @return mixed The array of values that satisfied the given expression from the table.

         */
        public function pdoQuery(string $table, string $selector, string $expression): mixed {

            $stmt = $this->pdo->prepare("SELECT $selector FROM $table WHERE $expression");
            $stmt->execute();
            return $stmt->fetch();

        }

        /**

         * Creates a MySQL query with the given expression returning an array
         * @param string $table The table wanting to select from.
         * @param string $selector The value(s) to grab from the table.
         * @param string $expression The expression statement to grab values from the table.
         * @param string $indexVariable The index variable to store in the array.
         * @param string $indexAssign The value attached to the indexVariable.
         * @return array The array of values that satisfied the given expression from the table stored into itself with the provided indexVariable and indexAssign.

         */
        public function pdoArrayQuery(string $table, string $selector, string $expression, string $indexVariable, string $indexAssign): array {

            $array = [];

            $stmt = $this->pdo->prepare("SELECT $selector FROM $table WHERE $expression");
            $stmt->execute();
            $data = $stmt->fetchAll();

            foreach($data as $rows) {
                $array += array($rows[$indexVariable] => $rows[$indexAssign]);
            }

            return $array;

        }

        /**

         * Creates a MySQL query with the given expression returning an array
         * @param string $table The table wanting to fetch data from.
         * @param string $selector The value(s) to grab from the table.
         * @param bool|string $expression The expression statement to grab values from the table.
         * @param bool|string $type The type of equation to be used. [**table**, **combo**]
         * @return array The array of values that satisfied the given expression from the table.

         */
        public function pdoWhileQuery(string $table, string $selector, bool|string $expression = false, bool|string $type = false): array {

            if($type == "table"){

                $stmt = $this->pdo->prepare("SELECT $selector FROM $table");

            } else {

                $stmt = $this->pdo->prepare("SELECT $selector FROM $table WHERE $expression");

            }

            $stmt->execute();
            return $stmt->fetchAll();

        }

        /**

         * Inserts data using a MySQL query
         * @param string $table The table wanting to select from.
         * @param array $columns The columns that want to be updated.
         * @param array $values The values that want to be assigned to the columns.
         * @return bool The value of whether the values have successfully been inserted into the given table and columns.

         */
        public function pdoInsertQuery(string $table, array $columns, array $values): bool {

            $cols = implode(',', $columns);
            $placeholders = implode(',', array_fill(0, count($columns), '?'));

            $stmt = $this->pdo->prepare("INSERT INTO $table ($cols) VALUES ($placeholders)");

            for($i = 0; $i < count($values); $i++) {

                $stmt->bindValue($i + 1, $values[$i]);

            }

            return $stmt->execute();
        }

        /**

         * Selects the total MySQL Database values with the given expression
         * @param string $table The table wanting to select from.
         * @param string $selector The value(s) to grab from the table.
         * @param string $expression The expression statement to grab values from the table.
         * @return int The total rows that satisfies the given expression.

         */
        public function pdoNumRows(string $table, string $selector, string $expression): int {

            $stmt = $this->pdo->prepare("SELECT $selector FROM $table WHERE $expression");
            $stmt->execute();
            return $stmt->rowCount();

        }

        /**

         * Updates data using a MySQL query with the given expression
         * @param string $table The table wanting to select from.
         * @param array $columns The columns that want to be updated.
         * @param array $values The values that want to be assigned to the columns.
         * @param string $expression The expression statement to update with the given columns and table.
         * @return bool The value of whether the values have successfully been updated into the given table and columns.

         */
        public function pdoUpdate(string $table, array $columns, array $values, string $expression): bool {

            $assignments = [];

            for($i = 0; $i < count($columns); $i++){

                $assignments[] = $columns[$i] . '=?';

            }

            $assignments = implode(',', $assignments);

            $stmt = $this->pdo->prepare("UPDATE $table SET $assignments WHERE $expression");

            for($i = 0; $i < count($values); $i++){

                $stmt->bindValue($i + 1, $values[$i]);

            }

            return $stmt->execute();

        }

        /**

         * Deletes data using a MySQL query with the given expression
         * @param string $table The table wanting to delete.
         * @param string $expression The expression statement to delete with the given table.
         * @return bool The value of whether the row of data have successfully been deleted with the given table and expression.

         */
        public function pdoDelete(string $table, string $expression): bool {

            $stmt = $this->pdo->prepare("DELETE FROM $table WHERE $expression");
            return $stmt->execute();

        }

        /**

         * Log's a user in.
         * @param string $inputPassword The password from the input form.
         * @param string $verifyPassword The password that is in the database.
         * @return bool The value of whether the user has successfully logged in.
         * @throws Exception

         */
        public function verifyPassword(string $inputPassword, string $verifyPassword): bool {

            return password_verify($inputPassword, $verifyPassword) ? true : false;

        }

        /**

         * Log's a user out.
         * @param string $logoutLocation The file location of the logout file.
         * @return void

         */
        #[NoReturn] public function logout(string $logoutLocation): void {

            session_start();
            session_destroy();

            header("Location: $logoutLocation"); exit;

        }

        /**

         * Check's if a user is logged in.
         * @param string $verifyLocation The file location if the user has MFA enabled.
         * @param string $logoutLocation The file location if the user has failed the login check.
         * @param bool $allowSession Allow the user to continue their session without redirecting.
         * @return bool The value of whether the user is currently logged in or not.

         */
        public function is_logged_in(string $verifyLocation, string $logoutLocation, bool $allowSession): bool {

            if(@$_SESSION['logged_in']){

                if(@$_SESSION['mfa_required']){

                    header("Location: $verifyLocation"); exit;

                } else {

                    return true;

                }
            } else {

                if($allowSession == true){

                    return true;

                } else {

                    $this->logout($logoutLocation);

                }

            }
        }

        

        /**

         * Grab's the user info
         * @param string $userID The ID of the user.
         * @return mixed

         */
        public function get_user_info(string $userID): mixed {

            return $this->pdoQuery("users", "*", "id = '$userID'");

        }

        /**

         * Grab's the total registered events from the user.
         * @param string $userID The ID of the user.
         * @return int The value of total registered events.

         */
        public function get_total_registered_events(string $userID): int {

            global $unixFullDate, $unixFullDateTime;

            $totalEvents = 0;

            foreach($this->pdoWhileQuery("events", "*", "UNIX_TIMESTAMP(date) >= '$unixFullDate'") as $rows){

                $eventID = $rows['id'];
                $serviceID = $rows['serviceID'];
                $eventDate = $rows['date'];
                $startTime = $rows['startTime'];
                $endTime = $rows['endTime'];
                $tableDate = strtotime($rows['date']);

                if($serviceID != ""){

                    if($rows = $this->pdoQuery("services", "*", "id = '$serviceID'")){

                        $serviceStartTime = $rows['startTime'];
                        $serviceEndTime = $rows['endTime'];

                        $serviceStartTime = ($serviceStartTime == "" ?: "00:00");
                        $serviceEndTime = ($serviceEndTime == "" ?: "00:00");

                    }

                    if($endTime != ""){

                        $serviceEndTime = strtotime("$eventDate $endTime");

                    } elseif($serviceEndTime != ""){

                        $serviceEndTime = strtotime("$eventDate $serviceEndTime");

                    } else {

                        if($startTime != ""){

                            $serviceEndTime = strtotime("$eventDate $startTime");

                        } elseif($serviceStartTime != ""){

                            $serviceEndTime = strtotime("$eventDate $serviceStartTime");

                        }

                        $serviceEndTime += (120 * 60);

                    }
                } else {

                    if($endTime != ""){

                        $serviceEndTime = strtotime("$eventDate $endTime");

                    } else {

                        $serviceEndTime = strtotime("$eventDate $startTime");
                        $serviceEndTime += (120 * 60);

                    }
                }

                if($rows = $this->pdoQuery("schedule", "*", "userID = '$userID' AND eventID = '$eventID'")){

                    $attendanceStatus = $rows['attendanceStatus'];

                } else {

                    $attendanceStatus = "";

                }

                if($attendanceStatus == "can" && (($unixFullDate <= $tableDate) && ($unixFullDateTime <= $serviceEndTime))){

                    $totalEvents++;

                }
            }

            return $totalEvents;
        }

        /**

         * Start a password recovery by the user
         * @param string $userID The ID of the user.
         * @param string $passwordRecoveryEmailLocation The email file location for password recovery.
         * @return string
         * @throws Exception

         */
        public function initiate_password_recovery(string $userID, string $passwordRecoveryEmailLocation): string {

            global $mail, $siteNameShort, $siteNameFull, $siteURL;

            $recoveryToken = bin2hex(random_bytes(32));
            $user = $this->get_user_info($userID);

            $userEmail = $user['email'];
            $userFirstName = $user['firstName'];
            $userLastName = $user['lastName'];

            $userFullName = "$userFirstName $userLastName";

            $this->pdoUpdate("users", ['token'], ["$recoveryToken"], "email = '$userEmail'");

            try {

                $mail->addAddress($userEmail, $userFullName);
                $mail->Subject = "Password Recovery - $siteNameShort";

                ob_start();
                require "$passwordRecoveryEmailLocation";
                $mail->Body = ob_get_contents();
                ob_end_clean();

                $mail->send();

                $this->logAction("settings", "email", "succeeded", "Send Password Reset Email: \"$user[firstName] $user[lastName]\"");
                return $this->errorMessage("warning", "A password recovery link has been sent!");

            } catch (Exception $e) {

                $this->logAction("settings", "email", "failed", "Couldn't send Password Reset Email: \"$user[firstName] $user[lastName]\" --> [$e]");
                return $this->errorMessage("danger", "This message could not be sent for some reason. Here is the exact error: <b>$mail->ErrorInfo</b>");

            }
        }

        private static function get_user_agent(): mixed {

            return $_SERVER['HTTP_USER_AGENT'];

        }

        public static function get_ip(): string|array|bool {

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

        public static function get_os(): string {

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

        public static function get_browser(): string {

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
         * @param string $errorType The type of error. [**success**, **warning**, **danger**]
         * @param string $message The message wanting to be displayed.
         * @param bool $dismissible Toggle dismissible alert boxes.
         * @return string The entire alert dialog box onto the page.

         */
        public function errorMessage(string $errorType, string $message, bool $dismissible = true): string {

            return match($errorType){
                "success" => "<div class='alert alert-success ".($dismissible ? "alert-dismissible" : "")." fade show' role='alert'><p><i class='far fa-circle-check fa-fw'></i> $message</p>".($dismissible ? "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>" : "")."</div>",
                "warning" => "<div class='alert alert-warning ".($dismissible ? "alert-dismissible" : "")." fade show' role='alert'><p><i class='far fa-circle-exclamation'></i> $message</p>".($dismissible ? "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>" : "")."</div>",
                "danger" => "<div class='alert alert-danger ".($dismissible ? "alert-dismissible" : "")." fade show' role='alert'><p><i class='far fa-circle-xmark'></i> $message</p>".($dismissible ? "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>" : "")."</div>",
                default => "<div class='alert alert-danger ".($dismissible ? "alert-dismissible" : "")." fade show' role='alert'><p><i class='far fa-circle-xmark'></i> <b style='color:red;'>WARNING:</b> This error message does not have a proper error type! <code>\$errorType = \"$errorType\"</code><br> Please change it!!!! <b>(This message still returned the message \"$message\")</b></p>".($dismissible ? "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>" : "")."</div>"
            };
        }

        /**

         * Logs the user's actions
         * @param string $page The page the user is currently at
         * @param string $action The type of action the user is completing
         * @param string $status The status of the log [**succeeded**, **failed**]
         * @param bool|string $customMessage The specific value that is shown along with the action and page
         * @return ?bool

         */
        public function logAction(string $page, string $action, string $status, bool|string $customMessage = false): ?bool {

            global $fullDateTime, $user;

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

            return $this->pdoInsertQuery("logs", ['dateTime', 'userID', 'userIP', 'userOS', 'userBrowser', 'page', 'actionType', 'activityStatus', 'description'], ["$fullDateTime", "$user[id]", Nevi::get_ip(), Nevi::get_os(), Nevi::get_browser(), "$page", "$action", "$status", "$description"]);

        }

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

                if ($d >= 1) {

                    $t = round($d);
                    return '' . $t . ' ' . $str . ($t > 1 ? 's' : '') . ' ago';

                }
            }
        }

        /**

         * Prints a random greeting message
         * @param string $name The name of the user wanting to great
         * @return string The greeting message randomly selected

         */
        function randomGreeting($name) {

            match(random_int(1, 55)){
                1 => "Welcome back <b>$name</b>!",
                2 => "What's up <b>$name</b>?",
                3 => "Greetings <b>$name</b>!",
                4 => "¡Hola <b>$name</b>!",
                5 => "How's it going <b>$name</b>?",
                6 => "Hello <b>$name</b>!",
                7 => "Howdy <b>$name</b>!",
                8 => "How are ya <b>$name</b>?",
                9 => "It's good to see you <b>$name</b>!",
                10 => "What's new <b>$name</b>?",
                11 => "How are things going <b>$name</b>?",
                12 => "Hey <b>$name</b>!",
                13 => "<b>$name</b>, your back!",
                14 => "How's it coming along <b>$name</b>?",
                15 => "Hope things are going great for you <b>$name</b>!",
                16 => "Are things going well <b>$name</b>?",
                17 => "Welcome <b>$name</b>!",
                18 => "It's nice to see you <b>$name</b>!",
                19 => "Hi <b>$name</b>!",
                20 => "It's been a while <b>$name</b>!",
                21 => "How are you doing <b>$name</b>?",
                22 => "How is everything <b>$name</b>?",
                23 => "How have you been <b>$name</b>?",
                24 => "Welcome aboard <b>$name</b>!",
                25 => "Thanks for coming back <b>$name</b>!",
                26 => "How are you <b>$name</b>?",
                27 => "Are things going great <b>$name</b>?",
                28 => "What's going on <b>$name</b>?",
                29 => "Ahoy <b>$name</b>!",
                30 => "Knock knock. (who's there?) It's <b>$name</b>!",
                31 => "Aloha <b>$name</b>!",
                32 => "Hold the phone, <b>$name</b> just entered the house!",
                33 => "Wassup <b>$name</b>?",
                34 => "Why did the chicken cross the road? To go see <b>$name</b> on the other side!",
                35 => "<span style='background-image: linear-gradient(to right,red,orange,green,blue,indigo,violet);-webkit-background-clip: text;-webkit-text-fill-color: transparent;'>You have a ".((1/55) * 100)."% chance to get this special message. Good one <b>$name</b></span>!",
                36 => "It's great to see you back <b>$name</b>!",
                37 => "What's new <b>$name</b>?",
                38 => "Everyone look! <b>$name</b> is here to save the day!",
                39 => "Glad to see you <b>$name</b>!",
                40 => "First things first, let's give a warm welcome to <b>$name</b>!",
                41 => "Bonjour <b>$name</b>!",
                42 => "こんにちは <b>$name</b>!",
                43 => "你好 <b>$name</b>!",
                44 => "It's a wonderful day to see <b>$name</b> back!",
                45 => "Hiya <b>$name</b>!",
                46 => "Thanks for finding your way back here <b>$name</b>.",
                47 => "<b>$name</b>, you managed to find your way back here?",
                48 => "<b>$name</b>, you just came back in time for my funny joke of the day! <i>cricket noises</i>",
                49 => "I hope your day is going great <b>$name</b>!",
                50 => "From my computer brain to yours <b>$name</b>, I hope you have a fantastic day!",
                51 => "Hey <b>$name</b>, is everything going to plan today?",
                52 => "Hey there <b>$name</b>! My <i>calculations</i> are indicating that you are going to do an awesome job today!",
                53 => "Long time no see there <b>$name</b>. Or maybe not, I'm not sure. I just say random messages every time you visit here.",
                54 => "Here <b>$name</b>, did you know that there is a very special message that is rare? Well now you know.",
                55 => "Welcome back <b>$name</b>, I was running in circles while you were away."
            };

        }

        /**

         * Format the user's phone number
         * @param string $phoneNumber The phone number from the user
         * @return string The user's phone number in the format: "(xxx) xxx-xxxx"

         */
        function formatPhoneNumber(string $phoneNumber): string {

            $phoneNumber = preg_replace('/\D/', '', $phoneNumber);
            return sprintf("(%s) %s-%s", substr($phoneNumber, 0, 3), substr($phoneNumber, 3, 3), substr($phoneNumber, 6, 9));

        }
    }