<?php
// Dependencies
require_once 'config/Database.php';
require_once 'model/ForumModel.php';

//  _____ ___  ____  _   _ __  __    ____ ___  _   _ _____ ____   ___  _     _     _____ ____  
// |  ___/ _ \|  _ \| | | |  \/  |  / ___/ _ \| \ | |_   _|  _ \ / _ \| |   | |   | ____|  _ \ 
// | |_ | | | | |_) | | | | |\/| | | |  | | | |  \| | | | | |_) | | | | |   | |   |  _| | |_) |
// |  _|| |_| |  _ <| |_| | |  | | | |__| |_| | |\  | | | |  _ <| |_| | |___| |___| |___|  _ < 
// |_|   \___/|_| \_\\___/|_|  |_|  \____\___/|_| \_| |_| |_| \_\\___/|_____|_____|_____|_| \_\
                                                                                            
class ForumController {
    private $db;
    public $userController;
    private $model;

    public function __construct($userController) {
        $this->db = (new Database())->getConnection();
        $this->userController = $userController;
        $this->model = new ForumModel($this->db);
    }

    public function redirectToHome() {
        header("Location: index.php");
        exit();
    }

    //  ____            _   _                 
    // / ___|  ___  ___| |_(_) ___  _ __  ___ 
    // \___ \ / _ \/ __| __| |/ _ \| '_ \/ __|
    //  ___) |  __/ (__| |_| | (_) | | | \__ \
    // |____/ \___|\___|\__|_|\___/|_| |_|___/

    // Crea una sección
    public function create_section($title, $description) {
        // Comprueba si el usuario logeado es administrador
        if(!$this->userController->is_admin($this->userController->username)) {
            return DataController::generateData(1, "", "index.php?view=home&error=is_not_an_admin");
        }

        // Comprueba que los datos no estén vacios
        if(empty($title) || empty($description)) {
            return DataController::generateData(1, "", "index.php?view=home&error=empty_data");
        }

        // Comprueba que los datos no midan más de lo estipulado
        $trimmedDescription = strip_tags($description);
        if(strlen($title) > 100 || strlen($trimmedDescription) > 200) {
            return DataController::generateData(1, "", "index.php?view=home&error=incorrect_length");
        }

        // Manda la orden al modelo para que se cree la sección
        $response = $this->model->create_section($title, $description, $this->userController->get_user_id());
        if($response === "section_name_taken") {
            return DataController::generateData(1, "", "index.php?view=home&error=section_name_taken");
        }
        return DataController::generateData(0, "", "index.php?msg=section_created_success");
    }

    // Edita una sección
    public function edit_section($id, $title, $description) {
        // Comprueba si el usuario logeado tiene permisos de administrador
        if (!$this->userController->is_admin()) {
            return DataController::generateData(1, "is_not_admin", "");
        }

        // Comprueba si alguno de los datos se encuentra vacío
        if (empty($id) || empty($title) || empty($description)) {
            return DataController::generateData(1, "empty_data", "");
        }

        // Comprueba que los datos no midan más de lo estipulado
        $trimmedDescription = strip_tags($description);
        if(strlen($title) > 100 || strlen($trimmedDescription) > 200) {
            return DataController::generateData(1, "", "index.php?view=home&error=incorrect_length");
        }

        // Manda la orden al modelo para que se edite la sección
        $response = $this->model->edit_section($id, $title, $description);
        if (!$response) {
            return DataController::generateData(1, "edit_failed", "");
        }
        return DataController::generateData(0, "ok", "");
    }

    // Elimina una sección
    public function delete_section($section_id) {
        // Comprueba si el usuario logeado tiene permisos de administrador
        if(!$this->userController->is_admin($this->userController->username)) {
            return DataController::generateData(1, "is_not_an_admin", "");
        }

        // Manda la orden al modelo para que se elimine la sección
        $response = $this->model->delete_section($section_id);

        // Si el id de la sección no existe
        if($response === 'section_id_does_not_exist') {
            return DataController::generateData(1, $response, "");
        }

        // Si se ha eliminado correctamente
        return DataController::generateData(0, "", "");
    }

    // Obtiene data de todas las secciones
    public function get_sections() {
        // Manda la orden al modelo para obtener un array data con las secciones
        return DataController::generateData(0, "ok", "", ["sections" => $this->model->get_sections()]);
    }

    // Obtiene data sobre una sección
    public function get_section_data($section_id) {
        if(empty($section_id) || !is_numeric($section_id)) {
            return DataController::generateData(1, "empty_or_not_int", "index.php");
        }

        $response = $this->model->get_section_data($section_id);
        if($response === 'section_not_exist') {
            return DataController::generateData(1, $response, "index.php");
        }

        return DataController::generateData(0, "ok", "", ["section" => $this->model->get_section_data($section_id)]);
    }

    // Obtiene data de todos los threads dentro de una sección
    public function get_section_threads($section_id) {
        // Manda la orden al modelo para obtener el data de los threads dentro de una seccion
        if (!$this->model->does_section_exist($section_id)) {
            return DataController::generateData(1, "section_not_exist", "index.php");
        }
        return DataController::generateData(0, "ok", "",[
            "section" => $this->model->get_section_data($section_id), 
            "threads" => $this->model->get_section_threads($section_id)
        ]);
    }

    // Obtiene el numero de threads dentro de una sección
    public function count_section_threads($section_id) {
        // Manda la orden al modelo para obtener el numero de threads dentro de una sección
        return $this->model->count_section_threads($section_id);
    }

    // Obtiene el número de posts dentro de una sección
    public function count_section_posts($section_id) {
        // Manda la orden al modelo para obtener el número de posts dentro de una sección
        return $this->model->count_section_posts($section_id);
    }

    //  _____ _                        _     
    // |_   _| |__  _ __ ___  __ _  __| |___ 
    //   | | | '_ \| '__/ _ \/ _` |/ _` / __|
    //   | | | | | | | |  __/ (_| | (_| \__ \
    //   |_| |_| |_|_|  \___|\__,_|\__,_|___/

    // Crear un thread
    public function create_thread($title, $msg, $section_id, $user_id) {
        // Comprueba si el usuario está conectado
        if(!$this->userController->get_is_connected()) {
            return DataController::generateData(1, "user_not_connected", "");
        }

        // Comprueba si alguno de los datos esta vacío y lanza un error en ese caso
        if (empty($section_id) || empty($title) || empty($msg)) {
            return DataController::generateData(1, "empty_data", "");
        }

        // Comprueba que los datos no midan más de lo estipulado
        $trimmedMsg = strip_tags($msg);
        if(strlen($title) > 100 || strlen($trimmedMsg) > 2000) {
            return DataController::generateData(1, "", "index.php?view=home&error=incorrect_length");
        }

        // Manda la orden al modelo para que se cree el thread
        $response = $this->model->create_thread($title, $msg, $section_id, $this->userController->get_user_id());
        if(!$response) {
            return DataController::generateData(1, "", "index.php?view=threads&section=$section_id&msg=thread_created_error");
        }
        return DataController::generateData(0, "", "index.php?view=posts&section=$section_id&thread=$response");
    }
    
    // Editar un thread
    public function edit_thread($id, $title, $msg) {
        // Comprueba si el usuario está conectado
        if(!$this->userController->get_is_connected()) {
            return DataController::generateData(1, "user_not_connected", "");
        }
        
        // Comprueba si algún dato está vacío
        if (empty($id) || empty($title) || empty($msg)) {
            return DataController::generateData(1, "empty_data", "");
        }

        // Comprobamos si el usuario es dueño del thread o tiene permiso de admin
        if(!$this->model->is_user_thread_owner($id, $this->userController->get_user_id()) && !$this->userController->get_is_admin()) {
            return DataController::generateData(1, "action_not_allowed", "");
        }

        // Comprueba que los datos no midan más de lo estipulado
        $trimmedMsg = strip_tags($msg);
        if(strlen($title) > 100 || strlen($trimmedMsg) > 2000) {
            return DataController::generateData(1, "incorrect_length", "index.php?view=home&error=incorrect_length");
        }

        // Manda la orden al modelo para editar el thread
        $response = $this->model->edit_thread($id, $title, $msg);
        if($response === 'thread_id_does_not_exist') {
            return DataController::generateData(1, "thread_id_does_not_exist", "");
        }
        return DataController::generateData(0, "", "index.php?view=threads&section=$id&msg=thread_edit_success");
    }
    
    // Eliminar un thread
    public function delete_thread($id) {
        // Comprueba si el usuario está conectado
        if(!$this->userController->get_is_connected()) {
            return DataController::generateData(1, "user_not_connected", "");
        }

        // Comprobamos si el usuario es dueño del thread o tiene permiso de admin
        if(!$this->model->is_user_thread_owner($id, $this->userController->get_user_id()) && !$this->userController->get_is_admin()) {
            return DataController::generateData(1, "action_not_allowed", "");
        }

        // Manda la orden al modelo para eliminar el thread
        $response = $this->model->delete_thread($id);
       
        // Si la respuesta es que el thread no existe
        if($response === 'thread_not_exist') {
            return DataController::generateData(1, $response, "");
        }
        return DataController::generateData(0, "ok", "index.php");
    }

    // Obtner la información de un thread
    public function get_thread ($section_id, $thread_id, $page) {
        
        if(empty($thread_id) || empty($section_id)) {
            return DataController::generateData(1, "empty_thread_id", "");
        }

        $sectionResponse = $this->get_section_data($section_id);
        if ($sectionResponse['status'] === 1) {
            return DataController::generateData(1, $sectionResponse, "");
        }

        // Manda la orden al modelo para obtener el data de un thread
        $threadResponse = $this->model->get_thread($thread_id);
        if($threadResponse === 'thread_not_exist' ) {
            return DataController::generateData(1, $threadResponse, "");
        }

        $limit_per_page = 5;
        $first_limit = ($page - 1) * $limit_per_page;
        $postsResponse = $this->get_thread_posts($thread_id, $first_limit, $limit_per_page);
        $postsCount = $this->model->count_thread_posts($thread_id);

        return DataController::generateData(0, "ok", "", [
            "section" => $sectionResponse['data']['section'],
            "thread" => $threadResponse,
            "posts" => $postsResponse,
            "posts_count" => [$postsCount]
        ]);
    }

    // Obtener la información de todos los posts dentro de un thread
    public function get_thread_posts($thread_id, $limit, $max_rows) {
        return $this->model->get_thread_posts($thread_id, $limit, $max_rows);
    }

    // Obtener el número de posts o replies que contiene un thread
    public function count_thread_posts($thread_id) {
        return $this->model->count_thread_posts($thread_id);
    }

    // ____           _       
    // |  _ \ ___  ___| |_ ___ 
    // | |_) / _ \/ __| __/ __|
    // |  __/ (_) \__ \ |_\__ \
    // |_|   \___/|___/\__|___/

    // Crear un post
    public function create_post($section_id, $thread_id, $user_id, $msg) {
        // Comprueba si el usuario está conectado
        if(!$this->userController->get_is_connected()) {
            return DataController::generateData(1, "user_not_connected", "");
        }

        // Comprueba si alguno de los datos está vacío
        if (empty($section_id) || empty($thread_id) || empty($user_id) || empty($msg)) {
            return DataController::generateData(1, "empty_data", "");
        }

        // Comprueba que el msg no mida más de lo estipulado
        $trimmedMsg = strip_tags($msg);
        if(strlen($trimmedMsg) > 2000) {
            return DataController::generateData(1, "", "index.php?view=home&error=incorrect_length");
        }
        
        // Manda la orden al modelo para que se cree el post
        $response = $this->model->create_post($section_id, $thread_id, $user_id, $msg);

        // Si la respuesta es false
        if(!$response) {
            return DataController::generateData(1, "post_created_error", "index.php?view=posts&thread=$thread_id&msg=post_created_error");
        }
        return DataController::generateData(0, "post_created_success", "index.php?view=posts&thread=$thread_id&msg=post_created_success");
    }
    
    // Editar un post
    public function edit_post($post_id, $msg) {
        // Comprueba si el usuario está conectado
        if(!$this->userController->get_is_connected()) {
            return DataController::generateData(1, "user_not_connected", "");
        }

        // Comprueba si está vacío
        if (empty($post_id) || empty($msg)) {
            return DataController::generateData(1, "empty_data", "");
        }

        // Comprueba que el msg no mida más de lo estipulado
        $trimmedMsg = strip_tags($msg);
        if(strlen($trimmedMsg) > 2000) {
            return DataController::generateData(1, "incorrect_length", "index.php?view=home&error=incorrect_length");
        }

        // Comprobamos si el usuario es dueño del post o tiene permiso de admin
        if(!$this->model->is_user_post_owner($post_id, $this->userController->get_user_id()) && !$this->userController->get_is_admin()) {
            return DataController::generateData(1, "action_not_allowed", "");
        }

        // Manda la orden al modelo para que edite el post
        $response = $this->model->edit_post($post_id, $msg);
        // Si la respuesta es que el post no exite
        if($response === 'post_not_exist') {
            return DataController::generateData(1, $response, "");
        }
        return DataController::generateData(0, "ok", "index.php?view=posts");
    }

    // Eliminar un post
    public function delete_post($post_id) {
        // Comprueba si el usuario está conectado
        if(!$this->userController->get_is_connected()) {
            return DataController::generateData(1, "user_not_connected", "");
        }

        // Comprobamos si el usuario es dueño del post o tiene permiso de admin
        if(!$this->model->is_user_post_owner($post_id, $this->userController->get_user_id()) && !$this->userController->get_is_admin()) {
            return DataController::generateData(1, "action_not_allowed", "");
        }

        // Manda la orden al modelo para que elimine el post
        $response = $this->model->delete_post($post_id);
       
        // Si la respuesta es que el post no existe
        if($response === 'post_not_exist') {
            return DataController::generateData(1, "post_not_exist", "");
        }

        return DataController::generateData(0, "ok", "");
    }                    
    
    // Obtener la información de un post
    public function get_post ($post_id) {
        if(empty($post_id)) {
            return DataController::generateData(1, "empty_post_id", "");
        }
        return $this->model->get_post($post_id);
    }
    
    //  ____             __ _ _      
    // |  _ \ _ __ ___  / _(_) | ___ 
    // | |_) | '__/ _ \| |_| | |/ _ \
    // |  __/| | | (_) |  _| | |  __/
    // |_|   |_|  \___/|_| |_|_|\___|

    // Obtener el nombre de usuario por su id
    public function get_username_by_user_id($id) {
        // Manda la orden al modelo para obtener el nombre de usuario
        $username = $this->model->get_username_by_user_id($id);

        // Si no existe nombre de usuario
        if(!$username) {
            return DataController::generateData(1, "error_username", "");
        }

        // Retorna el nombre de usuario
        return $username;
    }

    // Obtener la fecha de registro de un usuario por su id
    public function get_joined_date_by_user_id($user_id) {
        // Manda la orden al modelo para obtener la fecha de regisgtro del usuario
        $response = $this->model->get_joined_date_by_user_id($user_id);

        // Si no se encuentra la fecha
        if(!$response) {
            return DataController::generateData(1, "error_joined_date", "");
        }

        // Retorna la fecha
        return $response;
    }

    public function get_user_avatar($user_id) {
        $response = $this->model->get_user_avatar($user_id);
        if(!$response){
            return 'images/default-user.jpg';
        }
        return "uploads/$response";
    }
}
?>