function activateTab(position,node) {
    var children=node.children;
    for(var count=0;count<children.length;count++) {
        if(count==position) {
            children[count].setAttribute('class','active');
        } else {
            children[count].setAttribute('class','');
        }
    }
    var children=node.nextSibling.children;
    for(var count=0;count<children.length;count++) {
        if(count==position) {
            children[count].setAttribute('style','display:block');
        } else {
            children[count].setAttribute('style','');
        }
    }
}