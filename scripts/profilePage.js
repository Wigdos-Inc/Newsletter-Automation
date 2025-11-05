const profile_tabs = document.getElementById('profile_tabs');

function swapTab(tabID){
    document.getElementById(tabID);

    for(let i = 0; i < (profile_tabs.childNodes.length - 1) / 2; i++){
        let offset = i * 2;
        const curNode = profile_tabs.childNodes[offset +1];
        if(curNode.id == tabID){curNode.style.display = 'block';}
        else{curNode.style.display = 'none';}
    }
}

document.getElementById('btn_tab_about').addEventListener('click', (e) => {
    swapTab('tab_about');
});
document.getElementById('btn_tab_settings').addEventListener('click', (e) => {
    swapTab('tab_settings');
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
    } else {
        swapTab('tab_about'); // Default to about tab
    }
});
