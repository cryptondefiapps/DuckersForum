<?php
    //  ____       _                 
    // |  _ \  ___| |__  _   _  __ _ 
    // | | | |/ _ \ '_ \| | | |/ _` |
    // | |_| |  __/ |_) | |_| | (_| |
    // |____/ \___|_.__/ \__,_|\__, |
    //                         |___/ 

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    $debug = false;

    //  ____                            _                 _           
    // |  _ \  ___ _ __   ___ _ __   __| | ___ _ __   ___(_) ___  ___ 
    // | | | |/ _ \ '_ \ / _ \ '_ \ / _` |/ _ \ '_ \ / __| |/ _ \/ __|
    // | |_| |  __/ |_) |  __/ | | | (_| |  __/ | | | (__| |  __/\__ \
    // |____/ \___| .__/ \___|_| |_|\__,_|\___|_| |_|\___|_|\___||___/
    //            |_|                                                 
    session_start();
    const INIT = "1314";
    require_once 'config/Database.php';
    require_once 'controller/UserController.php';
    require_once 'controller/ForumController.php';
    require_once 'controller/DataController.php';

    $userController = new UserController();
    $forumController = new ForumController($userController);
    $avatarSrc = $userController->get_avatar() === 'default' ? 'images/default-user.jpg' : 'uploads/'.$userController->get_avatar();

    //  ____        __             _ _                            
    // |  _ \  ___ / _| __ _ _   _| | |_   _ __   __ _  __ _  ___ 
    // | | | |/ _ \ |_ / _` | | | | | __| | '_ \ / _` |/ _` |/ _ \
    // | |_| |  __/  _| (_| | |_| | | |_  | |_) | (_| | (_| |  __/
    // |____/ \___|_|  \__,_|\__,_|_|\__| | .__/ \__,_|\__, |\___|
    //                                    |_|          |___/      

    if (!isset($_GET['action']) && !isset($_GET['view'])) {
        header("Location: index.php?view=home");
        exit();
    }

    //  ____                       _ _            ____ _               _    
    // / ___|  ___  ___ _   _ _ __(_) |_ _   _   / ___| |__   ___  ___| | __
    // \___ \ / _ \/ __| | | | '__| | __| | | | | |   | '_ \ / _ \/ __| |/ /
    //  ___) |  __/ (__| |_| | |  | | |_| |_| | | |___| | | |  __/ (__|   < 
    // |____/ \___|\___|\__,_|_|  |_|\__|\__, |  \____|_| |_|\___|\___|_|\_\
    //                                   |___/                              

    if (isset($_GET['action']) && isset($_GET['view'])) {
        echo "Security flagged.";
        exit();
    }

    //      _        _   _                ____            _             _ _           
    //     / \   ___| |_(_) ___  _ __    / ___|___  _ __ | |_ _ __ ___ | | | ___ _ __ 
    //    / _ \ / __| __| |/ _ \| '_ \  | |   / _ \| '_ \| __| '__/ _ \| | |/ _ \ '__|
    //   / ___ \ (__| |_| | (_) | | | | | |__| (_) | | | | |_| | | (_) | | |  __/ |   
    //  /_/   \_\___|\__|_|\___/|_| |_|  \____\___/|_| |_|\__|_|  \___/|_|_|\___|_|   
                                                                               
    if (isset($_GET['action'])) {
        // User login
        if ($_GET['action'] === 'login') {
            $data = $userController->login($_POST['username'], $_POST['password']);
            header("Location: " .$data['redirectUrl']);
        }

        // User logout
        if ($_GET['action'] === "logout") {
            $data = $userController->logout();
            header("Location: " .$data['redirectUrl']);
        }
        
        // User registration
        if ($_GET['action'] === 'register') {
            // Requerir que el usuario no esté ya logueado
            $userController->requireNotLoggedIn();
            
            // Comprueba si alguno de los datos están vacíos o si no existen
            if(!isset($_POST['username']) || empty($_POST['username']) || !isset($_POST['password']) || empty($_POST['password'])|| !isset($_POST['email']) || empty($_POST['email'])) {
                header("Location: index.php?view=register&error=empty_data");
                exit();
            }

            // Registra al usuario
            $data = $userController->register($_POST['username'], $_POST['email'], $_POST['password']);
            header("Location: " .$data['redirectUrl']);
        }

        // Create section
        if ($_GET['action'] === "create_section") {
            $jsonData = DataController::decodeJson();
            DataController::returnJson($forumController->create_section($jsonData->title, $jsonData->description));
        }

        // Edit section
        if ($_GET['action'] === "edit_section") {
            $jsonData = DataController::decodeJson();
            DataController::returnJson($forumController->edit_section($jsonData->id, $jsonData->title, $jsonData->description));
        }

        // Delete section
        if ($_GET['action'] === "delete_section") {
            DataController::returnJson($forumController->delete_section($_GET['section_id']));
        }

        // Create thread
        if ($_GET['action'] === "create_thread") {
            $userController->loginRequired();
            $jsonData = DataController::decodeJson();
            DataController::returnJson($forumController->create_thread($jsonData->title, $jsonData->msg, $_GET['section'], $userController->user_id));
        }

        // Edit thread
        if ($_GET['action'] === "edit_thread") {
            $jsonData = DataController::decodeJson();
            DataController::returnJson($forumController->edit_thread($jsonData->id, $jsonData->title, $jsonData->msg));
        }

        // Delete thread
        if ($_GET['action'] === "delete_thread") {
            DataController::returnJson($forumController->delete_thread($_GET['id']));
        }

        // Create post
        if ($_GET['action'] === "create_post") {
            $jsonData = DataController::decodeJson();
            DataController::returnJson($forumController->create_post($_GET['section'], $_GET['thread'], $userController->get_user_id(), $jsonData->msg));
        }

        // Delete post
        if ($_GET['action'] === "delete_post") {
            DataController::returnJson($forumController->delete_post($_GET['id']));
        }

        // Edit post
        if ($_GET['action'] === "edit_post") {
            $jsonData = DataController::decodeJson();
            DataController::returnJson($forumController->edit_post($jsonData->id, $jsonData->msg));
        }

        // Edit password
        if ($_GET['action'] === "edit_password") {
            $jsonData = DataController::decodeJson();
            DataController::returnJson($userController->edit_password($jsonData->password, $jsonData->currentpass));
        }

        if ($_GET['action'] === "avatar_upload") {
            $origFilename = basename($_FILES['avatar']['name']);
            $ext = pathinfo($origFilename, PATHINFO_EXTENSION);
            $filename = md5($origFilename).".".$ext;
            if (!move_uploaded_file($_FILES['avatar']['tmp_name'], "./uploads/$filename")) {
                DataController::returnJson(DataController::generateData(1, "error", ""));
            }
            $userController->save_avatar($filename);
            DataController::returnJson(DataController::generateData(0, "ok", "", [
                "filename" => $filename
            ]));
            

        }
        
        exit();
    }
  
    // __     ___                  ____            _             _ _           
    // \ \   / (_) _____      __  / ___|___  _ __ | |_ _ __ ___ | | | ___ _ __ 
    //  \ \ / /| |/ _ \ \ /\ / / | |   / _ \| '_ \| __| '__/ _ \| | |/ _ \ '__|
    //   \ V / | |  __/\ V  V /  | |__| (_) | | | | |_| | | (_) | | |  __/ |   
    //    \_/  |_|\___| \_/\_/    \____\___/|_| |_|\__|_|  \___/|_|_|\___|_|   

    if (isset($_GET['view'])) {
        // HOME
        if ($_GET['view'] === 'home') {
            $data = $forumController->get_sections()['data'];
            $view = "view/home.php";
        }

        // REGISTER
        if ($_GET['view'] === 'register') {
            $userController->requireNotLoggedIn();
            $view = 'view/register.php';
        }

        // LOGIN
        if ($_GET['view'] === 'login') {
            $userController->requireNotLoggedIn();
            $view = "view/login.php";
        }

        // THREADS
        if ($_GET['view'] === 'threads') {
            $response = $forumController->get_section_threads($_GET['section']);
            if($response['status'] === 1) {
                $forumController->redirectToHome();
            }
            $data = $response['data'];
            $view = "view/threads.php";
        }

        // POSTS
        if ($_GET['view'] === 'posts') {
            $response = $forumController->get_thread($_GET['section'], $_GET['thread'], !isset($_GET['page']) ? 1 : $_GET['page']);
            if($response['status'] === 1) {
                $forumController->redirectToHome();
            }
            $data = $response['data'];
            $view = "view/posts.php";
        }

        // PROFILE
        if($_GET['view'] === 'profile') {
            $userController->loginRequired();
            $view = "view/profile.php";
        }

        // MY THREADS
        if($_GET['view'] === 'mythreads') {
            $userController->loginRequired();
            $data = $userController->get_my_threads()['data'];
            $view = "view/mythreads.php";
        }

        // MY POSTS
        if($_GET['view'] === 'myposts') {
            $userController->loginRequired();
            $data = $userController->get_my_posts()['data'];
            $view = "view/myposts.php";
        }

        include 'view/template.php';
        exit();
    }

?>
