(function(){
    tinymce.create('tinymce.plugins.ATDeeplinkPlugin', {
        init: function(ed, url) {
            ed.addButton('at_deeplink_button', {
                title: 'AT Deeplink',
                text: '[at]',
                cmd: 'at_deeplink_command',
                /*image: url + '/img/at.png'*/
            });
            ed.addCommand('at_deeplink_command', function() {
                var selectedText = ed.selection.getContent(/*{format: 'html'}*/);
                var win = ed.windowManager.open({
                    title: 'Deeplink Properties',
                    body: [
                        {
                            type: 'textbox',
                            name: 'nxlink',
                            label: 'Link*',
                            minWidth: 500,
                            value: ''
                        },
                        {
                            type: 'textbox',
                            name: 'nxcontent',
                            label: 'Nội dung hiển thị',
                            minWidth: 500,
                            value : selectedText
                        }
                    ],
                    buttons: [
                        {
                            text: "Ok",
                            subtype: "primary",
                            onclick: function() {
                                win.submit();
                            }
                        },
                        {
                            text: "Skip",
                            onclick: function() {
                                win.close();
                                var returnText = '' + selectedText + '';
                                ed.execCommand('mceInsertContent', 0, returnText);
                            }
                        },
                        {
                            text: "Cancel",
                            onclick: function() {
                                win.close();
                            }
                        }
                    ],
                    onsubmit: function(e){
                        var returnText = '';
                        if( e.data.nxcontent.length > 0 && e.data.nxlink.length > 0 ) {
                            returnText = '[at url="'+ e.data.nxlink +'"]' + e.data.nxcontent + '[/at]'; 
                        } else if( e.data.nxlink.length > 0 ) {
                            returnText = '[at]' + e.data.nxlink + '[/at]'; 
                        }
                        ed.execCommand('mceInsertContent', 0, returnText);
                    }
                });
            });
        },
        getInfo: function() {
            return {
                longname : 'Nhymxu AT Deeplink Generater',
                author : 'Dũng Nguyễn',
                authorurl : 'https://dungnt.net',
                version : "0.2"
            };
        }
    });
    tinymce.PluginManager.add( 'at_deeplink_button', tinymce.plugins.ATDeeplinkPlugin );
})();