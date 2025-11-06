<?php
session_start();
require_once '../scripts/db/config.php';

$userID = isset($_COOKIE['userID']) ? intval($_COOKIE['userID']) : null;

// Check if we're editing an existing newsletter
$editMode = false;
$newsletter = null;
$newsletterID = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($newsletterID && $userID) {
    $stmt = $pdo->prepare("SELECT * FROM tab_newsletters WHERE newsletter_ID = ? AND user_ID = ?");
    $stmt->execute([$newsletterID, $userID]);
    $newsletter = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($newsletter) {
        $editMode = true;
    }
}

// Fetch user's recent newsletters
$recentNewsletters = [];
if ($userID) {
    try {
        $stmt = $pdo->prepare("SELECT newsletter_ID, title, status, creation_date FROM tab_newsletters WHERE user_ID = ? ORDER BY creation_date DESC LIMIT 5");
        $stmt->execute([$userID]);
        $recentNewsletters = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Silent fail
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <base href="../">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="bootstrap-5.3.6-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="styling/main.css">
  <title><?php echo $editMode ? 'Edit Newsletter' : 'Newsletter Generator'; ?></title>
  <style>
    .recent-newsletters {
      max-height: 300px;
      overflow-y: auto;
    }
    .newsletter-item {
      padding: 10px;
      border: 1px solid #dee2e6;
      border-radius: 5px;
      margin-bottom: 10px;
      transition: background-color 0.2s;
    }
    .newsletter-item:hover {
      background-color: #f8f9fa;
    }
    .newsletter-item.active {
      background-color: #e7f3ff;
      border-color: #0d6efd;
    }
    #category-select, #generated-category-select {
      min-height: 120px;
    }
    #category-select option, #generated-category-select option {
      padding: 8px;
      cursor: pointer;
    }
    #category-select option:hover, #generated-category-select option:hover {
      background-color: #f0f0f0;
    }
  </style>
</head>
<body>
  <div class="container py-5">
    <div class="row">
      <!-- Left Column: Recent Newsletters -->
      <?php if ($userID && count($recentNewsletters) > 0): ?>
      <div class="col-md-3 mb-4">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Newsletters</h5>
          </div>
          <div class="card-body recent-newsletters">
            <?php foreach ($recentNewsletters as $recent): ?>
              <div class="newsletter-item <?php echo ($editMode && $recent['newsletter_ID'] == $newsletterID) ? 'active' : ''; ?>">
                <div class="d-flex justify-content-between align-items-start">
                  <div class="flex-grow-1">
                    <h6 class="mb-1"><?php echo htmlspecialchars(substr($recent['title'], 0, 30)) . (strlen($recent['title']) > 30 ? '...' : ''); ?></h6>
                    <small class="text-muted">
                      <i class="far fa-calendar me-1"></i><?php echo date('M j, Y', strtotime($recent['creation_date'])); ?>
                    </small>
                    <br>
                    <span class="badge bg-<?php echo $recent['status'] === 'published' ? 'success' : 'secondary'; ?> mt-1">
                      <?php echo ucfirst($recent['status']); ?>
                    </span>
                  </div>
                  <div class="btn-group-vertical btn-group-sm">
                    <a href="pages/newsletter_view.php?id=<?php echo $recent['newsletter_ID']; ?>" 
                       class="btn btn-sm btn-outline-primary" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-danger delete-btn" 
                            data-id="<?php echo $recent['newsletter_ID']; ?>" title="Delete">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Right Column: Generator/Editor -->
      <div class="<?php echo ($userID && count($recentNewsletters) > 0) ? 'col-md-9' : 'col-12'; ?>">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h1><?php echo $editMode ? '<i class="fas fa-edit me-2"></i>Edit Newsletter' : '<i class="fas fa-magic me-2"></i>AI Newsletter Generator'; ?></h1>
          <?php if ($editMode): ?>
            <a href="pages/newsletter_view.php" class="btn btn-secondary">
              <i class="fas fa-plus me-1"></i>Create New
            </a>
          <?php endif; ?>
        </div>

        <?php if ($editMode): ?>
          <!-- Edit Mode -->
          <div class="card mb-4">
            <div class="card-body">
              <label class="form-label">Title</label>
              <input type="text" id="newsletter-title" class="form-control mb-3" 
                     value="<?php echo htmlspecialchars($newsletter['title']); ?>">
              
              <label class="form-label">Content</label>
              <div id="newsletter-editor" class="form-control" 
                   style="min-height: 400px; overflow-y: auto;" contenteditable="true">
                <?php echo $newsletter['content']; ?>
              </div>
              
              <div class="mt-3">
                <label class="form-label">Categories</label>
                <select id="category-select" class="form-select mb-2" multiple size="5">
                  <option value="" disabled>Loading categories...</option>
                </select>
                <button id="add-category-btn" class="btn btn-sm btn-outline-primary">
                  <i class="fas fa-plus me-1"></i>Create New Category
                </button>
              </div>
              
              <div class="mt-3">
                <button id="save-changes-btn" class="btn btn-success">
                  <i class="fas fa-save me-1"></i>Save Changes
                </button>
                <button id="ai-edit-btn" class="btn btn-info">
                  <i class="fas fa-magic me-1"></i>AI Edit
                </button>
                <?php if ($newsletter['status'] === 'draft'): ?>
                  <button id="publish-btn" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-1"></i>Publish
                  </button>
                <?php else: ?>
                  <button id="unpublish-btn" class="btn btn-warning">
                    <i class="fas fa-undo me-1"></i>Unpublish
                  </button>
                <?php endif; ?>
                <button id="delete-current-btn" class="btn btn-danger">
                  <i class="fas fa-trash me-1"></i>Delete
                </button>
              </div>
            </div>
          </div>

        <?php else: ?>
          <!-- Generate Mode -->
          <div class="card mb-4">
            <div class="card-body">
              <label class="form-label">What should the newsletter be about?</label>
              <textarea id="newsletter-query" class="form-control mb-3" rows="4" 
                        placeholder="Example: Latest AI trends in 2025"></textarea>
              <button id="generate-btn" class="btn btn-primary">
                <i class="fas fa-magic me-1"></i>Generate Newsletter
              </button>
            </div>
          </div>

          <div id="loading" style="display:none;" class="text-center my-4">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2">Generating newsletter... (may take 1-2 minutes)</p>
          </div>

          <div id="result" style="display:none;" class="card">
            <div class="card-header">
              <h5 class="mb-0">Generated Newsletter</h5>
            </div>
            <div class="card-body" id="newsletter-content"></div>
          </div>
        <?php endif; ?>

        <div id="error" style="display:none;" class="alert alert-danger"></div>
      </div>
    </div>
  </div>

  <script src="bootstrap-5.3.6-dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Category Modal -->
  <div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Create New Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="new-category-name" class="form-label">Category Name *</label>
            <input type="text" class="form-control" id="new-category-name" required>
          </div>
          <div class="mb-3">
            <label for="new-category-description" class="form-label">Description (Optional)</label>
            <textarea class="form-control" id="new-category-description" rows="3"></textarea>
          </div>
          <div id="category-modal-error" class="alert alert-danger" style="display:none;"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="create-category-btn">Create Category</button>
        </div>
      </div>
    </div>
  </div>

  <?php if ($editMode): ?>
  <!-- Edit Mode Script -->
  <script>
    const newsletterID = <?php echo $newsletterID; ?>;
    const currentStatus = '<?php echo $newsletter['status']; ?>';
    let allCategories = [];
    let selectedCategoryIDs = [];

    // Load categories
    async function loadCategories() {
      try {
        const response = await fetch('scripts/php/categories.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'fetch' })
        });
        
        const data = await response.json();
        
        if (data.success) {
          allCategories = data.categories;
          
          // Load newsletter's current categories
          const catResponse = await fetch('scripts/php/categories.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
              action: 'fetch_for_newsletter',
              newsletter_ID: newsletterID
            })
          });
          
          const catData = await catResponse.json();
          if (catData.success) {
            selectedCategoryIDs = catData.category_ids.map(id => parseInt(id));
          }
          
          renderCategorySelect();
        }
      } catch (err) {
        console.error('Error loading categories:', err);
      }
    }

    function renderCategorySelect() {
      const select = document.getElementById('category-select');
      select.innerHTML = '';
      
      if (allCategories.length === 0) {
        select.innerHTML = '<option value="" disabled>No categories available</option>';
        return;
      }
      
      allCategories.forEach(cat => {
        const option = document.createElement('option');
        option.value = cat.category_ID;
        option.textContent = cat.name || cat.category_name;
        if (selectedCategoryIDs.includes(parseInt(cat.category_ID))) {
          option.selected = true;
        }
        select.appendChild(option);
      });
    }

    function getSelectedCategories() {
      const select = document.getElementById('category-select');
      return Array.from(select.selectedOptions).map(opt => parseInt(opt.value));
    }

    // Load categories on page load
    loadCategories();

    // Create new category modal
    const categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
    
    document.getElementById('add-category-btn').addEventListener('click', function() {
      document.getElementById('new-category-name').value = '';
      document.getElementById('new-category-description').value = '';
      document.getElementById('category-modal-error').style.display = 'none';
      categoryModal.show();
    });

    document.getElementById('create-category-btn').addEventListener('click', async function() {
      const name = document.getElementById('new-category-name').value.trim();
      const description = document.getElementById('new-category-description').value.trim();
      const error = document.getElementById('category-modal-error');
      const btn = this;
      
      if (!name) {
        error.textContent = 'Category name is required';
        error.style.display = 'block';
        return;
      }
      
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Creating...';
      error.style.display = 'none';
      
      try {
        const response = await fetch('scripts/php/categories.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ 
            action: 'create',
            category_name: name,
            category_description: description
          })
        });
        
        const data = await response.json();
        
        if (data.success) {
          // Add to categories list
          allCategories.push({
            category_ID: data.category_ID,
            category_name: data.category_name
          });
          
          // Add to selected
          selectedCategoryIDs.push(parseInt(data.category_ID));
          
          renderCategorySelect();
          categoryModal.hide();
          
          btn.innerHTML = 'Create Category';
          btn.disabled = false;
        } else {
          error.textContent = data.error || 'Failed to create category';
          error.style.display = 'block';
          btn.innerHTML = 'Create Category';
          btn.disabled = false;
        }
      } catch (err) {
        error.textContent = 'Error: ' + err.message;
        error.style.display = 'block';
        btn.innerHTML = 'Create Category';
        btn.disabled = false;
      }
    });

    // Save changes
    document.getElementById('save-changes-btn').addEventListener('click', async function() {
      const title = document.getElementById('newsletter-title').value.trim();
      const content = document.getElementById('newsletter-editor').innerHTML;
      const categoryIDs = getSelectedCategories();
      const saveBtn = this;
      const error = document.getElementById('error');
      
      if (!title) {
        error.textContent = 'Title is required';
        error.style.display = 'block';
        return;
      }
      
      saveBtn.disabled = true;
      saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
      error.style.display = 'none';
      
      try {
        const response = await fetch('scripts/php/newsletters.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ 
            action: 'update',
            newsletter_ID: newsletterID,
            title: title,
            content: content,
            category_ids: categoryIDs
          })
        });
        
        const data = await response.json();
        
        if (data.success) {
          saveBtn.innerHTML = '<i class="fas fa-check me-1"></i>Saved!';
          setTimeout(() => {
            saveBtn.innerHTML = '<i class="fas fa-save me-1"></i>Save Changes';
            saveBtn.disabled = false;
          }, 2000);
        } else {
          error.textContent = data.error || 'Failed to save changes';
          error.style.display = 'block';
          saveBtn.innerHTML = '<i class="fas fa-save me-1"></i>Save Changes';
          saveBtn.disabled = false;
        }
      } catch (err) {
        error.textContent = 'Error: ' + err.message;
        error.style.display = 'block';
        saveBtn.innerHTML = '<i class="fas fa-save me-1"></i>Save Changes';
        saveBtn.disabled = false;
      }
    });

        // AI Edit functionality
    document.getElementById('ai-edit-btn').addEventListener('click', async function() {
      const content = document.getElementById('newsletter-editor').innerHTML;
      const instructions = prompt('What changes would you like the AI to make?\n\nExamples:\n- Make it more professional\n- Add more details\n- Make it shorter\n- Improve the tone');
      
      if (!instructions) return;
      
      const aiBtn = this;
      const error = document.getElementById('error');
      const editor = document.getElementById('newsletter-editor');
      
      aiBtn.disabled = true;
      aiBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>AI is editing...';
      error.style.display = 'none';
      
      // Show loading overlay
      editor.style.opacity = '0.5';
      editor.contentEditable = 'false';
      
      try {
        const response = await fetch('scripts/php/newsletters.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ 
            action: 'ai_edit',
            content: content,
            instructions: instructions
          })
        });
        
        const data = await response.json();
        
        if (data.success !== false) {
          const editedContent = data.content || data.html || data.body || data;
          editor.innerHTML = typeof editedContent === 'string' ? editedContent : JSON.stringify(editedContent);
          
          // Show success message
          const successMsg = document.createElement('div');
          successMsg.className = 'alert alert-success alert-dismissible fade show mt-2';
          successMsg.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>AI edit completed! Don't forget to save your changes.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          `;
          editor.parentElement.insertBefore(successMsg, editor);
          
          setTimeout(() => {
            successMsg.remove();
          }, 5000);
          
          aiBtn.innerHTML = '<i class="fas fa-magic me-1"></i>AI Edit';
          aiBtn.disabled = false;
        } else {
          error.textContent = data.error || 'Failed to process AI edit';
          error.style.display = 'block';
          aiBtn.innerHTML = '<i class="fas fa-magic me-1"></i>AI Edit';
          aiBtn.disabled = false;
        }
      } catch (err) {
        error.textContent = 'Error: ' + err.message;
        error.style.display = 'block';
        aiBtn.innerHTML = '<i class="fas fa-magic me-1"></i>AI Edit';
        aiBtn.disabled = false;
      } finally {
        editor.style.opacity = '1';
        editor.contentEditable = 'true';
      }
    });

    // Publish/Unpublish
    const statusBtn = document.getElementById(currentStatus === 'draft' ? 'publish-btn' : 'unpublish-btn');
    if (statusBtn) {
      statusBtn.addEventListener('click', async function() {
        const action = currentStatus === 'draft' ? 'publish' : 'unpublish';
        const confirmMsg = action === 'publish' ? 'Publish this newsletter?' : 'Unpublish this newsletter?';
        
        if (!confirm(confirmMsg)) return;
        
        const btn = this;
        const error = document.getElementById('error');
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>' + (action === 'publish' ? 'Publishing...' : 'Unpublishing...');
        error.style.display = 'none';
        
        try {
          // If publishing, save content and categories first
          const requestData = { 
            action: action,
            newsletter_ID: newsletterID
          };
          
          if (action === 'publish') {
            requestData.title = document.getElementById('newsletter-title').value.trim();
            requestData.content = document.getElementById('newsletter-editor').innerHTML;
            requestData.category_ids = getSelectedCategories();
          }
          
          const response = await fetch('scripts/php/newsletters.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(requestData)
          });
          
          const data = await response.json();
          
          if (data.success) {
            alert(action === 'publish' ? 'Newsletter published!' : 'Newsletter unpublished!');
            location.reload();
          } else {
            error.textContent = data.error || 'Failed to ' + action;
            error.style.display = 'block';
            btn.innerHTML = '<i class="fas fa-' + (action === 'publish' ? 'paper-plane' : 'undo') + ' me-1"></i>' + 
                           (action === 'publish' ? 'Publish' : 'Unpublish');
            btn.disabled = false;
          }
        } catch (err) {
          error.textContent = 'Error: ' + err.message;
          error.style.display = 'block';
          btn.innerHTML = '<i class="fas fa-' + (action === 'publish' ? 'paper-plane' : 'undo') + ' me-1"></i>' + 
                         (action === 'publish' ? 'Publish' : 'Unpublish');
          btn.disabled = false;
        }
      });
    }

    // Delete current newsletter
    document.getElementById('delete-current-btn').addEventListener('click', async function() {
      if (!confirm('Are you sure you want to delete this newsletter? This cannot be undone.')) return;
      
      const btn = this;
      const error = document.getElementById('error');
      
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Deleting...';
      
      try {
        const response = await fetch('scripts/php/newsletters.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ 
            action: 'delete',
            newsletter_ID: newsletterID
          })
        });
        
        const data = await response.json();
        
        if (data.success) {
          alert('Newsletter deleted successfully!');
          window.location.href = 'pages/newsletter_view.php';
        } else {
          error.textContent = data.error || 'Failed to delete newsletter';
          error.style.display = 'block';
          btn.innerHTML = '<i class="fas fa-trash me-1"></i>Delete';
          btn.disabled = false;
        }
      } catch (err) {
        error.textContent = 'Error: ' + err.message;
        error.style.display = 'block';
        btn.innerHTML = '<i class="fas fa-trash me-1"></i>Delete';
        btn.disabled = false;
      }
    });

    // Delete buttons in sidebar
    document.querySelectorAll('.delete-btn').forEach(btn => {
      btn.addEventListener('click', async function(e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this newsletter?')) return;
        
        const id = this.dataset.id;
        
        try {
          const response = await fetch('scripts/php/newsletters.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
              action: 'delete',
              newsletter_ID: id
            })
          });
          
          const data = await response.json();
          
          if (data.success) {
            if (id == newsletterID) {
              window.location.href = 'pages/newsletter_view.php';
            } else {
              location.reload();
            }
          } else {
            alert('Error: ' + (data.error || 'Failed to delete'));
          }
        } catch (err) {
          alert('Error: ' + err.message);
        }
      });
    });
  </script>
  <?php else: ?>
  <!-- Generate Mode Script -->
  <script src="scripts/newsletterPrompt.js"></script>
  <script>
    // Delete buttons in sidebar
    document.querySelectorAll('.delete-btn').forEach(btn => {
      btn.addEventListener('click', async function(e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this newsletter?')) return;
        
        const newsletterID = this.dataset.id;
        
        try {
          const response = await fetch('scripts/php/newsletters.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
              action: 'delete',
              newsletter_ID: newsletterID
            })
          });
          
          const data = await response.json();
          
          if (data.success) {
            location.reload();
          } else {
            alert('Error: ' + (data.error || 'Failed to delete'));
          }
        } catch (err) {
          alert('Error: ' + err.message);
        }
      });
    });
  </script>
  <?php endif; ?>

  <script>
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

    const savedTheme = localStorage.getItem('theme') || 'dark';
    if (savedTheme === 'light') {
      document.body.classList.add('light-mode');
    }
  </script>
</body>
</html>