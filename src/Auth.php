<?php

    namespace Bubbycolditz\Nevi;

    class Auth {

        private $db;

        public function __construct(){

            session_start();
            $this->db = new Database();

        }

        public function login($username, $password, $remember = false){

            if($user = $this->db->pdoQuery("users", "*", "username = '$username'")){

                if(password_verify($password, $user['password'])){

                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_id'] = $user['id'];

                    if($remember){

                        $token = bin2hex(random_bytes(16));
                        $this->db->pdoUpdate("users", ['remember_token'], ["$token"], "id = '$user[id]'");
                        setcookie('remember_me', $token, time() + (10 * 365 * 24 * 60 * 60));

                    }

                    return true;

                }
            }

            return false;

        }

        public function is_logged_in(){

            if(isset($_COOKIE['remember_me'])){

                if($user = $this->db->pdoQuery("users", "*", "remember_token = '$_COOKIE[remember_me]'")){

                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_id'] = $user['id'];

                    return true;

                }
            }

            if($_SESSION['logged_in']){

                if($_SESSION['mfa_required']){

                    header("Location: verify"); exit;

                } else {

                    return true;

                }
            } else {

                header("Location: assets/includes/logout"); exit;

            }
        }

        public function logout() {

            session_destroy();

            if(isset($_COOKIE['remember_me'])){

                setcookie('remember_me', '', time() - 3600);

            }

            header('Location: ../../login'); exit;

        }
    }