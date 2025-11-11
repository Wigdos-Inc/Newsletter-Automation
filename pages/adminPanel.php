<?php
    session_start();

    // Check if user is logged in
    if(!isset($_COOKIE['userID']) || !isset($_COOKIE['user_data'])){
        header('location: login_view.php');
        exit();
    }

    // Decode user_data from JSON
    $result = json_decode($_COOKIE['user_data'], true);
    
    // Check if user is admin or owner
    require_once '../scripts/db/data.php';
    $userID = isset($_COOKIE['userID']) ? intval($_COOKIE['userID']) : null;
    
    $isAdminOrOwner = false;
    if ($userID && isset($users[$userID])) {
        $userRole = $users[$userID]['role'];
        $isAdminOrOwner = ($userRole === 'admin' || $userRole === 'owner');
    }
    
    if (!$isAdminOrOwner) {
        header('location: ../index.php?err=Access denied - Admin privileges required');
        exit();
    }
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
    <link rel="stylesheet" href="../styling/profile.css">
    <style>
        /* Override for iframe embedding - remove margins and ensure proper layout */
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            overflow-x: hidden;
            background-color: var(--bg-primary, #1a1c20) !important;
        }
        
        /* Center content properly in iframe */
        .admin-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        /* Remove Bootstrap container margins in iframe */
        .container {
            margin: 0 auto !important;
            padding-left: 1rem;
            padding-right: 1rem;
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
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
    
    <h2 class="mb-4 fw-bold text-center" style="color: var(--text-primary);">Admin Panel</h2>
    
    <div class="d-flex flex-column flex-md-row gap-3">
        <div class="admin-sidebar">
            <div class="text-center mb-4 pb-3" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                <img style="-webkit-user-drag: none;" class="rounded-circle mb-2" width="80" src="https://upload.wikimedia.org/wikipedia/commons/a/a3/Image-not-found.png" alt="Admin Avatar">
                <p class="mb-0 mt-2" style="color: var(--text-secondary); font-size: 0.9rem;">Admin Dashboard</p>
            </div>
            <nav class="admin-nav">
                <a id="btn_tab_create" class="admin-nav-link active">
                    <i class="fas fa-newspaper me-2"></i>
                    <span>Newsletters</span>
                </a>
                <a id="btn_tab_generator" class="admin-nav-link">
                    <i class="fas fa-magic me-2"></i>
                    <span>Generator</span>
                </a>
                <a id="btn_tab_viewLetters" class="admin-nav-link">
                    <i class="fas fa-users me-2"></i>
                    <span>Users</span>
                </a>
                <a id="btn_tab_viewCategories" class="admin-nav-link">
                    <i class="fas fa-tags me-2"></i>
                    <span>Categories</span>
                </a>
            </nav>
        </div>
        <div id="profile_tabs" class="admin-content-wrapper">
            <div id="tab_create" class="list-unstyled admin-tab-content" style="display: block;">
                <div class="admin-tab-header">
                    <h2>Newsletters</h2>
                    <p style="color: var(--text-secondary)">Here you can view, edit and delete NewsLetters.</p>
                </div>
                <div class="mb-3">
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" id="searchNewsLetter" placeholder="Search newsletters...">
                        <button class="btn btn-primary" type="button" id="search-newsletter-btn"><i class="fas fa-search"></i></button>
                    </div>
                    <button class="btn btn-danger" id="delete-selected-newsletters">
                        <i class="fas fa-trash me-1"></i>Delete selected
                    </button>
                </div>
                <div id="newsletters_view" class="overflow-y-auto">
                    <div class="card-body overflow-hidden">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select-all-newsletters"></th>
                                    <th>Title</th>
                                    <th>Owner</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="newsletters-table-body">
                                <?php foreach($newsletters as $newsletter): 
                                    $owner = isset($users[$newsletter['user_ID']]) ? $users[$newsletter['user_ID']]['username'] : 'Unknown';
                                ?>
                                <tr data-newsletter-id="<?= $newsletter['newsletter_ID'] ?>">
                                    <td><input type="checkbox" class="newsletter-checkbox" value="<?= $newsletter['newsletter_ID'] ?>"></td>
                                    <td><?= htmlspecialchars(substr($newsletter['title'], 0, 50)) ?><?= strlen($newsletter['title']) > 50 ? '...' : '' ?></td>
                                    <td><?= htmlspecialchars($owner) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $newsletter['status'] === 'published' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($newsletter['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($newsletter['creation_date'])) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-newsletter-btn me-1" data-id="<?= $newsletter['newsletter_ID'] ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <?php if($newsletter['status'] === 'published'): ?>
                                            <button class="btn btn-sm btn-warning unpublish-btn" data-id="<?= $newsletter['newsletter_ID'] ?>">Unpublish</button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-success publish-btn" data-id="<?= $newsletter['newsletter_ID'] ?>">Publish</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="tab_generator" class="list-unstyled admin-tab-content" style="display: none;">
                <div class="admin-tab-header">
                    <h2><i class="fas fa-magic me-2"></i>AI Newsletter Generator</h2>
                    <p style="color: var(--text-secondary)">Generate and edit newsletters with AI assistance.</p>
                </div>
                
                <!-- Generate Mode -->
                <div id="generate-mode" style="padding: 1.5rem;">
                    <div class="card mb-4">
                        <div class="card-body">
                            <label class="form-label fw-bold" style="color: var(--text-primary); font-size: 1.1rem;">
                                <i class="fas fa-lightbulb me-2"></i>What should the newsletter be about?
                            </label>
                            <textarea id="admin-newsletter-query" class="form-control mb-3" rows="5" 
                                      placeholder="Example: Write a newsletter about the latest AI trends in 2025, including breakthroughs in machine learning and their impact on businesses."
                                      style="background-color: var(--card-bg); color: var(--text-primary); border-color: var(--border-color); font-size: 1rem;"></textarea>
                            <div class="d-flex gap-2">
                                <button id="admin-generate-btn" class="btn btn-primary btn-lg">
                                    <i class="fas fa-magic me-2"></i>Generate Newsletter
                                </button>
                                <button class="btn btn-outline-primary btn-lg" onclick="document.getElementById('admin-newsletter-query').value = 'Write a newsletter about recent technological advancements'">
                                    <i class="fas fa-shuffle me-1"></i>Example
                                </button>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Generation takes 1-2 minutes. Be specific about topics, tone, and target audience.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="admin-loading" style="display:none;" class="text-center my-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h4 class="mt-3" style="color: var(--text-primary)">Generating Your Newsletter</h4>
                    <p class="mt-2" style="color: var(--text-secondary)">
                        <i class="fas fa-clock me-1"></i>This may take 1-2 minutes. Please wait...
                    </p>
                    <div class="progress mt-3 mx-auto" style="max-width: 400px; height: 8px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 100%"></div>
                    </div>
                </div>

                <!-- Edit Mode -->
                <div id="admin-edit-mode" style="display:none;">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label fw-bold" style="color: var(--text-primary)">
                                        <i class="fas fa-heading me-1"></i>Title
                                    </label>
                                    <input type="text" id="admin-newsletter-title" class="form-control" 
                                           placeholder="Enter newsletter title..."
                                           style="background-color: var(--card-bg); color: var(--text-primary); border-color: var(--border-color);">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold" style="color: var(--text-primary)">
                                        <i class="fas fa-tags me-1"></i>Categories
                                    </label>
                                    <select id="admin-category-select" class="form-select" multiple size="3"
                                            style="background-color: var(--card-bg); color: var(--text-primary); border-color: var(--border-color);">
                                        <option value="" disabled>Loading categories...</option>
                                    </select>
                                    <button id="admin-add-category-btn" class="btn btn-sm btn-outline-primary mt-2 w-100">
                                        <i class="fas fa-plus me-1"></i>New Category
                                    </button>
                                </div>
                            </div>
                            
                            <label class="form-label fw-bold" style="color: var(--text-primary)">
                                <i class="fas fa-file-alt me-1"></i>Content
                            </label>
                            <div id="admin-newsletter-editor" class="form-control mb-3" 
                                 style="min-height: 450px; max-height: 550px; overflow-y: auto; background-color: var(--card-bg); color: var(--text-primary); border-color: var(--border-color); font-size: 1rem; line-height: 1.6;" 
                                 contenteditable="true">
                            </div>
                            
                            <div class="d-flex gap-2 flex-wrap">
                                <button id="admin-save-btn" class="btn btn-success">
                                    <i class="fas fa-save me-1"></i>Save Draft
                                </button>
                                <button id="admin-ai-edit-btn" class="btn btn-info">
                                    <i class="fas fa-magic me-1"></i>AI Edit
                                </button>
                                <button id="admin-publish-btn" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i>Publish
                                </button>
                                <button id="admin-regenerate-btn" class="btn btn-secondary">
                                    <i class="fas fa-redo me-1"></i>Start Over
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error/Success Display -->
                <div id="admin-error" style="display:none;" class="alert alert-danger"></div>
                <div id="admin-success" style="display:none;" class="alert alert-success"></div>
            </div>

            <div id="tab_viewLetters" class="list-unstyled admin-tab-content" style="display: none;">
                <div class="admin-tab-header">
                    <h2>Users</h2>
                    <p style="color: var(--text-secondary)">Here you can view all user accounts.</p>
                </div>
                <div class="mb-3" style="padding: 0 1.5rem;">
                    <div class="input-group">
                        <input type="text" class="form-control" id="searchUsername" placeholder="Search users...">
                        <button class="btn btn-primary" type="button" id="search-user-btn"><i class="fas fa-search"></i></button>
                    </div>
                </div>
                <div class="overflow-y-auto">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Newsletters</th>
                                <th>Categories</th>
                            </tr>
                        </thead>
                        <tbody id="users-table-body">
                            <?php foreach($users as $user): ?>
                            <tr data-user-id="<?= $user['user_ID'] ?>">
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $user['role'] === 'owner' ? 'danger' : ($user['role'] === 'admin' ? 'warning' : 'primary') ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($user['creation_date'])) ?></td>
                                <td><?= count($user['c_newsletters']) ?></td>
                                <td><?= count($user['c_categories']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="tab_viewCategories" class="list-unstyled admin-tab-content" style="display: none;">
                <div class="admin-tab-header">
                    <h2>Categories</h2>
                    <p style="color: var(--text-secondary)">Here you can view and delete Categories.</p>
                </div>
                <div class="mb-3" style="padding: 0 1.5rem;">
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" id="searchCategories" placeholder="Search categories...">
                        <button class="btn btn-primary" type="button" id="search-category-btn"><i class="fas fa-search"></i></button>
                    </div>
                    <button class="btn btn-success me-2" id="create-category-tab-btn">
                        <i class="fas fa-plus me-1"></i>Create New Category
                    </button>
                    <button class="btn btn-danger" id="delete-selected-categories">
                        <i class="fas fa-trash me-1"></i>Delete selected
                    </button>
                </div>
                <div id="categories_view" class="overflow-y-auto">
                    <div class="card-body overflow-hidden">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select-all-categories"></th>
                                    <th>Name</th>
                                    <th>Newsletters</th>
                                    <th>Contributors</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="categories-table-body">
                                <?php foreach($categories as $category): ?>
                                <tr data-category-id="<?= $category['category_ID'] ?>">
                                    <td><input type="checkbox" class="category-checkbox" value="<?= $category['category_ID'] ?>"></td>
                                    <td><?= htmlspecialchars($category['name']) ?></td>
                                    <td><?= count($category['newsletters']) ?></td>
                                    <td><?= count($category['contributors']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-danger delete-category-btn" data-id="<?= $category['category_ID'] ?>">Delete</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

    <!-- Delete NewsLetters Confirmation Modal -->
    <div class="modal fade" id="delNewsletterConfirm" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="background-color: var(--bg-secondary)">
                <div class="modal-header">
                    <h5 class="modal-title" style="color: var(--text-primary)">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="color: var(--text-secondary)">
                    Are you sure you want to delete the selected newsletters?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirm-delete-newsletters">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Categories Confirmation Modal -->
    <div class="modal fade" id="delCategoryConfirm" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="background-color: var(--bg-secondary)">
                <div class="modal-header">
                    <h5 class="modal-title" style="color: var(--text-primary)">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="color: var(--text-secondary)">
                    Are you sure you want to delete the selected categories?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirm-delete-categories">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Modal for Newsletter Generator -->
    <div class="modal fade" id="adminCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="background-color: var(--bg-secondary)">
                <div class="modal-header">
                    <h5 class="modal-title" style="color: var(--text-primary)">Create New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="admin-new-category-name" class="form-control" 
                           placeholder="Category name" style="background-color: var(--bg-primary); color: var(--text-primary); border-color: var(--border-color);">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="admin-save-category-btn" class="btn btn-primary">Create</button>
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

    // ========================================
    // NEWSLETTER GENERATOR IN ADMIN PANEL
    // ========================================
    
    let currentNewsletterID = null;
    let currentNewsletterContent = null;

    // ========================================
    // Helper Functions (accessible globally)
    // ========================================
    
    // Load Categories Function
    async function loadCategories() {
        try {
            const response = await fetch('../scripts/php/categories.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'fetch' })
            });

            const data = await response.json();
            
            if (data.success) {
                const select = document.getElementById('admin-category-select');
                if (select) {
                    select.innerHTML = '';
                    
                    data.categories.forEach(cat => {
                        const option = document.createElement('option');
                        option.value = cat.category_ID;
                        option.textContent = cat.name;
                        select.appendChild(option);
                    });
                }
            }
        } catch (err) {
            console.error('Load categories error:', err);
        }
    }

    // Save Newsletter Function
    async function saveNewsletter(status) {
        const title = document.getElementById('admin-newsletter-title').value.trim();
        const content = document.getElementById('admin-newsletter-editor').innerHTML;
        const categorySelect = document.getElementById('admin-category-select');
        const selectedCategories = Array.from(categorySelect.selectedOptions).map(opt => opt.value);

        if (!title) {
            showError('Please enter a title');
            return;
        }

        const loading = document.getElementById('admin-loading');
        const editMode = document.getElementById('admin-edit-mode');
        
        editMode.style.display = 'none';
        loading.style.display = 'block';
        loading.querySelector('p').textContent = status === 'published' ? 'Publishing newsletter...' : 'Saving draft...';

        try {
            const response = await fetch('../scripts/php/newsletters.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: currentNewsletterID ? 'update' : 'save',
                    newsletter_ID: currentNewsletterID,
                    title: title,
                    content: content,
                    status: status
                })
            });

            const data = await response.json();
            
            if (data.success) {
                currentNewsletterID = data.newsletter_ID;
                
                // Save categories if any selected
                if (selectedCategories.length > 0) {
                    await fetch('../scripts/php/categories.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'save_newsletter_categories',
                            newsletter_ID: currentNewsletterID,
                            category_ids: selectedCategories
                        })
                    });
                }
                
                showSuccess(status === 'published' ? 'Newsletter published successfully!' : 'Newsletter saved as draft!');
                
                // Reload page after short delay to show updated newsletter list
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
                
            } else {
                throw new Error(data.error || 'Failed to save');
            }
        } catch (err) {
            console.error('Save error:', err);
            showError('Failed to save newsletter. Please try again.');
            loading.style.display = 'none';
            editMode.style.display = 'block';
        }
    }

    // Show Error Function
    function showError(message) {
        const errorDiv = document.getElementById('admin-error');
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 5000);
        }
    }

    // Show Success Function
    function showSuccess(message) {
        const successDiv = document.getElementById('admin-success');
        if (successDiv) {
            successDiv.textContent = message;
            successDiv.style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
            setTimeout(() => {
                successDiv.style.display = 'none';
            }, 5000);
        }
    }

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        
        // Load categories on page load
        loadCategories();

        // Add Generator tab button listener
        document.getElementById('btn_tab_generator')?.addEventListener('click', () => {
            swapTab('tab_generator');
        });

    // Generate Newsletter Button
    document.getElementById('admin-generate-btn').addEventListener('click', async function() {
        const query = document.getElementById('admin-newsletter-query').value.trim();
        const loading = document.getElementById('admin-loading');
        const generateMode = document.getElementById('generate-mode');
        const editMode = document.getElementById('admin-edit-mode');
        const error = document.getElementById('admin-error');
        const generateBtn = this;

        if (!query) {
            showError('Please enter a topic for the newsletter');
            return;
        }

        // Show loading
        generateMode.style.display = 'none';
        loading.style.display = 'block';
        error.style.display = 'none';
        generateBtn.disabled = true;
        generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Generating...';

        try {
            const response = await fetch('../scripts/php/newsletters.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    query: query,
                    action: 'generate'
                })
            });

            const data = await response.json();
            
            if (data.output) {
                currentNewsletterContent = data.output;
                currentNewsletterID = null; // Reset for new newsletter
                
                // Extract title from content (look for first h1 or h2)
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data.output;
                const heading = tempDiv.querySelector('h1, h2');
                const title = heading ? heading.textContent : 'Untitled Newsletter';
                
                // Show edit mode
                document.getElementById('admin-newsletter-title').value = title;
                document.getElementById('admin-newsletter-editor').innerHTML = data.output;
                
                loading.style.display = 'none';
                editMode.style.display = 'block';
                generateBtn.disabled = false;
                generateBtn.innerHTML = '<i class="fas fa-magic me-1"></i>Generate Newsletter';
                
            } else {
                throw new Error('No content generated');
            }
        } catch (err) {
            console.error('Generation error:', err);
            loading.style.display = 'none';
            generateMode.style.display = 'block';
            generateBtn.disabled = false;
            generateBtn.innerHTML = '<i class="fas fa-magic me-1"></i>Generate Newsletter';
            showError('Failed to generate newsletter. Please try again.');
        }
    });

    // Save Draft Button
    document.getElementById('admin-save-btn').addEventListener('click', async function() {
        await saveNewsletter('draft');
    });

    // Publish Button
    document.getElementById('admin-publish-btn').addEventListener('click', async function() {
        await saveNewsletter('published');
    });

    // AI Edit Button
    document.getElementById('admin-ai-edit-btn').addEventListener('click', async function() {
        const instructions = prompt('How would you like to edit the newsletter?\n\nExamples:\n- Make it more professional\n- Add more details about AI\n- Shorten it to 500 words\n- Make it more casual');
        
        if (!instructions) return;

        const content = document.getElementById('admin-newsletter-editor').innerHTML;
        const loading = document.getElementById('admin-loading');
        const editMode = document.getElementById('admin-edit-mode');

        editMode.style.display = 'none';
        loading.style.display = 'block';
        loading.querySelector('p').textContent = 'AI is editing your newsletter...';

        try {
            const response = await fetch('../scripts/php/newsletters.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'ai_edit',
                    content: content,
                    instructions: instructions
                })
            });

            const data = await response.json();
            
            if (data.output) {
                document.getElementById('admin-newsletter-editor').innerHTML = data.output;
                showSuccess('Newsletter edited successfully!');
            } else {
                throw new Error('No edited content received');
            }
        } catch (err) {
            console.error('AI Edit error:', err);
            showError('Failed to edit newsletter. Please try again.');
        } finally {
            loading.style.display = 'none';
            editMode.style.display = 'block';
            loading.querySelector('p').textContent = 'Generating newsletter... (may take 1-2 minutes)';
        }
    });

    // Start Over Button
    document.getElementById('admin-regenerate-btn').addEventListener('click', function() {
        if (confirm('Are you sure you want to start over? Your current work will be lost.')) {
            document.getElementById('generate-mode').style.display = 'block';
            document.getElementById('admin-edit-mode').style.display = 'none';
            document.getElementById('admin-loading').style.display = 'none';
            document.getElementById('admin-error').style.display = 'none';
            document.getElementById('admin-success').style.display = 'none';
            document.getElementById('admin-newsletter-query').value = '';
            document.getElementById('admin-newsletter-title').value = '';
            document.getElementById('admin-newsletter-editor').innerHTML = '';
            currentNewsletterID = null;
            currentNewsletterContent = null;
        }
    });

    // Add Category Button (in Generator tab)
    document.getElementById('admin-add-category-btn')?.addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('adminCategoryModal'));
        modal.show();
    });

    // Add Category Button (in Categories tab)
    document.getElementById('create-category-tab-btn')?.addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('adminCategoryModal'));
        modal.show();
    });

    // Save Category Button (in modal)
    document.getElementById('admin-save-category-btn')?.addEventListener('click', async function() {
        const categoryName = document.getElementById('admin-new-category-name').value.trim();
        if (!categoryName) {
            showError('Please enter a category name');
            return;
        }

        try {
            const response = await fetch('../scripts/php/categories.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'create',
                    name: categoryName
                })
            });

            const data = await response.json();
            
            if (data.success) {
                showSuccess('Category created successfully!');
                loadCategories();
                
                // Close modal and clear input
                const modalElement = document.getElementById('adminCategoryModal');
                const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                modal.hide();
                document.getElementById('admin-new-category-name').value = '';
                
                // Reload page to update categories table
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showError(data.error || 'Failed to create category');
            }
        } catch (err) {
            console.error('Create category error:', err);
            showError('Failed to create category');
        }
    });

    // ========================================
    // ADMIN PANEL ACTIONS
    // ========================================

    // Select all checkboxes
    document.getElementById('select-all-newsletters')?.addEventListener('change', function() {
        document.querySelectorAll('.newsletter-checkbox').forEach(cb => cb.checked = this.checked);
    });

    document.getElementById('select-all-categories')?.addEventListener('change', function() {
        document.querySelectorAll('.category-checkbox').forEach(cb => cb.checked = this.checked);
    });

    // Newsletter search
    document.getElementById('search-newsletter-btn')?.addEventListener('click', function() {
        const searchTerm = document.getElementById('searchNewsLetter').value.toLowerCase();
        document.querySelectorAll('#newsletters-table-body tr').forEach(row => {
            const title = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const owner = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            row.style.display = (title.includes(searchTerm) || owner.includes(searchTerm)) ? '' : 'none';
        });
    });

    // User search
    document.getElementById('search-user-btn')?.addEventListener('click', function() {
        const searchTerm = document.getElementById('searchUsername').value.toLowerCase();
        document.querySelectorAll('#users-table-body tr').forEach(row => {
            const username = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
            row.style.display = username.includes(searchTerm) ? '' : 'none';
        });
    });

    // Category search
    document.getElementById('search-category-btn')?.addEventListener('click', function() {
        const searchTerm = document.getElementById('searchCategories').value.toLowerCase();
        document.querySelectorAll('#categories-table-body tr').forEach(row => {
            const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            row.style.display = name.includes(searchTerm) ? '' : 'none';
        });
    });

    // Delete selected newsletters
    document.getElementById('delete-selected-newsletters')?.addEventListener('click', function() {
        const selected = Array.from(document.querySelectorAll('.newsletter-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Please select at least one newsletter');
            return;
        }
        const modal = new bootstrap.Modal(document.getElementById('delNewsletterConfirm'));
        modal.show();
    });

    // Confirm delete newsletters
    document.getElementById('confirm-delete-newsletters')?.addEventListener('click', async function() {
        const selected = Array.from(document.querySelectorAll('.newsletter-checkbox:checked')).map(cb => cb.value);
        
        try {
            const response = await fetch('../scripts/php/admin_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'delete_newsletters',
                    newsletter_ids: selected
                })
            });

            const data = await response.json();
            
            if (data.success) {
                window.location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        } catch (err) {
            console.error('Delete error:', err);
            alert('Failed to delete newsletters');
        }
    });

    // Publish/Unpublish buttons
    document.querySelectorAll('.publish-btn, .unpublish-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const newsletterID = this.dataset.id;
            const action = this.classList.contains('publish-btn') ? 'publish' : 'unpublish';
            
            try {
                const response = await fetch('../scripts/php/admin_actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'toggle_newsletter_status',
                        newsletter_id: newsletterID
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (err) {
                console.error('Status change error:', err);
                alert('Failed to change newsletter status');
            }
        });
    });

    // Edit newsletter buttons
    document.querySelectorAll('.edit-newsletter-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const newsletterID = this.dataset.id;
            
            try {
                // Fetch newsletter data
                const response = await fetch('../scripts/php/newsletters.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'fetch_single',
                        newsletter_ID: newsletterID
                    })
                });

                const data = await response.json();
                
                if (data.success && data.newsletter) {
                    // Switch to generator tab
                    swapTab('tab_generator');
                    
                    // Load newsletter data into editor
                    currentNewsletterID = newsletterID;
                    document.getElementById('admin-newsletter-title').value = data.newsletter.title;
                    document.getElementById('admin-newsletter-editor').innerHTML = data.newsletter.content;
                    
                    // Show edit mode
                    document.getElementById('generate-mode').style.display = 'none';
                    document.getElementById('admin-edit-mode').style.display = 'block';
                    
                    // Load and select categories
                    await loadCategories();
                    if (data.categories && data.categories.length > 0) {
                        const select = document.getElementById('admin-category-select');
                        Array.from(select.options).forEach(option => {
                            if (data.categories.includes(parseInt(option.value))) {
                                option.selected = true;
                            }
                        });
                    }
                } else {
                    alert('Error: ' + (data.error || 'Failed to load newsletter'));
                }
            } catch (err) {
                console.error('Edit error:', err);
                alert('Failed to load newsletter for editing');
            }
        });
    });

    // Delete selected categories
    document.getElementById('delete-selected-categories')?.addEventListener('click', function() {
        const selected = Array.from(document.querySelectorAll('.category-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Please select at least one category');
            return;
        }
        const modal = new bootstrap.Modal(document.getElementById('delCategoryConfirm'));
        modal.show();
    });

    // Confirm delete categories
    document.getElementById('confirm-delete-categories')?.addEventListener('click', async function() {
        const selected = Array.from(document.querySelectorAll('.category-checkbox:checked')).map(cb => cb.value);
        
        try {
            const response = await fetch('../scripts/php/admin_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'delete_categories',
                    category_ids: selected
                })
            });

            const data = await response.json();
            
            if (data.success) {
                window.location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        } catch (err) {
            console.error('Delete error:', err);
            alert('Failed to delete categories');
        }
    });

    // Delete single category
    document.querySelectorAll('.delete-category-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            if (!confirm('Are you sure you want to delete this category?')) return;
            
            const categoryID = this.dataset.id;
            
            try {
                const response = await fetch('../scripts/php/admin_actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'delete_categories',
                        category_ids: [categoryID]
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (err) {
                console.error('Delete error:', err);
                alert('Failed to delete category');
            }
        });
    });

    }); // End DOMContentLoaded
</script>
</body>
</html>
