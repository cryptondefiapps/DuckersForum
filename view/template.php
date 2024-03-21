<?php
    if (INIT != "1314") { exit(1); }
    
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Home</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/x-icon" href="images/favicon.png">
        <link rel="stylesheet" href="styles/styles.css" type="text/css">
        <script src="https://kit.fontawesome.com/cdb3baf29a.js" crossorigin="anonymous"></script>
        <script src="js/tinymce/tinymce.min.js" type="text/javascript"></script>
        <script type="text/javascript">  
            function menuToggle(selector) {
                const toggleMenu = document.querySelector(`#${selector}`);
                toggleMenu.classList.toggle("active");
            }
        </script>
    </head>
    <body>
        <header>
            <article>
                <a href="index.php"><img class="logo" src="images/duckers.png" width="100"></a>
            </article>
            <article id="logout-container">
                <?php
                // SI NO HAY UNA SESION INICIADA
                if(!isset($_SESSION['username'])){
                ?> 
                    <label id="login"><a href="index.php?view=login">Login</a></label>
            </article>
                <?php
                }
                // SI EXISTE UNA SESION INICIADA
                else {
                ?>  
                    <nav id="navbar" class="nav">
                        <div class="profile" id="user-container" onclick="menuToggle('profile-menu')">
                            <img src="<?=$avatarSrc?>" alt="avatar" width="60" height="60">
                            <p><?=$_SESSION['username']?></p>
                        </div>
                        <div id="profile-menu" class="menu">
                            <ul class="nav-list">
                                <li>
                                    <i class="fa-solid fa-user"></i>
                                    <a href="index.php?view=profile">My profile</a>
                                </li>
                                <li>
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                    <a href="index.php?action=logout">Logout</a>
                                </li>
                            </ul>
                        </div>
                    </nav>
            </article>
            <?php
                }
            ?>  
        </header>
        <section id="content-wp">
            <?php
            include $view;
            ?>
        </section>
    </body>
</html>