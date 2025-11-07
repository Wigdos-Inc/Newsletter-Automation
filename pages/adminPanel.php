<?php
    session_start();

    // Check if user is logged in
    if(!isset($_COOKIE['userID']) || !isset($_COOKIE['user_data'])){
        header('location: login_view.php');
        exit();
    }

    // Decode user_data from JSON
    $result = json_decode($_COOKIE['user_data'], true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../bootstrap-5.3.6-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styling/main.css">
</head>
<body class="light-mod">
<div class="container m-5">
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
    <h2 class="mb-5 fw-bold text-center">Admin Panel</h2>
    <div class="d-flex">
        <div class="p-3 list-unstyled" style="background-color: var(--text-primary)">
            <img style="-webkit-user-drag: none;" class="rounded-circle mb-2" src="https://upload.wikimedia.org/wikipedia/commons/a/a3/Image-not-found.png" alt="">
            <a id="btn_tab_create" class="text-decoration-none"><h3 style="color: var(--text-secondary)">NewsLetters</h3></a>
            <li class="border-top my-3"></li>
            <a id="btn_tab_viewLetters" class="text-decoration-none"><h3 style="color: var(--text-secondary)">Moderation</h3></a>
            <li class="border-top my-3"></li>
            <a id="btn_tab_viewCategories" class="text-decoration-none"><h3 style="color: var(--text-secondary)">Categories</h3></a>
        </div>
        <div id="profile_tabs" class="p-3 w-75" style="background-color: var(--bg-secondary)">
            <div id="tab_create" class="list-unstyled" style="display: block;">
                <h2>Newsletters</h2>
                <p style="color: var(--text-secondary)">Here you can view, edit and delete NewsLetters.</p>
                <li class="border-top my-3"></li>
                <div class="mb-3">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createLetter">Generate</button>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delconfirm">Delete selected</button>

                    <select class="form-select mt-2 mb-2" id="newsletterSelector">
                        <option value="" selected disabled>Choose a newsletter type...</option>
                        <option value="admin">Your Generated Newsletters</option>
                        <option value="global">Global Newsletters</option>
                        <option value="hidden">Hidden/Banned Newsletters</option>
                    </select>

                    <label for="searchNewsLetter" class="form-label">Search NewsLetters</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="searchNewsLetter" placeholder="Enter NewsLetters Name">
                        <button class="btn btn-primary" type="button"><li class="fas fa-search"></li></button>
                    </div>
                </div>
                <li class="border-top my-3"></li>
                <div id="newsletters_view" class="overflow-y-auto" style="height: 50vh">
                    <div class="card-body overflow-hidden">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Select</th>
                                    <th>Name</th>
                                    <th>Owner</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for($i = 0; $i < 55; $i++) { ?>
                                <tr>
                                    <td><input type="checkbox"></td>
                                    <td>Weekly Tech Digest</td>
                                    <td>john_doe</td>
                                    <td>
                                        <span class="badge bg-success">Visible</span>
                                        <span class="badge bg-success">Published</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning mb-2">Hide</button>
                                        <button class="btn btn-sm btn-danger">Unpublish</button>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="tab_viewLetters" class="list-unstyled" style="display: none;">
                <h2>Moderation</h2>
                <div class="tab-content" id="modTabsContent">
                    <!-- Tabs -->
                    <ul class="nav nav-tabs mb-3" id="modTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" style="color: var(--text-secondary)">User Accounts</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tags-tab" data-bs-toggle="tab" data-bs-target="#tags" type="button" style="color: var(--text-secondary)">Tags</button>
                        </li>
                    </ul>

                    <!-- Users Tab -->
                    <div class="tab-pane fade show active" id="users" role="tabpanel">
                        <div class="card">
                        <div class="card-header bg-secondary text-white">Manage User Accounts</div>
                        <div class="card-body">
                            <label for="searchUsername" class="form-label">Search users</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchUsername" placeholder="Enter Username">
                                <button class="btn btn-primary" type="button"><li class="fas fa-search"></li></button>
                            </div>
                            <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>@tech_guru</td>
                                    <td>techguru@example.com</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning mb-2" data-bs-toggle="modal" data-bs-target="#lockModal">Lock</button>
                                        <button class="btn btn-sm btn-danger">Ban</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>@bad_actor</td>
                                    <td>badactor@example.com</td>
                                    <td><span class="badge bg-danger">Banned</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary">Unban</button>
                                    </td>
                                </tr>
                            </tbody>
                            </table>
                        </div>
                        </div>
                    </div>

                    <!-- Tags Tab -->
                    <div class="tab-pane fade" id="tags" role="tabpanel">
                        <div class="card">
                        <div class="card-header bg-secondary text-white">Manage Tags</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="newTag" class="form-label">Ban New Tag</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="newTag" placeholder="Enter tag name (e.g. #violence)">
                                    <button class="btn btn-danger" type="button">Ban Tag</button>
                                </div>
                            </div>
                                <h6>Banned Tags:</h6>
                                <span class="badge bg-danger me-1">#profanity</span>
                                <span class="badge bg-danger me-1">#unsafe</span>
                                <span class="badge bg-danger me-1">#hate</span>
                            </div>
                        </div>
                    </div>  
                </div>
            </div>

            <div id="tab_viewCategories" class="list-unstyled" style="display: none;">
                <h2>Categories</h2>
                <p style="color: var(--text-secondary)">Here you can view and delete Categories.</p>
                <li class="border-top my-3"></li>
                <div class="mb-3">
                    <button class="btn btn-danger mb-2" data-bs-toggle="modal" data-bs-target="#delCatagoryconfirm">Delete selected</button><br>

                    <label for="searchCategories" class="form-label">Search Categories</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="searchCategories" placeholder="Enter Categories Name">
                        <button class="btn btn-primary" type="button"><li class="fas fa-search"></li></button>
                    </div>
                </div>
                <li class="border-top my-3"></li>
                <div id="categories_view" class="overflow-y-auto" style="height: 50vh">
                    <div class="card-body overflow-hidden">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Select</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for($i = 0; $i < 55; $i++) { ?>
                                <tr>
                                    <td><input type="checkbox"></td>
                                    <td>Category</td>
                                    <td>
                                        <span class="badge bg-success">Active</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning">Hide</button>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Delete NewsLetters Modal -->
    <div class="modal fade" id="delconfirm" tabindex="-1" aria-labelledby="lockModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content text-center p-4 shadow rounded" style="background-color: var(--bg-secondary)">
                <h2 style="color: var(--text-primary)">You are about to delete all selected NewsLetters.</h2>
                <p style="color: var(--text-secondary)">Do you want to proceed with this action?</p>
                <div class="modal-body">
                    <button class="btn btn-success"><a class="nav-link fw-bold text-light">Yes</a></button>
                    <button id="delconfirmclose" class="btn btn-danger"><a class="nav-link fw-bold text-light" data-bs-dismiss="modal" aria-label="Close">No</a></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Categories Modal -->
    <div class="modal fade" id="delCatagoryconfirm" tabindex="-1" aria-labelledby="lockModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content text-center p-4 shadow rounded" style="background-color: var(--bg-secondary)">
                <h2 style="color: var(--text-primary)">You are about to delete all selected Categories.</h2>
                <p style="color: var(--text-secondary)">Do you want to proceed with this action?</p>
                <div class="modal-body">
                    <button class="btn btn-success"><a class="nav-link fw-bold text-light">Yes</a></button>
                    <button id="delconfirmclose" class="btn btn-danger"><a class="nav-link fw-bold text-light" data-bs-dismiss="modal" aria-label="Close">No</a></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create NewsLetter Modal -->
    <div class="modal fade" id="createLetter" tabindex="-1" aria-labelledby="lockModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content text-center p-4 shadow rounded" style="background-color: var(--bg-secondary)">
                <div class="modal-header">
                    <h2 class="me-4" style="color: var(--text-primary)">Generate a new NewsLetter</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <iframe src="newsletter_view.php" style="width: 100%; height: 50vh"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Lock User Modal -->
    <div class="modal fade" id="lockModal" tabindex="-1" aria-labelledby="lockModalLabel" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content" style="background-color: var(--bg-secondary)">
            <div class="modal-header">
                <h5 class="modal-title" id="lockModalLabel" style="color: var(--text-primary)">Lock User Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label for="lockDays" class="form-label" style="color: var(--text-secondary)">Number of days to lock:</label>
                        <input type="number" class="form-control" id="lockDays" min="1" max="365" placeholder="e.g. 7">
                    </div>
                    <button type="button" class="btn btn-primary w-100">Confirm Lock</button>
                </form>
            </div>
        </div>
        </div>
    </div>

<script src="../bootstrap-5.3.6-dist/js/bootstrap.bundle.min.js"></script>
<script src="../scripts/adminpanel.js"></script>
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
