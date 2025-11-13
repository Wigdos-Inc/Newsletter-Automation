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

document.getElementById('btn_tab_about').addEventListener('click', (e) => {
    swapTab('tab_about');
});
document.getElementById('btn_tab_settings').addEventListener('click', (e) => {
    swapTab('tab_settings');
});
document.getElementById('btn_tab_favorites')?.addEventListener('click', (e) => {
    swapTab('tab_favorites');
});

document.getElementById('delaccBtn').addEventListener('click', (e) => {
    window.location.href = './profile_view.php?delac=1';
});

let params = new URLSearchParams(document.location.search);
let delac = params.get("delac");

if(delac){
    swapTab('tab_settings');
    document.getElementById('delconfirm').style.display = 'block';
}

// Check for hash on page load to show the correct tab
window.addEventListener('load', () => {
    const hash = window.location.hash;
    if (hash === '#settings') {
        swapTab('tab_settings');
    } else if (hash === '#profile') {
        swapTab('tab_about');
    } else if (hash === '#favorites') {
        swapTab('tab_favorites');
    } else {
        swapTab('tab_about'); // Default to about tab
    }
});
