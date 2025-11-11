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
