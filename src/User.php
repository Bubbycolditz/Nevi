<?php

    namespace Bubbycolditz\Nevi;

    class User {

        public function get_user_info($userID){

            global $db;

            $user = $db->pdoQuery("users", "*", "id = '$userID'");
            return $user;

        }

        public function get_total_registered_events($userID){

            global $db, $unixFullDate, $unixFullDateTime;

            $totalEvents = 0;

            foreach($db->pdoWhileQuery("events", "*", "UNIX_TIMESTAMP(date) >= '$unixFullDate'") as $rows){

                $eventID = $rows['id'];
                $serviceID = $rows['serviceID'];
                $eventDate = $rows['date'];
                $startTime = $rows['startTime'];
                $endTime = $rows['endTime'];
                $tableDate = strtotime($rows['date']);

                if($serviceID != ""){

                    if($rows = $db->pdoQuery("services", "*", "id = '$serviceID'")){

                        $serviceStartTime = $rows['startTime'];
                        $serviceEndTime = $rows['endTime'];

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

                if($rows = $db->pdoQuery("schedule", "*", "userID = '$userID' AND eventID = '$eventID'")){

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

        public function initiate_password_recovery($userID){

            global $db, $mail, $siteNameShort, $siteNameFull, $siteURL, $log;

            $recoveryToken = bin2hex(random_bytes(32));
            $user = User::get_user_info($userID);

            $userEmail = $user['email'];
            $userFirstName = $user['firstName'];
            $userLastName = $user['lastName'];

            $userFullName = "$userFirstName $userLastName";

            $db->pdoUpdate("users", ['token'], ["$recoveryToken"], "email = '$userEmail'");

            try {

                $mail->addAddress($userEmail, $userFullName);
                $mail->Subject = "Password Recovery - $siteNameShort";

                ob_start();
                include('assets/includes/emails/password_reset.php');
                $mail->Body = ob_get_contents();
                ob_end_clean();

                $mail->send();

                $log->logAction("settings", "email", "succeeded", "Send Password Reset Email: \"$user[firstName] $user[lastName]\"");
                return $log->errorMessage("warning", "A password recovery link has been sent!");

            } catch (Exception $e) {

                $log->logAction("settings", "email", "failed", "Couldn't send Password Reset Email: \"$user[firstName] $user[lastName]\"");
                return $log->errorMessage("danger", "This message could not be sent for some reason. Here is the exact error: <b>{$mail->ErrorInfo}</b>");

            }
        }

    }