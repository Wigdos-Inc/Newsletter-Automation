const contentBox = document.getElementById("pageContent");

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