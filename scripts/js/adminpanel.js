const profile_tabs = document.getElementById('profile_tabs');

function swapTab(tabID){
    // Hide all tabs and show the selected one
    for(let i = 0; i < (profile_tabs.childNodes.length - 1) / 2; i++){
        let offset = i * 2;
        const curNode = profile_tabs.childNodes[offset + 1];
        if(curNode.id == tabID){
            curNode.style.display = 'block';
        } else {
            curNode.style.display = 'none';
        }
    }
    
    // Update active state on navigation links
    document.querySelectorAll('.admin-nav-link').forEach(link => {
        link.classList.remove('active');
    });
    
    // Find and activate the corresponding button
    const activeButton = document.getElementById('btn_' + tabID);
    if(activeButton){
        activeButton.classList.add('active');
    }
    
    // Force layout recalculation
    profile_tabs.style.display = 'none';
    profile_tabs.offsetHeight; // Trigger reflow
    profile_tabs.style.display = 'flex';
    
    // Notify parent to resize iframe after DOM settles
    setTimeout(() => {
        if (window.parent) {
            window.parent.postMessage({ type: 'resizeIframe' }, '*');
        }
    }, 10);
}

document.getElementById('btn_tab_create').addEventListener('click', (e) => {
    swapTab('tab_create');
});
document.getElementById('btn_tab_generator').addEventListener('click', (e) => {
    swapTab('tab_generator');
});
document.getElementById('btn_tab_viewLetters').addEventListener('click', (e) => {
    swapTab('tab_viewLetters');
});
document.getElementById('btn_tab_viewCategories').addEventListener('click', (e) => {
    swapTab('tab_viewCategories');
});

// Restore active tab on page load
document.addEventListener('DOMContentLoaded', () => {
    const activeTab = sessionStorage.getItem('activeAdminTab');
    if (activeTab) {
        swapTab(activeTab);
        sessionStorage.removeItem('activeAdminTab'); // Clear it after use
    }
});
