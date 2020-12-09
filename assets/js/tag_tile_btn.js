(function() {
    const postsValuesRound = [];
    jQuery.each(postsValues_round_button, function(key, value) {
        postsValuesRound.push({text:value, value:key});
    });
    tinymce.create("tinymce.plugins.true_mce_button", {
        init : function(ed) {
            ed.addButton("true_mce_button", {
                type: 'listbox',
                text: 'Метки тегов',
                values: postsValuesRound,
                onselect: function() {
                    let v = this.value();
                    let return_text = '[tiles id=' + v + ']';
                    ed.execCommand("mceInsertContent", 0, return_text);
                }
            });
        },

        createControl : function() {
            return null;
        }
    });
    tinymce.PluginManager.add("true_mce_button", tinymce.plugins.true_mce_button);
})();