<?php

    //  _   _ ____  _____ ____    __  __  ___  ____  _____ _     
    // | | | / ___|| ____|  _ \  |  \/  |/ _ \|  _ \| ____| |    
    // | | | \___ \|  _| | |_) | | |\/| | | | | | | |  _| | |    
    // | |_| |___) | |___|  _ <  | |  | | |_| | |_| | |___| |___ 
    //  \___/|____/|_____|_| \_\ |_|  |_|\___/|____/|_____|_____|
                                                          
    class UserModel {

        private $conn;
        public $user_id;
        public $username;
        public $email;
        public $privileges;
        public $registration_date;
        public $avatar;
        public $is_admin;
        public $total_threads;
        public $total_posts;

        public function __construct($db) {
            $this->conn = $db;
        }

        //  ____        _           ____ _               _    _             
        // |  _ \  __ _| |_ __ _   / ___| |__   ___  ___| | _(_)_ __   __ _ 
        // | | | |/ _` | __/ _` | | |   | '_ \ / _ \/ __| |/ / | '_ \ / _` |
        // | |_| | (_| | || (_| | | |___| | | |  __/ (__|   <| | | | | (_| |
        // |____/ \__,_|\__\__,_|  \____|_| |_|\___|\___|_|\_\_|_| |_|\__, |
        //                                                            |___/ 

        // Comprueba si el usuario es administrador
        public function is_admin($username) { 
            return $this->is_admin === true ? true : false;           
        }
        // Comprueba si el usuario está registrado
        public function is_registered($username) {
            $query = "SELECT username FROM users WHERE username=:username";
            $stmt = $this->conn->prepare($query);

            // Sanitize
            $username = $this->sanitize($username);

            // Bind values
            $stmt->bindParam(":username", $username);

            // Execute the query
            $stmt->execute();

            // Check if any rows are returned
            if($stmt->rowCount() > 0) {
                // User with the username exists
                return true;
            }
            return false;
        }

        // Comprueba si la contraseña es correctaa
        public function is_correct_password($id, $currentpass) {

            $passwordHash = $this->get_password($id);
            var_dump($id);
            var_dump($currentpass);
            var_dump($passwordHash);
            if(password_verify($currentpass, $passwordHash) === true ) {
                return true;
            }
            return false;
        }

        //      _        _   _                 
        //     / \   ___| |_(_) ___  _ __  ___ 
        //    / _ \ / __| __| |/ _ \| '_ \/ __|
        //   / ___ \ (__| |_| | (_) | | | \__ \
        //  /_/   \_\___|\__|_|\___/|_| |_|___/

        public function sanitize($str) {
            return $str;
            // return htmlspecialchars(strip_tags($str));
        }

        public function login($username, $password) {

            if(!$this->is_registered($username)) {
                return false;
            }

            $query = "SELECT password FROM users WHERE username=:username";
            $stmt = $this->conn->prepare($query);
            $username = $this->sanitize($username);
            $stmt->bindParam(":username", $username);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $data = [];
                if (!password_verify($password, $row['password'])) {
                    return false;
                }                
                return true;
            }
            
        }

        public function register($username, $email, $password) {
            if($this->is_registered($username) == true) {
                return "username_taken";
            }

            $registration_date = date('Y-m-d H:i:s');
            $query = "INSERT INTO users SET username=:username, email=:email, password=:password, privileges='user', registration_date=:registration_date";
            $stmt = $this->conn->prepare($query);

            // sanitize
            // Check that the email syntax is correct
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return "not_valid_broh";
            }
            $username = $this->sanitize($username);
            $email = $this->sanitize($email);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            // bind values
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":password", $password);
            $stmt->bindParam(":registration_date", $registration_date);

            if($stmt->execute()) {
                return true;
            }
            return false;
        
        }

        // Comprueba si un usuario existe
        function does_user_exist($id) {
   
            $query = "SELECT id FROM users WHERE id=:id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();

            if($stmt->rowCount() > 0) {
                return true;
            }
            return false;
        }

        public function edit_password($id, $password) {
            
            if($this->does_user_exist($id) === false) {return false;}
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $query = "UPDATE users SET password=:password WHERE id=:id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":password", $passwordHash);
           
            if($stmt->execute()) {return true;}
            return false;
        }

        public function save_avatar($filename) {
            $stmt = $this->conn->prepare("UPDATE users SET avatar=:filename WHERE id=:id");
            $stmt->bindParam(":id", $this->user_id);
            $stmt->bindParam(":filename", $filename);
            if($stmt->execute()) {return true;}
            return false;
        }

        //      ____      _     ____        _        
        //     / ___| ___| |_  |  _ \  __ _| |_ __ _ 
        //    | |  _ / _ \ __| | | | |/ _` | __/ _` |
        //    | |_| |  __/ |_  | |_| | (_| | || (_| |
        //     \____|\___|\__| |____/ \__,_|\__\__,_|

        public function load_profile($username) {
            $query = "SELECT * FROM users WHERE username=:username";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->execute();
            $profile = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];

            $this->user_id = $profile['id'];
            $this->username = $profile['username'];
            $this->email = $profile['email'];
            $this->privileges = $profile['privileges'];
            $this->registration_date = $profile['registration_date'];
            $this->avatar = $profile['avatar'];
            $this->is_admin =  $profile['privileges'] === 'admin' ? true : false;
            $this->total_threads = $this->count_threads($this->user_id);
            $this->total_posts = $this->count_posts($this->user_id);
        }

        private function get_profile($username) {
            $query = "SELECT * FROM users WHERE username=:username";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->execute();
            $profile = $stmt->fetchAll(PDO::FETCH_ASSOC);
            var_dump($profile);
            exit();
        }

        public function count_threads($user_id) {
            $query = "SELECT COUNT(*) as count FROM threads WHERE user_id=:user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);

            if ($stmt->execute()) {
                return ($stmt->fetchAll(PDO::FETCH_ASSOC)[0])['count'];
            }
            return 0;
        }

        public function get_my_threads($user_id) {
            $query = "SELECT * FROM threads WHERE user_id=:user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            
            if ($stmt->execute() && $stmt->rowCount() > 0) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            return [];
        }

        public function count_posts($user_id) {
            $query = "SELECT COUNT(*) as count FROM posts WHERE user_id=:user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);

            if ($stmt->execute()) {
                return ($stmt->fetchAll(PDO::FETCH_ASSOC)[0])['count'];
            }
            return 0;
        }

        public function get_my_posts($user_id) {
            $query = "SELECT * FROM posts WHERE user_id=:user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            
            if ($stmt->execute() && $stmt->rowCount() > 0) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            return [];
        }

        public function getData($username, $column) {

            // 1. Comprobar que el usuario existe
            if($this->is_registered($username) == false) {
                return "not_registered";
            } 

            // 2. Comprobar que la columna existe
            $query = "SHOW COLUMNS FROM users WHERE Field=:column";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":column", $column);
            $stmt->execute();

            if($stmt->rowCount() == 0) {
                return "column_not_found";
            }

            // 4. Obtener datos con la query
            $query = "SELECT :column FROM users WHERE username=:username";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":column", $column);
            $stmt->bindParam(":username", $username);
            $stmt->execute();
            
            // 5. Devolver los datos
            $columnData = $stmt->fetch(PDO::FETCH_NUM);
            return $columnData[0];

        }

        private function get_password($id) {
            $query = "SELECT password FROM users WHERE id=:id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam("id", $id);
            
            if ($stmt->execute() && $stmt->rowCount() > 0) {
                // var_dump($stmt->fetchAll(PDO::FETCH_ASSOC)[0]['password']);
                // exit();
                return $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['password'];
            }
            return [];
        }
    }
 
?>