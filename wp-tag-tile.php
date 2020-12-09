<?php
/*
Plugin Name: Wordpress Tag Tile
Plugin URI: https://alexlundin.com
Description: Tag Tile for Wordpress
Version: 0.1.0
Author: Alexandr Lundin
Author URI: https://alexlundin.com
Text Domain: tag-tile
Domain Path: /languages
*/

if (!defined('WPINC')) {
    die;
}

add_action('init', 'register_tag_tile');

function register_tag_tile()
{
    register_post_type('tag', array(
        'label' => __('Tag Tiles', 'wp-seo-tag-tile'),
        'public' => false,
        'publicly_queryable' => false,
        'exclude_from_search' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 24,
        'menu_icon' => 'dashicons-tag',
        'capability_type' => 'post',
        'hierarchical' => false,
        'supports' => array('title', 'editor'),
        'labels' => array(
            'name' => __('Tag Tiles', 'wp-seo-tag-tile'),
            'singular_name' => __('Tile', 'wp-seo-tag-tile'),
            'menu_name' => __('Tag Tiles', 'wp-seo-tag-tile'),
            'add_new' => __('Add Tile', 'wp-seo-tag-tile'),
            'add_new_item' => __('Add New Tile', 'wp-seo-tag-tile'),
            'edit' => __('Edit', 'wp-seo-tag-tile'),
            'edit_item' => __('Edit Tile', 'wp-seo-tag-tile'),
            'new_item' => __('New Tile', 'wp-seo-tag-tile'),
            'view' => __('View Tile', 'wp-seo-tag-tile'),
            'view_item' => __('View Tile', 'wp-seo-tag-tile'),
            'search_items' => __('Search Tile', 'wp-seo-tag-tile'),
            'not_found' => __('No Tile Found', 'wp-seo-tag-tile'),
            'not_found_in_trash' => __('Not Tile Found in Trash', 'wp-seo-tag-tile')
        )
    ));
}

/**
 * Регистрация metabox
 */


// Добавляем дополнительное поле
function tiles_meta_box()
{
    add_meta_box(
        'tiles_meta_box', // Идентификатор(id)
        'Варианты отображения', // Заголовок области с мета-полями(title)
        'show_tiles_metabox', // Вызов(callback)
        'tag', // Где будет отображаться наше поле, в нашем случае в Записях
        'side');
}

add_action('add_meta_boxes', 'tiles_meta_box'); // Запускаем функцию

$meta = array(
    array(
        'label' => '',
        'desc' => '',
        'id' => 'templates',
        'type' => 'select',
        'options' => array(
            'one' => array(
                'label' => 'Карусель',
                'value' => 'slider'
            ),
            'two' => array(
                'label' => 'Выпадающий список',
                'value' => 'dropdown',
            ),
            'three' => array(
                'label' => 'Список',
                'value' => 'list'
            )
        )
    )
);


// Вызов метаполей
function show_tiles_metabox()
{
    global $meta; // Обозначим наш массив с полями глобальным
    global $post;  // Глобальный $post для получения id создаваемого/редактируемого поста
    // Выводим скрытый input, для верификации. Безопасность прежде всего!
    echo '<input type="hidden" name="custom_meta_box_nonce" value="' . wp_create_nonce(basename(__FILE__)) . '" />';

    // Начинаем выводить таблицу с полями через цикл
    echo '<table class="form-table">';
    foreach ($meta as $field) {
        $meta_field = get_post_meta($post->ID, $field['id'], true);
        echo ' <p><b>Шорткод для вывода</b> - [tiles id="' . $post->ID . '"]</p>';
        echo '<tr>
                <th style="display: none;"><label for="' . $field['id'] . '">' . $field['label'] . '</label></th>
                <td>';
        switch ($field['type']) {
            case 'select':
                echo '<select name="' . $field['id'] . '" id="' . $field['id'] . '">';
                foreach ($field['options'] as $option) {
                    echo '<option', $meta_field == $option['value'] ? ' selected="selected"' : '', ' value="' . $option['value'] . '">' . $option['label'] . '</option>';
                }
                echo '</select><br /><span class="description">' . $field['desc'] . '</span>';
                break;
        }

        echo '</td></tr>';
    }
    echo '</table>';
}

function save_my_meta_fields($post_id)
{
    global $meta;  // Массив с нашими полями

    // проверяем наш проверочный код
    if (isset($_POST['at_nonce']) && !wp_verify_nonce($_POST['at_nonce'], __FILE__))
        return $post_id;
    // Проверяем авто-сохранение
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return $post_id;
    // Проверяем права доступа
    if ('page' == !isset($_POST['post_type'])) {
        if (!current_user_can('edit_page', $post_id))
            return $post_id;
    } elseif (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    // Если все отлично, прогоняем массив через foreach
    foreach ($meta as $field) {
        $old = get_post_meta($post_id, $field['id'], true); // Получаем старые данные (если они есть), для сверки
        $new = !isset($_POST[$field['id']]);
        if ($new && $new != $old) {  // Если данные новые
            update_post_meta($post_id, $field['id'], $new); // Обновляем данные
        } elseif ('' == $new && $old) {
            delete_post_meta($post_id, $field['id'], $old); // Если данных нету, удаляем мету.
        }
    } // end foreach
}

add_action('save_post', 'save_my_meta_fields'); // Запускаем функцию сохранения

add_filter('user_can_richedit', 'disable_for_cpt');
function disable_for_cpt($default)
{
    global $post;
    if (get_post_type($post) == 'tag')
        return false;
    return $default;
}

/**
 * Регистрация шорткода
 */
function true_misha_func($atts)
{
    $params = shortcode_atts(array('id' => null), $atts);

    $post_content = get_post_field('post_content', $params['id']);
    $skin = get_post_meta($params['id'], 'templates', true);


    ob_start();
    if ($skin === 'list'):
        return '<div class="tag_tile" id="tag_tile">'.  $post_content .'</div>';

    elseif ($skin === 'slider'):
        return '<div class="slider" id="tag_tile">' .
            $post_content
            . '</div>';
    else:
        return '<div class="tag_tile_wrap">
    <div class="tag_tile tag_tile_drop" id="tag_tile">' . $post_content . '</div>
    <span class="more_tags">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" data-prefix="fas"
           data-icon="angle-down" class="svg-inline--fa fa-angle-down fa-w-10" role="img" viewBox="0 0 320 512"><path
              fill="currentColor"
              d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9l22.6-22.6c9.4-9.4 24.6-9.4 33.9 0l96.4 96.4 96.4-96.4c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9l-136 136c-9.2 9.4-24.4 9.4-33.8 0z"/></svg>
    </span>
</div>';
    endif;

    return ob_get_clean();

}

add_shortcode('tiles', 'true_misha_func');

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('tag_tile_slick', plugin_dir_url(__FILE__) . 'assets/slick/slick.css');
    wp_enqueue_style('tag_tile_slick-theme', plugin_dir_url(__FILE__) . 'assets/slick/slick-theme.css');
    wp_enqueue_style('tag_tile_list_frontend', plugin_dir_url(__FILE__) . 'assets/css/tag_tile_list_frontend.css');
    wp_enqueue_script("jquery");
    wp_enqueue_script('tile-slick', plugin_dir_url(__FILE__) . 'assets/slick/slick.min.js', ('jquery'), null, true);
    wp_enqueue_script('tile-script', plugin_dir_url(__FILE__) . 'assets/js/tag_tile_frontend.js', null, null, true);
    wp_enqueue_style('tag_tile_list_frontend', plugin_dir_url(__FILE__) . 'assets/css/tag_tile_list_frontend.css');
});


// Хуки
function true_add_mce_button()
{
    // проверяем права пользователя - может ли он редактировать посты и страницы
    if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
        return; // если не может, то и кнопка ему не понадобится, в этом случае выходим из функции
    }
    // проверяем, включен ли визуальный редактор у пользователя в настройках (если нет, то и кнопку подключать незачем)
    if ('true' == get_user_option('rich_editing')) {
        add_filter('mce_external_plugins', 'true_add_tinymce_script');
        add_filter('mce_buttons', 'true_register_mce_button');
    }
}

add_action('admin_head', 'true_add_mce_button');

// В этом функции указываем ссылку на JavaScript-файл кнопки
function true_add_tinymce_script($plugin_array)
{
    $plugin_array['true_mce_button'] = plugin_dir_url(__FILE__) . 'assets/js/tag_tile_btn.js'; // true_mce_button - идентификатор кнопки
    return $plugin_array;
}

// Регистрируем кнопку в редакторе
function true_register_mce_button($buttons)
{
    array_push($buttons, 'true_mce_button'); // true_mce_button - идентификатор кнопки
    return $buttons;
}

add_action('admin_footer', 'round_plag_get_tiles');
function round_plag_get_tiles()
{
    $arguments = array('post_type' => 'tag', 'post_status' => 'publish', 'posts_per_page' => -1,);
    $list_tag = get_posts($arguments);

    echo '<script>var postsValues_tiles_button = {};';
    $c = 0;
    foreach ($list_tag as $list_item) {
        $item_id = $list_item->ID;
        $item_title = get_the_title($list_item->ID);
        echo "postsValues_tiles_button[{$item_id}] = '{$item_title}';";
        $c++;
    }
    echo '</script>';
}