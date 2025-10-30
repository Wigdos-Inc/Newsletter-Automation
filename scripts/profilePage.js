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
