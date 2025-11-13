<?php
    session_start();
    
    // Check if user is logged in
    if(!isset($_COOKIE['userID']) || !isset($_COOKIE['user_data'])){
        header('location: login_view.php');
        exit();
    }
    
    // Decode user_data from JSON
    $result = json_decode($_COOKIE['user_data'], true);

    $isIframe = isset($_GET['iframe']);
?>
<?php if (!$isIframe): ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="../bootstrap-5.3.6-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- <link rel="stylesheet" href="../styling/main.css"> -->
    <link rel="stylesheet" href="../styling/profile.css">
</head>
<body>
<?php endif; ?>        
        
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
        
        <div class="d-flex flex-column flex-md-row gap-3">
            <div class="admin-sidebar">
                <div class="text-center mb-4 pb-3" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <img style="-webkit-user-drag: none;" class="rounded-circle mb-2" width="80" src="https://upload.wikimedia.org/wikipedia/commons/a/a3/Image-not-found.png" alt="User Avatar">
                    <p class="mb-0 mt-2" style="color: var(--text-secondary); font-size: 0.9rem;">User Profile</p>
                </div>
                <nav class="admin-nav">
                    <a href="#" id="btn_tab_about" class="admin-nav-link active">
                        <i class="fas fa-user me-2"></i>
                        <span>About</span>
                    </a>
                    <a href="#" id="btn_tab_settings" class="admin-nav-link">
                        <i class="fas fa-cog me-2"></i>
                        <span>Settings</span>
                    </a>
                    <a class="admin-nav-link" href="">
                        <i class="fas fa-star me-2"></i>
                        <span>Favorites</span>
                    </a>
                    <a class="admin-nav-link" href="../scripts/db/account/logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>
                        <span>Log out</span>
                    </a>
                </nav>
            </div>
            <div id="profile_tabs" class="admin-content-wrapper">
                <div id="tab_about" class="list-unstyled admin-tab-content" style="display: block;">
                    <div class="admin-tab-header">
                        <h2>About</h2>
                        <p>Your account information</p>
                    </div>
                    <div class="mb-3" style="padding: 1.5rem;">
                        <p class="fw-bold" style="color: var(--text-primary);">Username: <?=$result['username']?></p>
                        <p class="fw-bold" style="color: var(--text-primary);">E-mail: <?=$result['email']?></p>
                        <p class="fw-bold" style="color: var(--text-primary);">Date of birth: <?=$result['dob']?></p>
                    </div>
                </div>

                <div id="tab_settings" class="list-unstyled admin-tab-content" style="display: none;">
                    <div class="admin-tab-header">
                        <h2>Settings</h2>
                        <p>Update your account information</p>
                    </div>
                    <div class="mb-3" style="padding: 1.5rem;">
                        <h4 class="fw-bold mb-4" style="color: var(--text-primary);">Update Account Information</h4>
                        <form class="mb-5" action="../scripts/db/account/update_account.php" method="post" style="max-width: 600px;">
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
                            <button class="btn btn-success mb-3 mt-5 fw-bold" type="submit">
                                <i class="fas fa-save me-2"></i>Update Information
                            </button>
                        </form>

                        <h3 class="text-danger mt-5">
                            <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
                        </h3>
                        <hr style="border-color: var(--accent-red, #e74c3c); opacity: 0.3; margin: 1rem 0 2rem 0;">
                        <button class="btn btn-danger" id="delaccBtn">
                            <i class="fas fa-trash me-2"></i>Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Account Confirmation Modal -->
    <div class="modal fade" id="delconfirm" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Account Deletion
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>You are about to delete your account: <strong>'<?=$result['username']?>'</strong>.</p>
                    <p>Do you want to proceed with this action?</p>
                </div>
                <div class="modal-footer">
                    <a href="../scripts/db/account/delete_ac.php" class="btn btn-danger">
                        <i class="fas fa-check me-2"></i>Yes, Delete
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="../bootstrap-5.3.6-dist/js/bootstrap.bundle.min.js"></script>
    <script src="../scripts/js/profilePage.js"></script>
    <script src="../scripts/js/theme.js"></script>
    <script>
        // Delete account button - show modal
        document.getElementById('delaccBtn')?.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('delconfirm'));
            modal.show();
        });
    </script>
<?php if (!$isIframe): ?>
</body>
</html>
<?php endif; ?>
