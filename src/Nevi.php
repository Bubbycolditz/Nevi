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

        private PDO $pdo;

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

                if($allowSession){

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

            return getenv('HTTP_CLIENT_IP')
                ?? getenv('HTTP_X_FORWARDED_FOR')
                ?? getenv('HTTP_X_FORWARDED')
                ?? getenv('HTTP_FORWARDED_FOR')
                ?? getenv('HTTP_FORWARDED')
                ?? getenv('REMOTE_ADDR')
                ?? 'UNKNOWN';

        }

        public static function get_os(): string {

            $user_agent = self::get_user_agent();
            $os_platform = "Unknown OS Platform";
            $os_array = array(
                'windows nt 10' => 'Windows 10',
                'windows nt 6.3' => 'Windows 8.1',
                'windows nt 6.2' => 'Windows 8',
                'windows nt 6.1' => 'Windows 7',
                'windows nt 6.0' => 'Windows Vista',
                'windows nt 5.2' => 'Windows Server 2003/XP x64',
                'windows nt 5.1' => 'Windows XP',
                'windows xp' => 'Windows XP',
                'windows nt 5.0' => 'Windows 2000',
                'windows me' => 'Windows ME',
                'win98' => 'Windows 98',
                'win95' => 'Windows 95',
                'win16' => 'Windows 3.11',
                'macintosh|mac os x' => 'Mac OS X',
                'mac_powerpc' => 'Mac OS 9',
                'linux' => 'Linux',
                'ubuntu' => 'Ubuntu',
                'iphone' => 'iPhone',
                'ipod' => 'iPod',
                'ipad' => 'iPad',
                'android' => 'Android',
                'blackberry' => 'BlackBerry',
                'webos' => 'Mobile'
            );

            foreach($os_array as $regex => $value){
                if(preg_match('/' . $regex . '/i', $user_agent)){
                    $os_platform = $value; break;
                }
            }

            return $os_platform;

        }

        public static function get_browser(): string {

            $user_agent = self::get_user_agent();
            $browser = "Unknown Browser";
            $browser_array = array(
                '/msie|trident/i' => 'Internet Explorer',
                '/firefox/i' => 'Firefox',
                '/safari/i' => 'Safari',
                '/chrome/i' => 'Chrome',
                '/edge/i' => 'Edge',
                '/opera/i' => 'Opera',
                '/netscape/i' => 'Netscape',
                '/maxthon/i' => 'Maxthon',
                '/konqueror/i' => 'Konqueror',
                '/ubrowser/i' => 'UC Browser',
                '/mobile/i' => 'Handheld'
            );

            foreach($browser_array as $regex => $value){
                if(preg_match($regex, $user_agent)){
                    $browser = $value; break;
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

            return match ($errorType) {
                "success" => "<div class='alert alert-success ".($dismissible ? "alert-dismissible" : "")." fade show' role='alert'><p><i class='far fa-circle-check fa-fw'></i> $message</p>".($dismissible ? "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>" : "")."</div>",
                "warning" => "<div class='alert alert-warning ".($dismissible ? "alert-dismissible" : "")." fade show' role='alert'><p><i class='far fa-circle-exclamation'></i> $message</p>".($dismissible ? "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>" : "")."</div>",
                "danger" => "<div class='alert alert-danger ".($dismissible ? "alert-dismissible" : "")." fade show' role='alert'><p><i class='far fa-circle-xmark'></i> $message</p>".($dismissible ? "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>" : "")."</div>",
                default => "<div class='alert alert-danger ".($dismissible ? "alert-dismissible" : "")." fade show' role='alert'><p><i class='far fa-circle-xmark'></i> <b class='text-danger'>WARNING:</b> This error message does not have a proper error type! <code>\$errorType = \"$errorType\"</code><br> Please change it!!!! <b>(This message still returned the message \"$message\")</b></p>".($dismissible ? "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>" : "")."</div>"
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
         * @throws Exception
         */
        function randomGreeting(string $name): string {

            return match (random_int(1, 311)) {
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
                13 => "<b>$name</b>, you're back!",
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
                35 => "<span style='background-image: linear-gradient(to right,red,orange,green,blue,indigo,violet);-webkit-background-clip: text;-webkit-text-fill-color: transparent;'>You have a ".((1/157) * 100)."% chance to get this special message. Good one <b>$name</b></span>!",
                36 => "It's great to see you back <b>$name</b>!",
                37 => "Thanks for being the best <b>$name</b>!",
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
                55 => "Welcome back <b>$name</b>, I was running in circles while you were away.",
                56 => "Thanks for waddling your way back to here <b>$name</b>.",
                57 => "Long time ago, there was a user called <b>$name</b> and they sud... Oh, you're actually here!",
                58 => "Hey <b>$name</b>, good to have you here!",
                59 => "Welcome back, <b>$name</b>! It's been too long!",
                60 => "How's life treating you, <b>$name</b>?",
                61 => "Hola, <b>$name</b>! Espero que todo esté bien.",
                62 => "Long time no see, <b>$name</b>! What have you been up to?",
                63 => "Hey there, <b>$name</b>! It's always a pleasure to see you.",
                64 => "Guess who's back? It's <b>$name</b>!",
                65 => "Hi there, <b>$name</b>! Ready to conquer the day?",
                66 => "Good to see you again, <b>$name</b>!",
                67 => "Welcome back, <b>$name</b>! Did you miss me?",
                68 => "How's everything going, <b>$name</b>?",
                69 => "Hey <b>$name</b>, it's great to have you back!",
                70 => "Long time, no chat, <b>$name</b>! What's new?",
                71 => "Hello again, <b>$name</b>! Ready to dive in?",
                72 => "How have you been, <b>$name</b>? Missed your presence!",
                73 => "Hey <b>$name</b>, good to see you around!",
                74 => "<b>$name</b>! You're just in time for some exciting updates!",
                75 => "Welcome back, <b>$name</b>! Hope you're having a fantastic day!",
                76 => "Hola <b>$name</b>! ¿Cómo te va?",
                77 => "Look who's here! It's <b>$name</b>!",
                78 => "Hey there, <b>$name</b>! Ready to tackle some challenges?",
                79 => "Welcome back, <b>$name</b>! You were missed!",
                80 => "How's it going, <b>$name</b>? Ready for some fun?",
                81 => "Hey <b>$name</b>, it's been a while! What's the latest?",
                82 => "Hello again, <b>$name</b>! You bring a smile to my face!",
                83 => "Long time no see, <b>$name</b>! How have you been?",
                84 => "Hi there, <b>$name</b>! It's always a pleasure to see you.",
                85 => "Welcome back, <b>$name</b>! Ready to make some magic happen?",
                86 => "How's everything going, <b>$name</b>? We've been waiting for you!",
                87 => "Hey <b>$name</b>, it's great to have you back in the groove!",
                88 => "Long time no chat, <b>$name</b>! What's been happening?",
                89 => "Hello again, <b>$name</b>! Hope you're ready for some excitement!",
                90 => "How have you been, <b>$name</b>? We've missed your presence!",
                91 => "Hey <b>$name</b>, good to see you around these parts!",
                92 => "<b>$name</b>! You're just in time for some incredible updates!",
                93 => "Welcome back, <b>$name</b>! We hope you're ready for an amazing journey!",
                94 => "Hola <b>$name</b>! ¿Cómo te ha ido?",
                95 => "Look who's here! It's <b>$name</b>, and we couldn't be happier!",
                96 => "Hey there, <b>$name</b>! Ready to conquer the world?",
                97 => "Welcome back, <b>$name</b>! You were missed more than you know!",
                98 => "How's it going, <b>$name</b>? We're excited to have you with us!",
                99 => "Hey <b>$name</b>, it's been a while! Fill us in on what's been going on!",
                100 => "Welcome back, <b>$name</b>! It feels like you never left!",
                101 => "Hey <b>$name</b>, good to see you back in action!",
                102 => "Long time no chat, <b>$name</b>! What's been going on?",
                103 => "Hello again, <b>$name</b>! Hope you're ready for another adventure!",
                104 => "Hey <b>$name</b>, glad to have you back in the mix!",
                105 => "Welcome back, <b>$name</b>! Get ready for some amazing things!",
                106 => "Hola, <b>$name</b>! Espero que todo esté genial.",
                107 => "Look who's here! It's <b>$name</b> - the life of the party!",
                108 => "Hey there, <b>$name</b>! Time to shine and make things happen!",
                109 => "Welcome back, <b>$name</b>! We've been eagerly waiting for your return!",
                110 => "How's it going, <b>$name</b>? We're thrilled to have you with us!",
                111 => "Hey <b>$name</b>, it's been a while! Fill us in on what's been happening!",
                112 => "Hello again, <b>$name</b>! Ready to conquer new frontiers?",
                113 => "How have you been, <b>$name</b>? Your presence lights up the room!",
                114 => "Look who's here! It's <b>$name</b> - ready to make magic happen!",
                115 => "Hey there, <b>$name</b>! Let's rock and roll!",
                116 => "Welcome back, <b>$name</b>! Your presence brightens up our day!",
                117 => "How's it going, <b>$name</b>? Ready for some exciting adventures?",
                118 => "Greetings, <b>$name</b>! Your presence is like a breath of fresh air!",
                119 => "Welcome back, <b>$name</b>! We hope you're ready for some unforgettable moments!",
                120 => "Hola, <b>$name</b>! Espero que estés listo para divertirte.",
                121 => "Look who's here! It's <b>$name</b> - the star of the show!",
                122 => "Hey there, <b>$name</b>! It's time to shine and make your mark!",
                123 => "Welcome back, <b>$name</b>! We've been eagerly awaiting your return!",
                124 => "How's it going, <b>$name</b>? We're thrilled to have you here!",
                125 => "Hey <b>$name</b>, it's been a while! What's the scoop?",
                126 => "Hello again, <b>$name</b>! Your presence brings joy to our hearts!",
                127 => "How have you been, <b>$name</b>? Your enthusiasm is contagious!",
                128 => "Hey <b>$name</b>, good to see you back in the game!",
                129 => "<b>$name</b>! You're just in time for some exciting news!",
                130 => "Welcome back, <b>$name</b>! Hope you're ready for an extraordinary experience!",
                131 => "Welcome back, <b>$name</b>! It's great to have you with us!",
                132 => "Rise and shine, <b>$name</b>! Today is a brand-new day filled with endless possibilities!",
                133 => "Hey there, <b>$name</b>! Wishing you a day filled with joy, laughter, and success!",
                134 => "Good morning, <b>$name</b>! May your day be as bright and beautiful as you are!",
                135 => "Hola, <b>$name</b>! Espero que tengas un día lleno de alegría y bendiciones!",
                136 => "Wakey-wakey, <b>$name</b>! It's time to seize the day and make it amazing!",
                137 => "Rise up and conquer, <b>$name</b>! You have the power to achieve greatness!",
                138 => "Hey <b>$name</b>, sending you positive vibes and good energy for an incredible day ahead!",
                139 => "Good morning, <b>$name</b>! Embrace the day with a smile and let your light shine bright!",
                140 => "Bonjour, <b>$name</b>! Que votre journée soit remplie de bonheur et de succès!",
                141 => "Wishing you a fantastic day, <b>$name</b>! May every moment be filled with joy and positivity!",
                142 => "Rise and sparkle, <b>$name</b>! Today is your day to shine like the star that you are!",
                143 => "Hey there, <b>$name</b>! It's a new day, a new beginning, and a chance to make it extraordinary!",
                144 => "Good morning, <b>$name</b>! May your day be filled with love, laughter, and beautiful moments!",
                145 => "Greetings, <b>$name</b>! Embrace the day with open arms and let the magic unfold!",
                146 => "Wake up with determination, <b>$name</b>! Today is your opportunity to create a life you love!",
                147 => "Hey <b>$name</b>, wishing you a day filled with endless possibilities and exciting adventures!",
                148 => "Good morning, <b>$name</b>! May your day be blessed with happiness, success, and positive vibes!",
                149 => "Hola, <b>$name</b>! Que tengas un día maravilloso lleno de alegría y sonrisas!",
                150 => "Rise and thrive, <b>$name</b>! Your potential knows no bounds, so go out and conquer the world!",
                151 => "Hey there, <b>$name</b>! Today is a blank canvas waiting for your unique masterpiece!",
                152 => "Good morning, <b>$name</b>! May your day be as beautiful and extraordinary as you are!",
                153 => "Greetings, <b>$name</b>! Take a deep breath, embrace the day, and let your dreams take flight!",
                154 => "Wake up with gratitude, <b>$name</b>! Today is a gift, and you have the power to make it amazing!",
                155 => "Hey <b>$name</b>, the world awaits your brilliance! Step into the day with confidence and grace!",
                156 => "Good morning, <b>$name</b>! May your day be filled with moments that make your heart smile!",
                157 => "Hola, <b>$name</b>! Que hoy sea un día lleno de alegría, amor y prosperidad!",
                158 => "Welcome back, <b>$name</b>! Brace yourself for an extraordinary journey!",
                159 => "Hey there, <b>$name</b>! Your positive vibes light up the atmosphere!",
                160 => "Greetings, <b>$name</b>! Your presence brings joy and inspiration to us all!",
                161 => "Hey <b>$name</b>, you're a superstar! Keep shining brightly!",
                162 => "Welcome back, <b>$name</b>! Your energy and enthusiasm are contagious!",
                163 => "How's it going, <b>$name</b>? Together, let's create magic and achieve greatness!",
                164 => "Hey there, <b>$name</b>! Get ready for an extraordinary journey filled with endless possibilities!",
                165 => "Welcome back, <b>$name</b>! Your presence adds a touch of brilliance to our world!",
                166 => "Hola <b>$name</b>! ¿Estás listo para una experiencia increíble?",
                167 => "Look who's here! It's <b>$name</b>, the one who spreads warmth and kindness!",
                168 => "Welcome back, <b>$name</b>! Your positive energy is like a ray of sunshine!",
                169 => "Hey <b>$name</b>, you're a star in our galaxy of awesomeness!",
                170 => "It's a pleasure to have you here, <b>$name</b>! Let's make this journey unforgettable!",
                171 => "Welcome back, <b>$name</b>! Your presence brightens up our days!",
                172 => "Hey there, <b>$name</b>! Your positive vibes make the world a better place!",
                173 => "Greetings, <b>$name</b>! Your enthusiasm and creativity are truly inspiring!",
                174 => "Hey <b>$name</b>, you're like a breath of fresh air! Let's conquer new heights together!",
                175 => "Welcome back, <b>$name</b>! Your energy and passion are contagious!",
                176 => "Hola <b>$name</b>! ¿Listo para hacer cosas increíbles juntos?",
                177 => "Look who's back! It's <b>$name</b>, the one who adds color to our lives!",
                178 => "Welcome, <b>$name</b>! Your presence fills our hearts with joy and excitement!",
                179 => "Hey there, <b>$name</b>! Your positive attitude sparks creativity all around!",
                180 => "It's a delight to have you here, <b>$name</b>! Let's make incredible things happen!",
                181 => "Welcome back, <b>$name</b>! Your smile brightens up our world!",
                182 => "Hey <b>$name</b>, you're a shining star! Keep spreading your light!",
                183 => "Greetings, <b>$name</b>! Your presence brings joy and inspiration to everyone around!",
                184 => "Hey there, <b>$name</b>! Get ready for an adventure full of surprises!",
                185 => "Welcome back, <b>$name</b>! Your positive energy is contagious!",
                186 => "How's it going, <b>$name</b>? Let's create something extraordinary together!",
                187 => "Hey <b>$name</b>, you're a true inspiration! Keep shining brightly!",
                188 => "Welcome back, <b>$name</b>! Your presence brings warmth and happiness!",
                189 => "Hola <b>$name</b>! ¿Estás listo para un viaje lleno de aventuras?",
                190 => "Look who's here! It's <b>$name</b>, the one who makes everything more vibrant!",
                191 => "Welcome, <b>$name</b>! Your positive vibes fill the room with excitement!",
                192 => "Hey there, <b>$name</b>! Let's unleash our creativity and make magic happen!",
                193 => "It's a pleasure to have you here, <b>$name</b>! Let's embark on an incredible journey together!",
                194 => "Welcome back, <b>$name</b>! Your energy and enthusiasm light up the atmosphere!",
                195 => "Hola <b>$name</b>! ¿Estás listo para una experiencia maravillosa?",
                196 => "Look who's back! It's <b>$name</b>, the one who fills our hearts with joy!",
                197 => "Welcome, <b>$name</b>! Your positive energy brings a smile to everyone's face!",
                198 => "Hey there, <b>$name</b>! Your presence creates a vibrant and inspiring atmosphere!",
                199 => "It's a delight to have you here, <b>$name</b>! Let's make each moment unforgettable!",
                200 => "Welcome back, <b>$name</b>! Your optimism and enthusiasm are truly contagious!",
                201 => "Hey <b>$name</b>, you're a ray of sunshine! Keep spreading warmth and happiness!",
                202 => "Greetings, <b>$name</b>! Your energy and creativity are boundless!",
                203 => "Hey there, <b>$name</b>! Let's embark on a thrilling adventure together!",
                204 => "Welcome back, <b>$name</b>! Your presence adds a touch of magic to our world!",
                205 => "Hola <b>$name</b>! ¿Listo para disfrutar de momentos inolvidables?",
                206 => "Look who's here! It's <b>$name</b>, the one who makes everything extraordinary!",
                207 => "Welcome, <b>$name</b>! Your positive vibes ignite the spirit of creativity!",
                208 => "Hey there, <b>$name</b>! Let's create memories that will last a lifetime!",
                209 => "It's a pleasure to have you here, <b>$name</b>! Let's make dreams a reality!",
                210 => "Welcome back, <b>$name</b>! Your energy and passion light up the room!",
                211 => "Hola <b>$name</b>! ¿Estás listo para explorar nuevos horizontes?",
                212 => "Look who's back! It's <b>$name</b>, the one who brings joy wherever they go!",
                213 => "Welcome, <b>$name</b>! Your positive presence makes every moment memorable!",
                214 => "Hey there, <b>$name</b>! Let's embark on a journey of endless possibilities!",
                215 => "Welcome back, <b>$name</b>! Your enthusiasm and optimism are truly inspiring!",
                216 => "Hey <b>$name</b>, you're a shining example of kindness and compassion!",
                217 => "Greetings, <b>$name</b>! Let's create a world filled with laughter and joy!",
                218 => "Hey there, <b>$name</b>! Get ready for an adventure that will leave you breathless!",
                219 => "Welcome back, <b>$name</b>! Your presence adds a spark of magic to our lives!",
                220 => "Hola <b>$name</b>! ¿Listo para vivir momentos increíbles juntos?",
                221 => "Look who's here! It's <b>$name</b>, the one who inspires us to be our best selves!",
                222 => "Welcome, <b>$name</b>! Your positive energy creates a ripple effect of happiness!",
                223 => "Hey there, <b>$name</b>! Let's unleash our creativity and make dreams come true!",
                224 => "It's a pleasure to have you here, <b>$name</b>! Let's make every moment count!",
                225 => "Welcome back, <b>$name</b>! Your enthusiasm and passion are truly contagious!",
                226 => "Hola <b>$name</b>! ¿Estás listo para descubrir nuevas aventuras?",
                227 => "Look who's back! It's <b>$name</b>, the one who fills our hearts with joy and laughter!",
                228 => "Welcome, <b>$name</b>! Your positive vibes bring a sense of harmony and bliss!",
                229 => "Hey there, <b>$name</b>! Let's embrace the beauty of life and create unforgettable memories!",
                230 => "Welcome back, <b>$name</b>! Your presence illuminates the room with positivity!",
                231 => "Hey <b>$name</b>, you're a beacon of light! Keep shining and inspiring others!",
                232 => "Greetings, <b>$name</b>! Your energy and enthusiasm ignite the fire within us!",
                233 => "Hey there, <b>$name</b>! Let's embark on a journey of self-discovery and growth!",
                234 => "Welcome back, <b>$name</b>! Your optimism and resilience inspire us all!",
                235 => "Hola <b>$name</b>! ¿Listo para disfrutar de momentos llenos de alegría?",
                236 => "Look who's here! It's <b>$name</b>, the one who adds a touch of magic to our lives!",
                237 => "Welcome, <b>$name</b>! Your positive energy lifts our spirits and brightens our days!",
                238 => "Hey there, <b>$name</b>! Let's unleash our creativity and make the impossible possible!",
                239 => "Welcome back, <b>$name</b>! Your presence brings a sense of serenity and joy!",
                240 => "Hey <b>$name</b>, you're a true inspiration! Keep following your dreams and shining bright!",
                241 => "Greetings, <b>$name</b>! Your enthusiasm and passion ignite the flame of possibility!",
                242 => "Hey there, <b>$name</b>! Let's embark on an extraordinary journey of discovery!",
                243 => "Welcome back, <b>$name</b>! Your positive outlook brightens our path to success!",
                244 => "Hola <b>$name</b>! ¿Estás listo para vivir momentos inolvidables y llenos de emoción?",
                245 => "Look who's back! It's <b>$name</b>, the one who brings laughter and joy to our lives!",
                246 => "Welcome, <b>$name</b>! Your positive vibes create a ripple effect of happiness!",
                247 => "Hey there, <b>$name</b>! Let's embrace the adventure and make memories to last a lifetime!",
                248 => "Welcome back, <b>$name</b>! Your presence illuminates our world with positivity!",
                249 => "Hey <b>$name</b>, you're a guiding light! Keep inspiring and spreading love!",
                250 => "Greetings, <b>$name</b>! Your energy and creativity fuel our collective imagination!",
                251 => "Hey there, <b>$name</b>! Let's embark on a journey of self-discovery and transformation!",
                252 => "Welcome back, <b>$name</b>! Your optimism and resilience inspire us to reach new heights!",
                253 => "Hola <b>$name</b>! ¿Estás listo para disfrutar de momentos llenos de alegría y felicidad?",
                254 => "Look who's here! It's <b>$name</b>, the one who adds a sprinkle of magic to our lives!",
                255 => "Welcome, <b>$name</b>! Your positive energy lifts our spirits and fuels our dreams!",
                256 => "Hey there, <b>$name</b>! Let's unlock our potential and create a future of success!",
                257 => "Welcome back, <b>$name</b>! Your presence brings a sense of harmony and inspiration!",
                258 => "Hey <b>$name</b>, you're a true visionary! Keep pursuing your dreams and making an impact!",
                259 => "Greetings, <b>$name</b>! Your enthusiasm and passion ignite the fire of creativity!",
                260 => "Hey there, <b>$name</b>! Let's embark on an extraordinary adventure of possibilities!",
                261 => "Welcome back, <b>$name</b>! Your positive mindset sets the stage for limitless potential!",
                262 => "Hola <b>$name</b>! ¿Estás listo para vivir momentos inolvidables y emocionantes?",
                263 => "Look who's back! It's <b>$name</b>, the one who brings laughter and joy wherever they go!",
                264 => "Welcome, <b>$name</b>! Your positive vibes create a ripple effect of happiness and inspiration!",
                265 => "Hey there, <b>$name</b>! Let's embrace the journey and make memories that will last a lifetime!",
                266 => "Welcome back, <b>$name</b>! Your presence illuminates our world with positivity and hope!",
                267 => "Hey <b>$name</b>, you're a guiding star! Keep shining and spreading your unique light!",
                268 => "Greetings, <b>$name</b>! Your energy and creativity fuel our collective imagination and innovation!",
                269 => "Hey there, <b>$name</b>! Let's embark on a journey of self-discovery, growth, and transformation!",
                270 => "Welcome back, <b>$name</b>! Your optimism and resilience inspire us to dream big and achieve greatness!",
                271 => "Hola <b>$name</b>! ¿Estás listo para disfrutar de momentos llenos de alegría, felicidad y aventura?",
                272 => "Look who's here! It's <b>$name</b>, the one who adds a sprinkle of magic and wonder to our lives!",
                273 => "Welcome, <b>$name</b>! Your positive energy lifts our spirits, ignites our passion, and empowers us all!",
                274 => "Hey there, <b>$name</b>! Let's unlock our full potential, unleash our creativity, and shape a brighter future!",
                275 => "Welcome back, <b>$name</b>! Your presence brings a sense of harmony, inspiration, and boundless opportunities!",
                276 => "Hey <b>$name</b>, you're a true visionary! Keep pursuing your dreams, making an impact, and leaving a legacy!",
                277 => "Greetings, <b>$name</b>! Your enthusiasm, passion, and innovative thinking ignite the fire of creativity and progress!",
                278 => "Hey there, <b>$name</b>! Let's embark on an extraordinary adventure of possibilities, growth, and positive change!",
                279 => "Welcome back, <b>$name</b>! Your positive mindset sets the stage for limitless potential, abundance, and happiness!",
                280 => "Hola <b>$name</b>! ¿Estás listo para vivir momentos inolvidables, emocionantes y repletos de amor y amistad?",
                281 => "Hey there, <b>$name</b>! Your presence fills the room with joy and laughter!",
                282 => "Welcome, <b>$name</b>! Your positive energy is like a beacon of light in our lives!",
                283 => "Hey <b>$name</b>, you're a star! Keep shining and inspiring those around you!",
                284 => "Greetings, <b>$name</b>! Your smile brightens up even the cloudiest days!",
                285 => "Hey there, <b>$name</b>! Your kindness and compassion are truly remarkable!",
                286 => "Welcome, <b>$name</b>! Your enthusiasm is contagious and uplifting!",
                287 => "Hey <b>$name</b>, you're a source of inspiration for us all!",
                288 => "Greetings, <b>$name</b>! Your positivity is a breath of fresh air!",
                289 => "Hey there, <b>$name</b>! Your presence brings a sense of peace and tranquility!",
                290 => "Welcome, <b>$name</b>! Your creativity knows no bounds!",
                291 => "Hey <b>$name</b>, you're a true adventurer! Keep exploring and discovering!",
                292 => "Greetings, <b>$name</b>! Your determination and resilience are commendable!",
                293 => "Hey there, <b>$name</b>! Your optimism brightens the darkest of days!",
                294 => "Welcome, <b>$name</b>! Your laughter is like music to our ears!",
                295 => "Hey <b>$name</b>, you're a ray of sunshine in a cloudy sky!",
                296 => "Greetings, <b>$name</b>! Your friendship is a precious gift!",
                297 => "Hey there, <b>$name</b>! Your presence brings a sense of harmony and balance!",
                298 => "Welcome, <b>$name</b>! Your wisdom and guidance are invaluable!",
                299 => "Hey <b>$name</b>, you're a true leader! Keep inspiring and empowering others!",
                300 => "Greetings, <b>$name</b>! Your passion fuels the fire of success!",
                301 => "Hey there, <b>$name</b>! Your positive attitude is infectious!",
                302 => "Welcome, <b>$name</b>! Your love and compassion touch the lives of many!",
                303 => "Hey <b>$name</b>, you're a beacon of hope in a world that sometimes feels dark!",
                304 => "Greetings, <b>$name</b>! Your presence brings a sense of serenity and joy!",
                305 => "Hey there, <b>$name</b>! Your determination inspires us to never give up!",
                306 => "Welcome, <b>$name</b>! Your authenticity is refreshing and inspiring!",
                307 => "Hey <b>$name</b>, you're a true trailblazer! Keep paving the way for others!",
                308 => "Greetings, <b>$name</b>! Your kindness has a ripple effect on the world!",
                309 => "Hey there, <b>$name</b>! Your presence lights up the room!",
                310 => "Welcome, <b>$name</b>! Your optimism is a ray of hope in challenging times!",
                311 => "Hey <b>$name</b>, you're a true friend! Thank you for being there for us!",
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