<?php
    // Dependencies
    require_once 'config/Database.php';
    require_once 'model/UserModel.php';

    //  _   _ ____  _____ ____     ____ ___  _   _ _____ ____   ___  _     _     _____ ____  
    // | | | / ___|| ____|  _ \   / ___/ _ \| \ | |_   _|  _ \ / _ \| |   | |   | ____|  _ \ 
    // | | | \___ \|  _| | |_) | | |  | | | |  \| | | | | |_) | | | | |   | |   |  _| | |_) |
    // | |_| |___) | |___|  _ <  | |__| |_| | |\  | | | |  _ <| |_| | |___| |___| |___|  _ < 
    //  \___/|____/|_____|_| \_\  \____\___/|_| \_| |_| |_| \_\\___/|_____|_____|_____|_| \_\
                                                                                          
    class UserController {

        private $db;
        private $model;
        private $is_connected = false;
        public $user_id;
        public $username;
        private $is_admin = false;
        private $total_threads;
        private $total_posts;
        private $registration_date;
        private $avatar;

        public function __construct() {
            $this->db = (new Database())->getConnection();
            $this->model = new UserModel($this->db);

            if(isset($_SESSION['username'])) {
                $this->model->load_profile($_SESSION['username']);
                $this->is_connected = true;
                $this->username = $this->model->username;
                $this->user_id = $this->model->user_id;
                $this->is_admin = $this->model->is_admin;
                $this->total_threads = $this->model->total_threads;
                $this->total_posts = $this->model->total_posts;
                $this->registration_date = $this->model->registration_date;
                $this->avatar = $this->model->avatar == '' ? 'default' : $this->model->avatar;
            }
        }

        public function loginRequired() {
            if(!$this->get_is_connected()) {
                header("Location: index.php?view=home&error=user_not_connected");
                exit();
            }
        }

        public function requireNotLoggedIn() {
            if($this->get_is_connected()) {
                header("Location: index.php?view=home");
                exit();
            }
        }

        //  ____        _           ____ _               _    _             
        // |  _ \  __ _| |_ __ _   / ___| |__   ___  ___| | _(_)_ __   __ _ 
        // | | | |/ _` | __/ _` | | |   | '_ \ / _ \/ __| |/ / | '_ \ / _` |
        // | |_| | (_| | || (_| | | |___| | | |  __/ (__|   <| | | | | (_| |
        // |____/ \__,_|\__\__,_|  \____|_| |_|\___|\___|_|\_\_|_| |_|\__, |
        //                                                            |___/ 

        // Comprueba si el usuario es administrador
        public function is_admin($username) {
            return $this->model->is_admin($username);
        }

        // Comprueba si el usuario está registrado
        public function is_registered($username) {
            return $this->model->is_registered($username);
        }
        
        //      _        _   _                 
        //     / \   ___| |_(_) ___  _ __  ___ 
        //    / _ \ / __| __| |/ _ \| '_ \/ __|
        //   / ___ \ (__| |_| | (_) | | | \__ \
        //  /_/   \_\___|\__|_|\___/|_| |_|___/
        
        public function login($username, $password) {
            $loginData = [];

            // Incorrect login
            if(!$this->model->login($username, $password)) {
                return DataController::generateData(1, "incorrectpass", "index.php?view=login&error=incorrectpass");
            }

            // Correct login
            $_SESSION['username'] = $username;
            return DataController::generateData(0, "ok", "index.php");
        }

        public function logout() {
            session_destroy();
            $data['status'] = 0;
            $data['redirectUrl'] = "index.php" ;
            return $data;
        }

        public function register($username, $email, $password) {
            $r = $this->model->register($username, $email, $password);
            $data = [];

            if($r === "username_taken") {    
                $data['status'] = 1;
                $data['redirectUrl'] = 'index.php?view=register&error=username_taken';
                return $data;
            }

            if ($r === "not_valid_broh" || $r == false ) {
                $data['status'] = 1;
                $data['redirectUrl'] = 'index.php?view=register&error=not_valid_broh';
                return $data;
            }

            // Example of mail sending when the user registers
            // $this->sendRegistrationMail($username)

            $data['status'] = 0;
            $data['redirectUrl'] = 'index.php?view=login&msg=register_success';
            return $data;
        }

        public function edit_password($password, $currentpass) {
            // Comprueba si el usuario esta logeado y concide con el usuario a editar
            if (!$this->get_is_connected()) {
                return DataController::generateData(1, "user_not_connected", ""); 
            }

            // Comprueba si alguno de los datos se encuentra vacío
            if (empty($password) || empty($currentpass)) {
                return DataController::generateData(1, "empty_data", "");
            }

            // Comprueba si la contraseña actual es correcta antes de actualizarla
            if(!$this->model->is_correct_password($this->get_user_id(), $currentpass)) {
                return DataController::generateData(1, "incorrect_pass", "");
            }

            // // Manda la orden al modelo para que se edite la sección
            $response = $this->model->edit_password($this->get_user_id(), $password);

            // Si la respuesta es negativa
            if (!$response) {
                return DataController::generateData(1, "edit_failed", "");
            }

            // Si la respuesta es satisfactoria
            return DataController::generateData(0, "ok", "");
        }

        private function sendRegistrationMail ($username) {
            $to = $this->model->getData($username, 'email');
            $from = "admin@seas-vm.test";
            $subject = "Registration succesfully";
            $message = "Welcome @".$username." to our site. \n\n Thanks for your registration.";
            $headers = "From:" . $from;
            if (mail($to, $subject, $message, $headers)) {
                return true;
            }
            return false;
        }

        public function save_avatar($filename) {
            if(empty($filename)) {
                return DataController::generateData(1, "empty_filename", "");
            }

            if(!$this->model->save_avatar($filename)){
                return false;
            }
            return true;
        }

        //      ____      _     ____        _        
        //     / ___| ___| |_  |  _ \  __ _| |_ __ _ 
        //    | |  _ / _ \ __| | | | |/ _` | __/ _` |
        //    | |_| |  __/ |_  | |_| | (_| | || (_| |
        //     \____|\___|\__| |____/ \__,_|\__\__,_|


        public function get_is_connected() {
            return $this->is_connected;
        }

        public function get_is_admin() {
            return $this->is_admin;
        }

        public function get_user_id() {
            return $this->user_id;
        }

        public function get_avatar() {
            return $this->avatar;
        }

        public function get_registration_date() {
            return $this->registration_date;
        }

        public function get_total_threads() {
            return $this->total_threads;
        }

        public function get_total_posts() {
            return $this->total_posts;
        }

        public function get_my_threads() {
            return DataController::generateData(0, "ok", "", [
                "threads" => $this->model->get_my_threads($this->get_user_id())
            ]);
        }

        public function count_posts($user_id) {
            return $this->model->count_posts($user_id);
        }

        public function count_threads($user_id) {
            return $this->model->count_threads($user_id);
        }

        public function get_my_posts() {
            return DataController::generateData(0, "ok", "", [
                "posts" => $this->model->get_my_posts($this->get_user_id())
            ]);
        }
    }

?>