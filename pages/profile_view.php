<?php
    session_start();
    
    // Check if user is logged in
    if(!isset($_COOKIES['userID']) || !isset($_SESSION['user_data'])){
        header('location: login_view.php');
        exit();
    }
    
    $result = $_SESSION['user_data'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="../bootstrap-5.3.6-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../styling/main.css">
</head>
<body>
    <div class="container m-5">
        <h2 class="mb-5 fw-bold text-dark text-center">Profile</h2>
        
        <?php if(isset($_GET['err'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> <?= htmlspecialchars($_GET['err']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success:</strong> <?= htmlspecialchars($_GET['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <div class="d-flex">
            <div class="bg-cyan-dark p-3 list-unstyled">
                <img style="-webkit-user-drag: none;" class="rounded-circle mb-2" src="https://upload.wikimedia.org/wikipedia/commons/a/a3/Image-not-found.png" alt="">
                <a id="btn_tab_about" class="text-decoration-none"><h3 class="text-light">About</h3></a>
                <li class="border-top my-3"></li>
                <a id="btn_tab_settings" class="text-decoration-none"><h3 class="text-light">Settings</h3></a>
                <li class="border-top my-3"></li>
                <a class="text-decoration-none" href=""><h3 class="text-light">Favorites</h3></a>
                <li class="border-top my-3"></li>
                <a class="text-decoration-none" href="../scripts/db/account/logout.php"><h3 class="text-light">Sign out</h3></a>
            </div>
            <div id="profile_tabs" class="p-3 w-75 bg-light">
                <div id="tab_about" class="list-unstyled" style="display: block;">
                    <h2 class="text-dark">About</h2>
                    <li class="border-top my-3"></li>
                    <p class="fw-bold">Username: <?=$result['username']?></p>
                    <p class="fw-bold">E-mail: <?=$result['email']?></p>
                    <p class="fw-bold">Date of birth: <?=$result['dob']?></p>
                </div>

                <div id="tab_settings" class="list-unstyled" style="display: none;">
                    <h2 class="text-dark">Settings</h2>
                    <li class="border-top my-3"></li>
                    <p class="fw-bold">Update Account information:</p>
                    <form class="w-50 mb-5" action="../scripts/db/account/update_account.php" method="post">
                        <div class="form-floating mb-2 mt-2">
                            <input class="form-control" type="text" name="username" id="username" placeholder="New Username ..." value="<?=$result['username']?>">
                            <label for="username">New Username ...</label>
                        </div>
                        <div class="form-floating mb-2">
                            <input class="form-control" type="email" name="email" id="email" placeholder="New E-mail ..." value="<?=$result['email']?>">
                            <label for="email">New E-mail ...</label>
                        </div>
                        <div class="form-floating mb-2">
                            <input class="form-control" type="date" name="dob" id="dob" placeholder="New Date of birth ..." value="<?=$result['dob']?>">
                            <label for="dob">New Date of birth ...</label>
                        </div>
                        <div class="form-floating mb-2 mt-5">
                            <input class="form-control" type="password" name="oldpassword" id="oldpassword" placeholder="Old Password ...">
                            <label for="oldpassword">Old Password ...</label>
                        </div>
                        <div class="form-floating mb-2">
                            <input class="form-control" type="password" name="password" id="password" placeholder="New Password ...">
                            <label for="password">New Password ...</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input class="form-control" type="password" name="passwordCheck" id="passwordCheck" placeholder="New Password(Again) ...">
                            <label for="passwordCheck">New Password(Again) ...</label>
                        </div>
                        <input class="btn btn-success mb-3 mt-5 fw-bold" type="submit" value="Update Information">
                    </form>

                    <h3 class="text-danger">Danger Zone</h3>
                    <li class="border-top my-3"></li>
                    <button class="btn btn-danger" id="delaccBtn">Delete Account</button>
                </div>
            </div>
        </div>
    </div>

    <div id="delconfirm" style="display: none">
            <div class="container bg-body-secondary min-vh-100 min-vw-100 position-fixed align-self-center opacity-25"></div>
            <div class="modal position-fixed d-flex align-items-center">
                <div class="modal-dialog shadow rounded p-0">
                    <div class="modal-content text-center p-4">
                        <p>You are about to delete your account: '<?=$result['username']?>'.</p>
                        <p>Do you want to proceed with this action?</p>
                        <div>
                            <button class="btn btn-success"><a class="nav-link fw-bold text-light" href="../scripts/db/account/delete_ac.php">Yes</a></button>
                            <button class="btn btn-danger"><a class="nav-link fw-bold text-light" href="profile_view.php">No</a></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../bootstrap-5.3.6-dist/js/bootstrap.bundle.min.js"></script>
    <script src="../scripts/profilePage.js"></script>
    <script>
        // Listen for theme changes from parent window
        window.addEventListener('message', function(event) {
            if (event.data && event.data.type === 'themeChange') {
                const theme = event.data.theme;
                if (theme === 'light') {
                    document.body.classList.add('light-mode');
                } else {
                    document.body.classList.remove('light-mode');
                }
            }
        });

        // Apply saved theme on load
        const savedTheme = localStorage.getItem('theme') || 'dark';
        if (savedTheme === 'light') {
            document.body.classList.add('light-mode');
        }
    </script>
</body>
</html>
