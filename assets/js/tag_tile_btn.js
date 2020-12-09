(function() {
    const postsValuesTiles = [];
    jQuery.each(postsValues_tiles_button, function(key, value) {
        postsValuesTiles.push({text:value, value:key});
    });
    tinymce.create("tinymce.plugins.true_mce_button", {
        init : function(ed) {
            ed.addButton("true_mce_button", {
                type: 'listbox',
                text: 'Метки тегов',
                values: postsValuesTiles,
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