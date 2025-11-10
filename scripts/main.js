const contentBox = document.getElementById("pageContent");

// Global Popup System - accessible from all pages
window.popupSystem = {
  // Store data globally
  newsletters: {},
  categories: {},
  users: {},

  // Initialize data (called from pages)
  initData: function(newsletters, categories, users) {
    this.newsletters = newsletters || {};
    this.categories = categories || {};
    this.users = users || {};
  },

  // Strip HTML tags for preview text
  stripHtml: function(html) {
    const tmp = document.createElement('div');
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || '';
  },

  // Truncate text
  truncateText: function(text, maxLength = 200) {
    const stripped = this.stripHtml(text);
    if (stripped.length <= maxLength) return stripped;
    return stripped.substr(0, maxLength) + '...';
  },

  // Open Newsletter Popup
  newsPopup: function(id) {
    const newsletter = this.newsletters[id];
    if (!newsletter) return;

    const modalHTML = `
      <div class="global-modal-overlay" onclick="window.popupSystem.closePopup()">
        <div class="global-modal-wrapper" onclick="event.stopPropagation()">
          <div class="global-modal-header">
            <h2 class="global-modal-title">${this.escapeHtml(newsletter.title)}</h2>
            <button class="global-modal-close" onclick="window.popupSystem.closePopup()">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="global-modal-body">
            <div class="global-modal-meta">
              <div class="global-modal-meta-item">
                <span class="global-modal-label">Created</span>
                <span class="global-modal-value">${new Date(newsletter.creation_date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}</span>
              </div>
              <div class="global-modal-meta-item">
                <span class="global-modal-label">Categories</span>
                <span class="global-modal-value">${(newsletter.categories || []).length}</span>
              </div>
              <div class="global-modal-meta-item">
                <span class="global-modal-label">Contributors</span>
                <span class="global-modal-value">${(newsletter.contributors || []).length}</span>
              </div>
            </div>
            <div class="global-modal-section">
              <div class="global-modal-label">Content</div>
              <div class="global-modal-content">${newsletter.content}</div>
            </div>
          </div>
        </div>
      </div>
    `;

    this.showModal(modalHTML);
  },

  // Open Category Popup
  openCategoryPopup: function(id) {
    const category = this.categories[id];
    if (!category) return;

    const categoryNewsletters = (category.newsletters || [])
      .map(nId => ({ id: nId, data: this.newsletters[nId] }))
      .filter(n => n.data);

    // Get top 3 recent newsletters
    const recentNewsletters = [...categoryNewsletters]
      .sort((a, b) => new Date(b.data.creation_date) - new Date(a.data.creation_date))
      .slice(0, 3);

    // Get top 3 favorited newsletters
    const favoritedNewsletters = [...categoryNewsletters]
      .sort((a, b) => (b.data.favorited_by || []).length - (a.data.favorited_by || []).length)
      .slice(0, 3);

    const recentHTML = recentNewsletters.length > 0 
      ? recentNewsletters.map(n => `
          <div class="newsletter-card-mini" onclick="window.popupSystem.newsPopup(${n.id})">
            <div class="newsletter-card-mini-header">
              <i class="fas fa-newspaper"></i>
              <h4>${this.escapeHtml(n.data.title)}</h4>
            </div>
            <p>${this.truncateText(n.data.content, 100)}</p>
            <div class="newsletter-card-mini-footer">
              <small><i class="fas fa-calendar-alt"></i> ${new Date(n.data.creation_date).toLocaleDateString()}</small>
            </div>
          </div>
        `).join('')
      : '<p class="text-muted">No newsletters yet</p>';

    const favoritedHTML = favoritedNewsletters.length > 0
      ? favoritedNewsletters.map(n => `
          <div class="newsletter-card-mini" onclick="window.popupSystem.newsPopup(${n.id})">
            <div class="newsletter-card-mini-header">
              <i class="fas fa-newspaper"></i>
              <h4>${this.escapeHtml(n.data.title)}</h4>
            </div>
            <p>${this.truncateText(n.data.content, 100)}</p>
            <div class="newsletter-card-mini-footer">
              <small><i class="fas fa-heart"></i> ${(n.data.favorited_by || []).length} favorites</small>
            </div>
          </div>
        `).join('')
      : '<p class="text-muted">No favorited newsletters</p>';

    const modalHTML = `
      <div class="global-modal-overlay" onclick="window.popupSystem.closePopup()">
        <div class="global-modal-wrapper" onclick="event.stopPropagation()">
          <div class="global-modal-header">
            <h2 class="global-modal-title">${this.escapeHtml(category.name)}</h2>
            <button class="global-modal-close" onclick="window.popupSystem.closePopup()">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="global-modal-body">
            <div class="global-modal-meta">
              <div class="global-modal-meta-item">
                <span class="global-modal-label">Total Newsletters</span>
                <span class="global-modal-value">${categoryNewsletters.length}</span>
              </div>
              <div class="global-modal-meta-item">
                <span class="global-modal-label">Contributors</span>
                <span class="global-modal-value">${(category.contributors || []).length}</span>
              </div>
            </div>
            
            <div class="global-modal-section">
              <div class="global-modal-label"><i class="fas fa-clock"></i> Recently Added</div>
              <div class="newsletters-grid">
                ${recentHTML}
              </div>
            </div>

            <div class="global-modal-section">
              <div class="global-modal-label"><i class="fas fa-heart"></i> Most Favorited</div>
              <div class="newsletters-grid">
                ${favoritedHTML}
              </div>
            </div>
          </div>
        </div>
      </div>
    `;

    this.showModal(modalHTML);
  },

  // Open Creator Popup
  openCreatorPopup: function(id) {
    const user = this.users[id];
    if (!user) return;

    const createdNewsletters = (user.c_newsletters || [])
      .map(nId => ({ id: nId, data: this.newsletters[nId] }))
      .filter(n => n.data);

    // Get newsletters where user contributed but didn't create
    const contributedNewsletters = [];
    for (let nId in this.newsletters) {
      const newsletter = this.newsletters[nId];
      if ((newsletter.contributors || []).includes(parseInt(id)) && newsletter.creator_ID != id) {
        contributedNewsletters.push({ id: nId, data: newsletter });
      }
    }

    // Get 5 most recent contributions
    const recentContributions = contributedNewsletters
      .sort((a, b) => new Date(b.data.creation_date) - new Date(a.data.creation_date))
      .slice(0, 5);

    const createdHTML = createdNewsletters.length > 0
      ? createdNewsletters.map(n => `
          <div class="newsletter-card-mini" onclick="window.popupSystem.newsPopup(${n.id})">
            <div class="newsletter-card-mini-header">
              <i class="fas fa-newspaper"></i>
              <h4>${this.escapeHtml(n.data.title)}</h4>
            </div>
            <p>${this.truncateText(n.data.content, 100)}</p>
            <div class="newsletter-card-mini-footer">
              <small><i class="fas fa-calendar-alt"></i> ${new Date(n.data.creation_date).toLocaleDateString()}</small>
            </div>
          </div>
        `).join('')
      : '<p class="text-muted">No newsletters created yet</p>';

    const contributedHTML = recentContributions.length > 0
      ? recentContributions.map(n => `
          <div class="newsletter-card-mini" onclick="window.popupSystem.newsPopup(${n.id})">
            <div class="newsletter-card-mini-header">
              <i class="fas fa-newspaper"></i>
              <h4>${this.escapeHtml(n.data.title)}</h4>
            </div>
            <p>${this.truncateText(n.data.content, 100)}</p>
            <div class="newsletter-card-mini-footer">
              <small><i class="fas fa-calendar-alt"></i> ${new Date(n.data.creation_date).toLocaleDateString()}</small>
            </div>
          </div>
        `).join('')
      : '<p class="text-muted">No contributions yet</p>';

    const modalHTML = `
      <div class="global-modal-overlay" onclick="window.popupSystem.closePopup()">
        <div class="global-modal-wrapper" onclick="event.stopPropagation()">
          <div class="global-modal-header">
            <div class="creator-header-content">
              <div class="creator-avatar-large">
                ${user.username.charAt(0).toUpperCase()}
              </div>
              <div>
                <h2 class="global-modal-title">${this.escapeHtml(user.username)}</h2>
                <span class="creator-role ${user.role.toLowerCase()}">${this.escapeHtml(user.role)}</span>
              </div>
            </div>
            <button class="global-modal-close" onclick="window.popupSystem.closePopup()">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="global-modal-body">
            <div class="global-modal-meta">
              <div class="global-modal-meta-item">
                <span class="global-modal-label">Joined</span>
                <span class="global-modal-value">${new Date(user.creation_date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}</span>
              </div>
              <div class="global-modal-meta-item">
                <span class="global-modal-label">Created</span>
                <span class="global-modal-value">${createdNewsletters.length} newsletters</span>
              </div>
              <div class="global-modal-meta-item">
                <span class="global-modal-label">Contributions</span>
                <span class="global-modal-value">${contributedNewsletters.length} newsletters</span>
              </div>
            </div>

            <div class="global-modal-section">
              <div class="global-modal-label"><i class="fas fa-newspaper"></i> Created Newsletters</div>
              <div class="newsletters-grid">
                ${createdHTML}
              </div>
            </div>

            <div class="global-modal-section">
              <div class="global-modal-label"><i class="fas fa-hands-helping"></i> Recent Contributions</div>
              <div class="newsletters-grid">
                ${contributedHTML}
              </div>
            </div>
          </div>
        </div>
      </div>
    `;

    this.showModal(modalHTML);
  },

  // Helper to show modal
  showModal: function(html) {
    // Remove any existing modal
    this.closePopup();
    
    // Create modal in parent document (not iframe)
    const modalContainer = document.createElement('div');
    modalContainer.id = 'globalModalContainer';
    modalContainer.innerHTML = html;
    document.body.appendChild(modalContainer);

    // Prevent body scroll
    document.body.style.overflow = 'hidden';

    // Close on escape key
    this.escapeHandler = (e) => {
      if (e.key === 'Escape') this.closePopup();
    };
    document.addEventListener('keydown', this.escapeHandler);
  },

  // Close popup
  closePopup: function() {
    const modal = document.getElementById('globalModalContainer');
    if (modal) {
      modal.remove();
      document.body.style.overflow = '';
      if (this.escapeHandler) {
        document.removeEventListener('keydown', this.escapeHandler);
        this.escapeHandler = null;
      }
    }
  },

  // Escape HTML
  escapeHtml: function(text) {
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
  }
};

// Make popup functions globally accessible from iframes
window.newsPopup = (id) => window.popupSystem.newsPopup(id);
window.openCategoryPopup = (id) => window.popupSystem.openCategoryPopup(id);
window.openCreatorPopup = (id) => window.popupSystem.openCreatorPopup(id);

async function search() {

}

document.addEventListener('DOMContentLoaded', () => {
  const iframe = document.getElementById('pageContent');
  const navLinks = Array.from(document.querySelectorAll('a[data-page]'));
  const menuToggle = document.getElementById('menuToggle');
  const mobileMenu = document.getElementById('mobileMenu');

  function setActive(link) {
    navLinks.forEach(l => l.classList.remove('active'));
    if (link) link.classList.add('active');
  }

  function loadPage(path, clickedLink) {
    if (!iframe) return;
    iframe.src = path;
    setActive(clickedLink);
    // close mobile menu if open
    if (mobileMenu && window.getComputedStyle(mobileMenu).display !== 'none') {
      mobileMenu.style.display = 'none';
    }
  }

  navLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const page = link.getAttribute('data-page');
      if (page) loadPage(page, link);
    });
  });

  if (menuToggle && mobileMenu) {
    menuToggle.addEventListener('click', () => {
      const isHidden = mobileMenu.style.display === 'none' || getComputedStyle(mobileMenu).display === 'none';
      mobileMenu.style.display = isHidden ? 'block' : 'none';
    });
  }

  // Try to auto-resize iframe to fit content (works when same-origin)
  if (iframe) {
    iframe.addEventListener('load', () => {
      try {
        const doc = iframe.contentDocument || iframe.contentWindow.document;
        const height = Math.max(doc.documentElement.scrollHeight, doc.body.scrollHeight);
        iframe.style.minHeight = height + 'px';
      } catch (err) {
        // cross-origin or access denied; ignore
      }
    });
  }

  // Listen for navigation messages from iframe
  window.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'navigateTo') {
      const targetLink = navLinks.find(link => link.getAttribute('data-page') === event.data.page);
      loadPage(event.data.page, targetLink);
    }
  });

});