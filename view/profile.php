<script type="text/javascript">
   
   async function editPassword() {
        const currentPass = document.querySelector('.current-password').value
        const newPass = document.querySelector('.new-password').value
        const confirmPass = document.querySelector('.confirm-new-password').value
        const currentPassWp = document.querySelector('.current-password')
        const newPassWp = document.querySelector('.new-password')
        const confirmPassWp = document.querySelector('.confirm-new-password')


        // Comprobar que ningún campo está vacio
        if(!currentPass || !newPass || !confirmPass) {
            if(!currentPass) {
                currentPassWp.style.border = '1px solid red'
            }
            if(!newPass) {
                newPassWp.style.border = '1px solid red'
            }
            if(!confirmPass) {
                confirmPassWp.style.border = '1px solid red'
            }
            return false
        }
        
        // Comprobar que se ha escrito bien la nueva contraseña
        if(newPass !== confirmPass) {
            newPassWp.style.border = '1px solid red'
            confirmPassWp.style.border = '1px solid red'
            document.querySelector('.current-password').value = ""
            document.querySelector('.new-password').value = ""
            document.querySelector('.confirm-new-password').value = ""
            return
        }
        
        // Llamar al servidor para editar la seccion
        try {
            const response = await fetch("index.php?action=edit_password", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    password: newPass,
                    currentpass: currentPass
                })
            })
            console.log( await response.text())
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
    window.onload = (event) => {
        document.getElementById('avatar').addEventListener('change', async function() {
            if (this.files && this.files[0]) {
                const formData = new FormData();
                formData.append('avatar', this.files[0]);
                const response = await fetch("index.php?action=avatar_upload", {
                    method: 'POST',
                    body: formData,
                });
                const jsonData = await response.json()
                if (jsonData.status === 1) { return }
                document.getElementById("avatarimg").src = "uploads/" + jsonData.data.filename
            }
        });
    }

</script>
<nav>
    <ul>
        <a href="index.php?view=home"><span><i class="fa-solid fa-house"></i></span></a>
        <span>
            <span>
                <a href="index.php?view=home">DuckersForum</a>
            </span>
            <span>
                <i class="fa-solid fa-angle-right"></i>
            </span>
            <span>
                <strong>My profile</strong>
            </span>
        </span>
    </ul>
</nav>

<div id="profile-wp">
    <article class="user-profile-wp">
        <div class="img-profile-wp">
            <label for="avatar"><img id="avatarimg" src="<?=$avatarSrc?>" alt="avatar" width="100" height="100"><i class="fa-solid fa-camera camera"></i></label>
            <input style="display:none" type="file" id="avatar" name="avatar" accept="image/png, image/jpeg" />
        </div>
        <div class="username-profile-wp">
            <p><?=$_SESSION['username']?></p>
            <form><input style="display:none" type="file" id="profile_avatar" name="profile_avatar"></form>
        </div>
    </article>
    <hr>
    <article class="interactions-wp">
        <a href="index.php?view=mythreads"><div class="threads-profile-wp">
            <p>My threads</p>
            <p><?=$userController->get_total_threads()?></p>
        </div></a>
       <a href="index.php?view=myposts"><div class="replies-profile-wp">
            <p>My replies</p>
            <p><?=$userController->get_total_posts()?></p>
        </div></a>
    </article>
    <hr>
    <article class="edit-password-wp">
        <p>Edit password</p>
        <div class="current-password-wp">
            <label><input class="current-password" type="password" placeholder="Enter your current password" require></label>
        </div>
        <div class="new-password-wp">
            <label><input class="new-password" type="password" placeholder="Enter your new password" require></label>
            <label><input class="confirm-new-password" type="password" placeholder="Confirm your new password" require></label>
        </div>
        <div class="pass-submit-wp">
            <button class="submit" onclick="editPassword()">Edit password</button>
        </div>
    </article>
</div>