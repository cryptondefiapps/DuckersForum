<?php
//  _____ ___  ____  _   _ __  __   __  __  ___  ____  _____ _     
// |  ___/ _ \|  _ \| | | |  \/  | |  \/  |/ _ \|  _ \| ____| |    
// | |_ | | | | |_) | | | | |\/| | | |\/| | | | | | | |  _| | |    
// |  _|| |_| |  _ <| |_| | |  | | | |  | | |_| | |_| | |___| |___ 
// |_|   \___/|_| \_\\___/|_|  |_| |_|  |_|\___/|____/|_____|_____|
//
                                                                
    class ForumModel {
        private $conn;

        // Constructor
        public function __construct($db) {
            $this->conn = $db;
        }

        //  ____            _   _                 
        // / ___|  ___  ___| |_(_) ___  _ __  ___ 
        // \___ \ / _ \/ __| __| |/ _ \| '_ \/ __|
        //  ___) |  __/ (__| |_| | (_) | | | \__ \
        // |____/ \___|\___|\__|_|\___/|_| |_|___/                                 

        // Comprueba si una sección existe
        function does_section_exist($section_id) {
            $stmt = $this->conn->prepare("SELECT id FROM sections WHERE id=:section_id");
            $stmt->bindParam(":section_id", $section_id);
            $stmt->execute();
            if($stmt->rowCount() > 0) {
                return true;
            }
            return false;
        }

        // Crea una sección
        function create_section($title, $description, $user_id) {
            $stmt = $this->conn->prepare("INSERT INTO sections SET title=:title, description=:description, user_id=:user_id");
            $stmt->bindParam(":title", $title);
            $stmt->bindParam(":description", $description);
            $stmt->bindParam(":user_id", $user_id);
            if($stmt->execute()) {
                return true;
            }
            return false;
        }

        // Edita una sección
        function edit_section($section_id, $title, $description){
            if(!$this->does_section_exist($section_id)) {
                return "section_not_exist";
            }
            $stmt = $this->conn->prepare("UPDATE sections SET title=:title, description=:description WHERE id=:id");
            $stmt->bindParam(":title", $title);
            $stmt->bindParam(":description", $description);
            $stmt->bindParam(":id", $section_id);
            if($stmt->execute()) {
                return true;
            }
            return false;
        }

        // Elimina una sección
        function delete_section($section_id) {
            if(!$this->does_section_exist($section_id)) {
                return "section_not_exist";
            }
            $stmt = $this->conn->prepare("DELETE FROM sections WHERE id=:section_id");
            $stmt->bindParam(":section_id", $section_id);
            if($stmt->execute()) {
                return true;
            }
            return false;
        }

        // Obtiene array data de todas las secciones
        public function get_sections() {
            $stmt = $this->conn->prepare("SELECT * FROM sections");
            $stmt->execute();
            if ($stmt->rowCount() > 0) { 
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            return [];
        }

        // Obtiene array data de una sección
        public function get_section_data($section_id) {
            if(!$this->does_section_exist($section_id)) {
                return "section_not_exist";
            }
            $stmt = $this->conn->prepare("SELECT * FROM sections WHERE id=:section_id");
            $stmt->bindParam(":section_id", $section_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
        }

        // Obtiene array data con los threads de una sección
        public function get_section_threads($section_id) {
            $stmt = $this->conn->prepare("SELECT * FROM threads WHERE section_id=:section_id ORDER BY creation_date DESC");
            $stmt->bindParam(":section_id", $section_id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            return [];
        }

        // Cuenta el número de threads de una sección
        public function count_section_threads($section_id) {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM threads WHERE section_id=:section_id");
            $stmt->bindParam(":section_id", $section_id);
            if ($stmt->execute()) {
                return ($stmt->fetchAll(PDO::FETCH_ASSOC)[0])['count'];
            }
            return 0;
        }

        // Cuenta el número de posts en una sección
        public function count_section_posts($section_id) {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM posts WHERE section_id=:section_id");
            $stmt->bindParam(":section_id", $section_id);
            if ($stmt->execute()) {
                return ($stmt->fetchAll(PDO::FETCH_ASSOC)[0])['count'];
            }
            return 0;
        }
        
        //  _____ _                        _     
        // |_   _| |__  _ __ ___  __ _  __| |___ 
        //   | | | '_ \| '__/ _ \/ _` |/ _` / __|
        //   | | | | | | | |  __/ (_| | (_| \__ \
        //   |_| |_| |_|_|  \___|\__,_|\__,_|___/
        
        // Comprueba si un thread existe
        public function does_thread_exist($thread_id) {
            if(empty($thread_id)) {
                return "empty_data";
            }
            $stmt = $this->conn->prepare("SELECT id FROM threads WHERE id=:thread_id");
            $stmt->bindParam(":thread_id", $thread_id);
            $stmt->execute();
            if($stmt->rowCount() > 0) {
                return true;
            }
            return false;
        }

        // Comprueba si el usuario logeado es el creador del thread
        public function is_user_thread_owner($thread_id, $user_id) {
            $stmt = $this->conn->prepare("SELECT id FROM threads WHERE id=:thread_id AND user_id=:user_id");
            $stmt->bindParam(":thread_id", $thread_id);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            if($stmt->rowCount() > 0) {
                return true;
            }
            return false;
        }

        // Crea un thread
        public function create_thread($title, $msg, $section_id, $user_id) {
            if(!$this->does_section_exist($section_id)) {
                return "section_not_exist";
            }
            $stmt = $this->conn->prepare("INSERT INTO threads SET title=:title, msg=:msg, section_id=:section_id, user_id=:user_id");
            $stmt->bindParam(":title", $title);
            $stmt->bindParam(":msg", $msg);
            $stmt->bindParam(":section_id", $section_id);
            $stmt->bindParam(":user_id", $user_id);
            if($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        }

        // Edita un thread
        public function edit_thread($thread_id, $title, $msg){
            if(!$this->does_thread_exist($thread_id)) {
                return "thread_not_exist";
            }
            $stmt = $this->conn->prepare("UPDATE threads SET title=:title, msg=:msg WHERE id=:id");
            $stmt->bindParam(":title", $title);
            $stmt->bindParam(":msg", $msg);
            $stmt->bindParam(":id", $thread_id);
            if($stmt->execute()) {
                return true;
            }
            return false;
        }

        // Eliminar todos los post dentro de un thread
        public function delete_posts_from_thread($thread_id) {
            if(!$this->does_thread_exist($thread_id)) {
                return "thread_not_exist";
            }
            $stmt = $this->conn->prepare("DELETE FROM posts WHERE thread_id=:thread_id");
            $stmt->bindParam(":thread_id", $thread_id);
            $stmt->execute();
        }

        // Eliminar un thread
        public function delete_thread($thread_id) {
            if(!$this->does_thread_exist($thread_id)) {
                return "thread_not_exist";
            }
            // Elimina los posts que existen dentro del thread
            $this->delete_posts_from_thread($thread_id);
            $stmt = $this->conn->prepare("DELETE FROM threads WHERE id=:thread_id");
            $stmt->bindParam(":thread_id", $thread_id);
            if($stmt->execute()) {
                return true;
            }
            return false;
        }

        // Obtener data de un thread
        public function get_thread($thread_id) {
            if(!$this->does_thread_exist($thread_id)){
                return 'thread_not_exist';
            }
            $stmt = $this->conn->prepare("SELECT * FROM threads WHERE id=:thread_id");
            $stmt->bindParam(":thread_id", $thread_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
        }

        // Obtener los replies o posts de un thread
        public function get_thread_posts($thread_id, $limit, $max_rows) {
            $stmt = $this->conn->prepare("SELECT * FROM posts WHERE thread_id=:thread_id LIMIT :limit,:max_rows");
            $stmt->bindParam(":thread_id", $thread_id);
            $stmt->bindValue(':limit', (int) trim($limit), PDO::PARAM_INT);
            $stmt->bindValue(':max_rows', (int) trim($max_rows), PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            return [];
        }
        
        // Contar el número de replies o posts que tiene un thread
        public function count_thread_posts($thread_id) {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM posts WHERE thread_id=:thread_id");
            $stmt->bindParam(":thread_id", $thread_id);
            if ($stmt->execute()) {
                return ($stmt->fetchAll(PDO::FETCH_ASSOC)[0])['count'];
            }
            return 0;
        }

        //  ____           _       
        // |  _ \ ___  ___| |_ ___ 
        // | |_) / _ \/ __| __/ __|
        // |  __/ (_) \__ \ |_\__ \
        // |_|   \___/|___/\__|___/
       
        // Comprueba si un post existe
        public function does_post_exist($post_id) {
            if(empty($post_id)) {
                return "empty_data";
            }
            $stmt = $this->conn->prepare("SELECT id FROM posts WHERE id=:post_id");
            $stmt->bindParam(":post_id", $post_id);
            $stmt->execute();
            if($stmt->rowCount() > 0) {
                return true;
            }
            return false;
        }

        // Comprueba si el usuario logeado es el creador del post
        public function is_user_post_owner($post_id, $user_id) {
            $stmt = $this->conn->prepare("SELECT id FROM posts WHERE id=:post_id AND user_id=:user_id");
            $stmt->bindParam(":post_id", $post_id);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            if($stmt->rowCount() > 0) {
                return true;
            }
            return false;
        }

        // Crea un post
        public function create_post($section_id, $thread_id, $user_id, $msg) {
            if(!$this->does_thread_exist($thread_id)) {
                return "thread_not_exist";
            }
            $stmt = $this->conn->prepare("INSERT INTO posts SET section_id=:section_id, thread_id=:thread_id, user_id=:user_id, msg=:msg");
            $stmt->bindParam(":section_id", $section_id);
            $stmt->bindParam(":thread_id", $thread_id);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":msg", $msg);
            if($stmt->execute()) {
                return true;
            }
            return false;
        }

        // Edita un post
        public function edit_post($post_id, $msg){
            if(!$this->does_post_exist($post_id)) {
                return "post_not_exist";
            }
            $stmt = $this->conn->prepare("UPDATE posts SET msg=:msg WHERE id=:post_id");
            $stmt->bindParam(":msg", $msg);
            $stmt->bindParam(":post_id", $post_id);
            if($stmt->execute()) {
                return true;
            }
            return false;
        }

        // Elimina un post
        public function delete_post($post_id) {
            if(!$this->does_post_exist($post_id)) {
                return "post_not_exist";
            }
            $stmt = $this->conn->prepare("DELETE FROM posts WHERE id=:post_id");
            $stmt->bindParam(":post_id", $post_id);
            if($stmt->execute()) {
                return true;
            }
            return false;
        }

        // Obtiene array data de un post
        public function get_post ($post_id) {
            $stmt = $this->conn->prepare("SELECT * FROM posts WHERE id=:post_id");
            $stmt->bindParam(":post_id", $post_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
        }

        //  ____             __ _ _      
        // |  _ \ _ __ ___  / _(_) | ___ 
        // | |_) | '__/ _ \| |_| | |/ _ \
        // |  __/| | | (_) |  _| | |  __/
        // |_|   |_|  \___/|_| |_|_|\___|
                                      
        // Obtiene el nombre de usuario por la id del usuario
        public function get_username_by_user_id($user_id) {
            $stmt = $this->conn->prepare("SELECT username FROM users WHERE id=:user_id");
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return ($stmt->fetchAll(PDO::FETCH_ASSOC)[0])['username'];
            }
            return false;
        }

        // Obtiene la fecha en la que se inscribió el usuario
        public function get_joined_date_by_user_id($user_id) {
            $stmt = $this->conn->prepare("SELECT registration_date FROM users WHERE id=:user_id");
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return ($stmt->fetchAll(PDO::FETCH_ASSOC)[0])['registration_date'];
            }
            return false;
        }

        public function get_user_avatar($user_id) {
            $stmt = $this->conn->prepare("SELECT avatar FROM users WHERE id=:user_id");
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return ($stmt->fetchAll(PDO::FETCH_ASSOC)[0])['avatar'];
            }
            return false;
        }


    }
 
?>