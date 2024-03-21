<?php
    if (INIT != "1314") { exit(1); }
    if ($debug) {
        echo '<pre>' , var_dump($data) , '</pre>';
    }
    //  ____   ___  ____ _____ ____   __     _____ _______        __
    // |  _ \ / _ \/ ___|_   _/ ___|  \ \   / /_ _| ____\ \      / /
    // | |_) | | | \___ \ | | \___ \   \ \ / / | ||  _|  \ \ /\ / / 
    // |  __/| |_| |___) || |  ___) |   \ V /  | || |___  \ V  V /  
    // |_|    \___/|____/ |_| |____/     \_/  |___|_____|  \_/\_/                                                                
?>
<script>

    //   _____                 _   _                 
    //  |  ___|   _ _ __   ___| |_(_) ___  _ __  ___ 
    //  | |_ | | | | '_ \ / __| __| |/ _ \| '_ \/ __|
    //  |  _|| |_| | | | | (__| |_| | (_) | | | \__ \
    //  |_|   \__,_|_| |_|\___|\__|_|\___/|_| |_|___/

    let editToggled = false

    // THREADS
    async function edit_thread(threadId) {
       // Confirmar antes de editar
       if (!confirm('Are you sure you want to edit this thread?')) {
            return
        }

        // const msgValue = document.querySelector('.esmsg_input').value
        const msgValue = tinymce.activeEditor.getContent('.esmsg_input')
        const titleValue = document.querySelector('.estitle_input').value

        console.log(msgValue, titleValue, threadId)
        // return
        
        // Llamar al servidor para editar la seccion
        try {
            const response = await fetch("index.php?action=edit_thread", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    title: titleValue,
                    msg: msgValue,
                    id: threadId
                })
            })
            // console.log(await response.text())
            const jsonData = await response.json()
            console.log(jsonData)
            if (jsonData.status === 0) {
                window.location.href = window.location.href
            }
        }
        catch (e) {
            console.log(e)
        }
        // Volver a mostrar los elementos HTML como antes (h2, p)
    }

    function toggle_edit_thread(threadId) {
        if(editToggled === true){
            return
        }
        editToggled = true; 
        const title = document.querySelector(`.thread_title_${threadId}`)     
        const msg = document.querySelector(`.thread_msg_${threadId}`)
        const content = document.querySelector(`.thread_content_${threadId}`)

        // Replace title with input
        const titleInput = document.createElement('input')
        titleInput.value = title.textContent;
        titleInput.classList = "estitle_input"
        title.parentNode.replaceChild(titleInput, title);

        // Replace msg with input
        const msgInput = document.createElement('textarea')
        msgInput.value = msg.textContent;
        msgInput.rows = 10

        // editing_section_description_input
        msgInput.classList = "esmsg_input"
        msg.parentNode.replaceChild(msgInput, msg);

        // Create sendButton
        const sendButton = document.createElement('button')
        sendButton.textContent = "Edit"
        sendButton.onclick = function() {
            edit_thread(threadId)
        }
        const textarea = document.querySelector(".esmsg_input")
        textarea.parentNode.appendChild(sendButton)
        // content.appendChild(sendButton)

            // Edit Thread
        tinymce.init({
            selector: '.esmsg_input',
            width: "100%",
            height: 300,
            menubar: false,
            plugins: 'emoticons wordcount',
            toolbar: 'undo redo | formatselect | ' +
            'bold italic backcolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'removeformat | emoticons',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
            newline_behavior: 'linebreak',
            max_chars: 2000,
            setup: function(editor) {

                // Evento para manejar cambios de teclado y pegado
                editor.on('keydown keyup', function(e) {
                    // Si es keydown, verifica si se debe permitir la entrada basada en el conteo de caracteres
                    if (e.type === 'keydown') {
                        const content = editor.getContent({format: 'text'});
                        if (content.length >= 2000 && e.keyCode !== 8 && e.keyCode !== 46) { // 8 es backspace, 46 es delete
                            e.preventDefault();
                        }
                    }
                });

                // Manejar el evento de pegado para limitar el contenido
                editor.on('paste', function(e) {
                    e.preventDefault();
                    const text = (e.clipboardData || window.clipboardData).getData('text').substring(0, 2000);
                    const content = editor.getContent({format: 'text'});
                    if (content.length + text.length > 2000) {
                        // Calcula cuántos caracteres más se pueden pegar
                        const allowedLength = 2000 - content.length;
                        const trimmedText = text.substring(0, allowedLength);
                        editor.insertContent(trimmedText);
                    }
                    else {
                        editor.insertContent(text);
                    }
                });

            }
        });
    }

    async function delete_thread(threadId) {
        // Confirmar antes de eliminar
        if (!confirm('Are you sure you want to delete this thread?')) {
            return;
        }

        const response = await fetch(`index.php?action=delete_thread&id=${threadId}`)
        console.log(await response.text())

        const jsonData = await response.json()

        if(jsonData.status === 0) {
            //window.location.href = jsonData['redirectUrl']
        }
    }

    // POSTS
    async function create_post() {

        const msgValue = tinymce.activeEditor.getContent('#editor')
        // Llamar al servidor para crear el post
        try {
            const response = await fetch("index.php?action=create_post&section=<?=$data['thread']['section_id']?>&thread=<?=$data['thread']['id']?>", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    msg: msgValue
                })
            })
            const jsonData = await response.json()
            console.log(jsonData)
            if (jsonData.status === 0) {
                window.location.href = window.location.href
            }
        }
        catch (e) {
            console.log(e)
        } 
    }

    async function edit_post(postId) {
       // Confirmar antes de editar
       if (!confirm('Are you sure you want to edit this post?')) {
            return
        }
        
        //const msgValue = document.querySelector('.esmsg_input').value
        const msgValue = tinymce.activeEditor.getContent('.esmsg_input')

        // Llamar al servidor para editar la seccion
        try {
            const response = await fetch("index.php?action=edit_post", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    msg: msgValue,
                    id: postId
                })
            })
            const jsonData = await response.json()
            console.log(jsonData)
            if (jsonData.status === 0) {
                window.location.href = window.location.href
            }
        }
        catch (e) {
            console.log(e)
        }
    }

    function toggle_edit_post(postId) {  
        if(editToggled === true){
            return
        }
        editToggled = true;  
        const msg = document.querySelector(`.post_msg_${postId}`)
        const content = document.querySelector(`.post_content_${postId}`)

        // Replace msg with input
        const msgInput = document.createElement('textarea')
        msgInput.value = msg.textContent;
        msgInput.rows = 10
        // editing_section_description_input
        msgInput.classList = "esmsg_input"
        msg.parentNode.replaceChild(msgInput, msg);

        // Create sendButton
        const sendButton = document.createElement('button')
        sendButton.textContent = "Edit"
        sendButton.onclick = function() {
            edit_post(postId)
        }
        sendButton.style.marginTop = '20px'
        const textarea = document.querySelector(".esmsg_input")
        textarea.parentNode.appendChild(sendButton)

        tinymce.init({
            selector: '.esmsg_input',
            width: "100%",
            height: 300,
            menubar: false,
            plugins: 'emoticons',
            toolbar: 'undo redo | formatselect | ' +
            'bold italic backcolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'removeformat | emoticons',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
            max_chars: 2000,
            newline_behavior: 'linebreak'
        });
    }

    async function delete_post(postId) {
        // Confirmar antes de eliminar
        if (!confirm('Are you sure you want to delete this post?')) {
            return;
        }

        const response = await fetch(`index.php?action=delete_post&id=${postId}`) 
        const jsonData = await response.json()

        if(jsonData.status === 0) {
            window.location.href = window.location.href
        }
    }

    // Quick reply
    tinymce.init({
        selector: '#editor',
        width: "95%",
        height: 300,
        menubar: false,
        plugins: 'emoticons wordcount',
        toolbar: 'undo redo | formatselect | ' +
        'bold italic backcolor | alignleft aligncenter ' +
        'alignright alignjustify | bullist numlist outdent indent | ' +
        'removeformat | emoticons',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
        max_chars: 2000,
        newline_behavior: 'linebreak',
        setup: function(editor) {

            // Evento para manejar cambios de teclado y pegado
            editor.on('keydown keyup', function(e) {
                // Si es keydown, verifica si se debe permitir la entrada basada en el conteo de caracteres
                if (e.type === 'keydown') {
                    const content = editor.getContent({format: 'text'});
                    if (content.length >= 2000 && e.keyCode !== 8 && e.keyCode !== 46) { // 8 es backspace, 46 es delete
                        e.preventDefault();
                    }
                }
            });

            // Manejar el evento de pegado para limitar el contenido
            editor.on('paste', function(e) {
                e.preventDefault();
                const text = (e.clipboardData || window.clipboardData).getData('text').substring(0, 2000);
                const content = editor.getContent({format: 'text'});
                if (content.length + text.length > 2000) {
                    // Calcula cuántos caracteres más se pueden pegar
                    const allowedLength = 2000 - content.length;
                    const trimmedText = text.substring(0, allowedLength);
                    editor.insertContent(trimmedText);
                }
                else {
                    editor.insertContent(text);
                }
            });

        }
    });

</script>

<!-- 
 ____  _                   _                  
/ ___|| |_ _ __ _   _  ___| |_ _   _ _ __ ___ 
\___ \| __| '__| | | |/ __| __| | | | '__/ _ \
 ___) | |_| |  | |_| | (__| |_| |_| | | |  __/
|____/ \__|_|   \__,_|\___|\__|\__,_|_|  \___| 
--> 
<nav>
    <ul>
        <a href="index.php?view=home"><span><i class="fa-solid fa-house"></i></span></a>
        <span>
            <span>
                <a href="index.php?view=home">DuckersForums</a>
            </span>
            <span>
                <i class="fa-solid fa-angle-right"></i>
            </span>
            <span>
                <a href="index.php?view=threads&section=<?=$data['section']['id']?>"><?=$data['section']['title']?></a>
            </span>
            <span>
                <i class="fa-solid fa-angle-right"></i>
            </span>
            <span>
                <strong><?=$data['thread']['title']?></strong>
            </span>
        </span>
    </ul>
</nav>

<div id="posts-wp">
    <div style="padding: 20px;">
        <span><b>Pages:</b> </span>
        <?php
        $numberOfPages = ceil($data['posts_count'][0] / 5);
        for ($i = 1; $i <= $numberOfPages; $i++) {
        ?>
        <a style="margin-right: 2px;" href="index.php?view=posts&section=<?=$data['section']['id']?>&thread=<?=$data['thread']['id']?>&page=<?=$i?>"><?=$i?></a>
        <?php
        }
        ?>
        
    </div>
    <?php
    if (!isset($_GET['page']) || isset($_GET['page']) && $_GET['page'] === "1") {
        $postId = 2;
    ?>
    <!-- THREAD -->
    <section class="complete-thread thread_<?=$data['thread']['id']?> thread_content_<?=$data['thread']['id']?>">
        <div class="thread-title-wp">
            <section class="reference-wp">
                <p class="reference">#1</p>
                <h3 class="thread_title_<?=$data['thread']['id']?>"><?=$data['thread']['title']?></h3>
            </section>
            <?php
            // SI EL USUARIO ES EL CREADOR DEL THREAD O ES ADMIN
            if($userController->get_is_connected() && $data['thread']['user_id'] == $userController->get_user_id() || $userController->get_is_connected() && $userController->get_is_admin()) {
            ?>
            <div class="dropdown-wp">
                <div class="dropdown ellipsis-wp">
                    <i class="fa-solid fa-ellipsis-vertical c-black"></i>
                    <div class="menu dropdown-content thread-menu">
                    <ul class="nav-list">
                        <li onclick ="toggle_edit_thread(<?=$data['thread']['id']?>)">
                            <i class="fa-regular fa-pen-to-square"></i>
                            <span>Edit</span> 
                        </li>
                        <li onclick ="delete_thread(<?=$data['thread']['id']?>)">
                            <i class="fa-solid fa-trash"></i>
                            <span>Delete</span> 
                        </li>
                    </ul>
                    </div>
                </div> 
            </div>
            <?php
            }
            ?>
        </div>
        <div class="info-thread-wp">
            <!-- THREAD USER PROFILE -->
            <article class="user-profile">
                <p class="username"><?=$forumController->get_username_by_user_id($data['thread']['user_id'])?></p>
                <img src="<?=$forumController->get_user_avatar($data['thread']['user_id'])?>" alt="avatar" width="130" height="130">
                <hr>
                <div class="int-info">
                    <p>Post: <?=$userController->count_posts($data['thread']['user_id']);?></p>
                    <p>Threads: <?=$userController->count_threads($data['thread']['user_id']);?></p>
                    <p>Joined: <?=$forumController->get_joined_date_by_user_id($data['thread']['user_id'])?><p>
                </div>
            </article>
            <article class="thread-msg">
                <p><?=$data['thread']['creation_date']?></p>
                <div class="thread_msg_<?=$data['thread']['id']?> post-msg-plus"><?=$data['thread']['msg']?></div>
            </article>
        </div>
    </section>
    <?php
    }
    ?>
    <!-- POST -->
    <?php
    // POR CADA POST
    if (isset($_GET['page'])) {
        $currentPage = max(1, intval($_GET['page'])); // Aseguramos que la página mínima sea 1
        $postId = ($currentPage - 1) * 5 + 2; // Calcula el primer ID de post para la página actual
    } else {
        $currentPage = 1;
        $postId = 2; // El primer post siempre es 2
    }
    
    foreach($data['posts'] as $post) {
    ?>
    <section class="complete-post">
        <div class="post-title-wp">
            <section  class="reference-wp">
                <p class="reference">#<?=$postId?></p>
            </section>
            <?php
            // SI EL USUARIO ES EL CREADOR DEL POST O ES ADMIN
            if($userController->get_is_connected() && $post['user_id'] == $userController->get_user_id() || $userController->get_is_connected() && $userController->get_is_admin()) {
            ?>
           <div class="dropdown-wp">
                <div class="dropdown ellipsis-wp">
                    <i class="fa-solid fa-ellipsis-vertical c-black"></i>
                    <div class="menu dropdown-content post-menu">
                    <ul class="nav-list">
                        <li onclick ="toggle_edit_post(<?=$post['id']?>)">
                            <i class="fa-regular fa-pen-to-square"></i>
                            <span>Edit</span> 
                        </li>
                        <li onclick ="delete_post(<?=$post['id']?>)">
                            <i class="fa-solid fa-trash"></i>
                            <span>Delete</span> 
                        </li>
                    </ul>
                    </div>
                </div> 
            </div>
            <?php
            }
            ?>
        </div>
        <div class="info-post-wp">
            <!-- POST USER PROFILE -->
            <article class="user-profile">
                <p class="username"><?=$forumController->get_username_by_user_id($post['user_id'])?></p>
                <img src="<?=$forumController->get_user_avatar($post['user_id'])?>" alt="avatar" width="130" height="130">
                <hr>
                <div class="int-info">
                    <p>Post: <?=$userController->count_posts($post['user_id']);?></p>
                    <p>Threads: <?=$userController->count_threads($post['user_id']);?></p>
                    <p>Joined: <?=$forumController->get_joined_date_by_user_id($post['user_id'])?><p>
                </div>
            </article>
            <article class="post-msg post_content_<?=$post['id']?>">
                <p><?=$post['creation_date']?></p>
                <div class="post_msg_<?=$post['id']?> post-msg-plus"><?=$post['msg']?></div>
            </article>
        </div>
    </section>
    <?php
    $postId++;
    }
    ?>
<!-- SI EL USUARIO ESTÁ CONECTADO -->
</div>

<?php if($userController->get_is_connected()) {
?>
    <div id="create-post-wp">
        <article class="create-post">
            <div class="title-form">
                <h3>Quick reply</h3>
            </div>
            <label><textarea id="editor" name="msg" rows="10" cols="20" placeholder="I think that ..."></textarea></label>
            <button class="submit" onclick="create_post()">Create post</button>
            <?php
                if(isset($_GET['msg']) && strtolower($_GET['msg']) == "post_created_success") {
                    echo '<div class="msg-container"><i class="checkmark  fa-solid fa-check"></i><h3>Post has been created successfully</h3></div>';
                }
            ?>
        </article>
    </div>
    <script>
       function characterCount() {
            const wordCount = tinymce.activeEditor.plugins.wordcount;
            alert(wordcount.body.getCharacterCountWithoutSpaces());
        }
    </script>
<?php } ?>







