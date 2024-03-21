<?php
    if (INIT != "1314") { exit(1); }
    //  _   _  ___  __  __ _____  __     _____ _______        __
    // | | | |/ _ \|  \/  | ____| \ \   / /_ _| ____\ \      / /
    // | |_| | | | | |\/| |  _|    \ \ / / | ||  _|  \ \ /\ / / 
    // |  _  | |_| | |  | | |___    \ V /  | || |___  \ V  V /  
    // |_| |_|\___/|_|  |_|_____|    \_/  |___|_____|  \_/\_/   
?>
<script>

    //   _____                 _   _                 
    //  |  ___|   _ _ __   ___| |_(_) ___  _ __  ___ 
    //  | |_ | | | | '_ \ / __| __| |/ _ \| '_ \/ __|
    //  |  _|| |_| | | | | (__| |_| | (_) | | | \__ \
    //  |_|   \__,_|_| |_|\___|\__|_|\___/|_| |_|___/
                                              
    async function create_section() {
        const titleValue = document.querySelector(`#title-editor`).value
        const msgValue = tinymce.activeEditor.getContent('#editor')

        if (!titleValue || !msgValue) {
            document.getElementById('title-editor').checkValidity()
            return false
        }

        // Llamar al servidor para crear el post
        try {
            const response = await fetch('index.php?action=create_section', {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    title: titleValue,
                    description: msgValue
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

    let editToggled = false

    async function edit_section(sectionId) {
       // Confirmar antes de editar
        if (!confirm('Are you sure you want to edit this section?')) {
            return
        }

        const titleValue = document.querySelector('.estitle_input').value
        const descValue = document.querySelector('.esdesc_input').value
        
        // Llamar al servidor para editar la seccion
        try {
            const response = await fetch("index.php?action=edit_section", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    title: titleValue,
                    description: descValue,
                    id: sectionId
                })
            })
            const json = await response.json()
            if (json.msg === 'ok') {
                window.location.href = window.location.href
            }
        }
        catch (e) {
            // console.log(e)
        }
        // Volver a mostrar los elementos HTML como antes (h2, p)
    }

    function toggle_edit_section(sectionId) {   
        if(editToggled === true){
            return
        }
        editToggled = true; 

        const title = document.querySelector(`.section_title_${sectionId}`)
        const description = document.querySelector(`.section_desc_${sectionId}`)
        const content = document.querySelector(`.section_content_${sectionId}`)
        // Replace title with input
        const titleInput = document.createElement('input')
        titleInput.value = title.textContent;
        titleInput.classList = "estitle_input"
        title.parentNode.replaceChild(titleInput, title);

        // Replace description with input
        const descriptionInput = document.createElement('input')
        descriptionInput.value = description.textContent;
        // editing_section_description_input
        descriptionInput.classList = "esdesc_input"
        description.parentNode.replaceChild(descriptionInput, description);

        // Create sendButton
        const sendButton = document.createElement('button')
        sendButton.textContent = "Edit"
        sendButton.onclick = function() {
            edit_section(sectionId)
        }
        content.appendChild(sendButton)


    }

    async function delete_section(sectionId) {
        // Confirmar antes de eliminar
        if (!confirm('Are you sure you want to delete this section?')) {
            return;
        }

        const url = `index.php?action=delete_section&section_id=${sectionId}`
        r = await fetch(url) 
        const j = await r.json()

        if(j.status == 0) {
            const elem = document.querySelector(`.section_${sectionId}`)
            elem.remove()
        }
    }

    function tinymce_updateCharCounter(el, len) {
        $('#' + el.id).prev().find('.char_count').text(len + '/' + el.settings.max_chars);
    }
    function tinymce_getContentLength() {
        return tinymce.get(tinymce.activeEditor.id).contentDocument.body.innerText.length;
    }


    tinymce.init({
        selector: '#editor',
        width: "90%",
        height: 200,
        menubar: false,
        plugins: 'emoticons wordcount', 
        toolbar: 'undo redo | formatselect | emoticons',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
        max_chars: 170,
        setup: function(editor) {

            // // Función para actualizar el conteo y aplicar restricciones
            // function updateCharacterCount() {
            //     const content = editor.getContent({format: 'text'});
            //     const charCount = content.length;

            //     // Si se superan los 170 caracteres, recorta el contenido
            //     if (charCount > 170) {
            //         const trimmedContent = content.substring(0, 170);
            //         editor.setContent(trimmedContent);
            //         // Coloca el cursor al final
            //         editor.selection.select(editor.getBody(), true);
            //         editor.selection.collapse(false);
            //     }

            //     // Aquí puedes actualizar algún elemento de tu UI con charCount si quieres mostrar el conteo
            // }

            // Evento para manejar cambios de teclado y pegado
            editor.on('keydown keyup', function(e) {
                // Si es keydown, verifica si se debe permitir la entrada basada en el conteo de caracteres
                if (e.type === 'keydown') {
                    const content = editor.getContent({format: 'text'});
                    if (content.length >= 170 && e.keyCode !== 8 && e.keyCode !== 46) { // 8 es backspace, 46 es delete
                        e.preventDefault();
                    }
                }

                // Actualiza el conteo después de cualquier cambio
                //updateCharacterCount();
            });

            // Manejar el evento de pegado para limitar el contenido
            editor.on('paste', function(e) {
                e.preventDefault();
                const text = (e.clipboardData || window.clipboardData).getData('text').substring(0, 170);
                const content = editor.getContent({format: 'text'});
                if (content.length + text.length > 170) {
                    // Calcula cuántos caracteres más se pueden pegar
                    const allowedLength = 170 - content.length;
                    const trimmedText = text.substring(0, allowedLength);
                    editor.insertContent(trimmedText);
                }
                else {
                    editor.insertContent(text);
                }
                //updateCharacterCount();
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
        </span>
    </ul>
</nav>
<div id="sections-wp">
    <?php
    // SI EXISTE ALGUNA SECCIÓN
    if (count($data['sections']) === 0) {
        echo "<h2>No sections. Please add.</h2>";
    }
    // POR CADA SECCION 
    foreach($data['sections'] as $section) {
    ?>  <div class="all-section-wp section_<?=$section['id']?>">
            <article class="section-wp">
                <div class="section_content_<?=$section['id']?>">
                    <a href="index.php?view=threads&section=<?=$section['id']?>"><h3 class="section_title_<?=$section['id']?>"><?=$section['title']?></h3></a>
                    <p class="section_desc_<?=$section['id']?>"><?=$section['description']?></p>
                </div>
                <div>
                    <p><?=$forumController->count_section_threads($section['id'])?> Threads</p>
                    <p><?=$forumController->count_section_posts($section['id'])?> Post</p>
                </div>
            </article>
            <?php 
            // SI EL USUARIO ES ADMINISTRADOR
            if($userController->get_is_admin()) {
            ?>
            <div class="dropdown-wp">
                <div class="dropdown ellipsis-wp">
                    <i class="fa-solid fa-ellipsis-vertical c-black"></i>
                    <div class="menu dropdown-content section-menu">
                    <ul class="nav-list">
                        <li onclick ="toggle_edit_section(<?=$section['id']?>)">
                            <i class="fa-regular fa-pen-to-square"></i>
                            <span>Edit</span> 
                        </li>
                        <li onclick ="delete_section(<?=$section['id']?>)">
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
    <?php
    }
    ?>
</div>
<?php
    // ALERTS
    if(isset($_GET['error']) && strtolower($_GET['error']) == "section_name_taken") {
        echo '<div class="error-container"><i class="xmark fa-solid fa-xmark"></i><h2>The section name already exists</h2></div>';
    }
    if(isset($_GET['action']) && strtolower($_GET['action']) == "delete_section") {
        echo '<div class="msg-container"><i class="checkmark  fa-solid fa-check"></i><h3>The section has been deleted successfully</h3></div>';
    }
?>
<!-- SI EL USUARIO ES ADMINISTRADOR -->
<?php if($userController->get_is_admin()) {
?>
<div id="create-section-wp">
    <article class="create-section">
        <div class="title-form">
            <h3>Create new section</h3>
        </div>
            <label><input id="title-editor" type="text" name="title" maxlength="100" placeholder="Title" required></label>
            <label><textarea id="editor" name="description" rows="10" cols="20" placeholder="Describe what this section is about"></textarea></label>
            <!-- <input class="submit" type="submit" value="Create section"> -->
            <button class="submit" onclick="create_section()">Create section</button>
            <?php
                if(isset($_GET['msg']) && strtolower($_GET['msg']) == "section_created_success") {
                    echo '<div class="msg-container"><i class="checkmark  fa-solid fa-check"></i><h3>The section has been created successfully</h3></div>';
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