(function() {
    const postsValuesTiles = [];
    jQuery.each(postsValues_tiles_button, function(key, value) {
        valuesTiles.push({text:value, val:key});
    });
    tinymce.create("tinymce.plugins.true_mce_button", {
        init : function(editor) {
            editor.addButton("true_mce_button", {
                type: 'listbox',
                text: 'Метки тегов',
                values: valuesTiles,
                onselect: function() {
                    let value = this.val();
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