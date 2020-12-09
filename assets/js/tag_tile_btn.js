(function() {
    const postsValuesTiles = [];
    jQuery.each(values_tiles_button, function(key, value) {
        postsValuesTiles.push({text:value, value:key});
    });
    tinymce.create("tinymce.plugins.true_mce_button", {
        init : function(editor) {
            editor.addButton("true_mce_button", {
                type: 'listbox',
                text: 'Метки тегов',
                values: postsValuesTiles,
                onselect: function() {
                    let value = this.value();
                    let return_t = '[tiles id=' + value + ']';
                    editor.execCommand("mceInsertContent", 0, return_t);
                }
            });
        },

        createControl : function() {
            return null;
        }
    });
    tinymce.PluginManager.add("true_mce_button", tinymce.plugins.true_mce_button);
})();