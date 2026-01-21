function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });
    return vars;
}

var auniqueid = getUrlVars()["id"];

CKEDITOR.dialog.add('custimageDialog',function(editor){return{title:'Image Properties',minWidth:400,minHeight:200,contents:[{id:'tab-basic',label:'Basic Settings',elements:[{type:'text',id:'src',label:'Source',validate:CKEDITOR.dialog.validate.notEmpty("Image source field cannot be empty")},{type:'text',id:'alt',label:'Alternative'}]}],onShow:function(){CKEDITOR.dialog.getCurrent().hide();
var my=window.open("/common/ckeditor/plugins/custimage/dialogs/dialog.php?id=" +  auniqueid,"ImageBrowser","menubar=0,resizable=0,width=500,height=300");//change this code to your desired page
my.focus()},onOk:function(){var dialog=this;var custimage=editor.document.createElement('img');custimage.setAttribute('src',dialog.getValueOf('tab-basic','src'));custimage.setAttribute('alt',dialog.getValueOf('tab-basic','alt'));editor.insertElement(custimage)}}});