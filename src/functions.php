<?php
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_head','wp_shortlink_wp_head', 10, 0 );
remove_action('wp_head','adjacent_posts_rel_link_wp_head', 10);
remove_action('wp_print_styles', 'print_emoji_styles' );
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('admin_print_styles', 'print_emoji_styles');
//自動pタグのキャンセル
add_action('init', function() {
  remove_filter('the_excerpt', 'wpautop');
  remove_filter('the_content', 'wpautop');
});
add_filter('tiny_mce_before_init', function($init) {
  $init['wpautop'] = false;
  $init['apply_source_formatting'] = true;
  return $init;
});
//アイキャッチの有効化
add_theme_support('post-thumbnails');
//カテゴリーごとの表示件数変更
function change_posts_per_page($query) {
    if ( is_admin() || ! $query->is_main_query() )
        return;
    if ( $query->is_category() ) { /* カテゴリーページの時に表示件数を5件にセット */
        $query->set( 'posts_per_page', '5' );
    }
}
add_action( 'pre_get_posts', 'change_posts_per_page' );
//子カテゴリーがある親カテゴリーは選べなくする
require_once(ABSPATH . '/wp-admin/includes/template.php');
class Danda_Category_Checklist extends Walker_Category_Checklist {
     function start_el( &$output, $category, $depth, $args, $id = 0 ) {
        extract($args);
        if ( empty($taxonomy) )
            $taxonomy = 'category';
        if ( $taxonomy == 'category' )
            $name = 'post_category';
        else
            $name = 'tax_input['.$taxonomy.']';
        $class = in_array( $category->term_id, $popular_cats ) ? ' class="popular-category"' : '';
  $cat_child = get_category_children($category->term_id);
  if($cat_child !== "") {
            $output .= "\n<li id='{$taxonomy}-{$category->term_id}'$class>" . '<label class="selectit"><input value="' . $category->term_id . '" type="checkbox" name="'.$name.'[]" id="in-'.$taxonomy.'-' . $category->term_id . '"' . checked( in_array( $category->term_id, $selected_cats ), true, false ) . disabled( empty( $args['disabled'] ), true, false ) . ' /> ' . esc_html( apply_filters('the_category', $category->name )) . '</label>';
        }else{
            $output .= "\n<li id='{$taxonomy}-{$category->term_id}'$class>" . '<label class="selectit"><input value="' . $category->term_id . '" type="checkbox" name="'.$name.'[]" id="in-'.$taxonomy.'-' . $category->term_id . '"' . checked( in_array( $category->term_id, $selected_cats ), true, false ) . disabled( empty( $args['disabled'] ), false, false ) . ' /> ' . esc_html( apply_filters('the_category', $category->name )) . '</label>';
        }
    }
}
function lig_wp_category_terms_checklist_no_top( $args, $post_id = null ) {
    $args['checked_ontop'] = false;
    $args['walker'] = new Danda_Category_Checklist();
    return $args;
}
add_action( 'wp_terms_checklist_args', 'lig_wp_category_terms_checklist_no_top' );
//カテゴリーを一つしか選べなくする
add_action( 'admin_print_footer_scripts', 'limit_category_select' );
function limit_category_select() {
  ?>
  <script type="text/javascript">
    jQuery(function($) {
      // 投稿画面のカテゴリー選択を制限
      var cat_checklist = $('.categorychecklist input[type=checkbox]');
      cat_checklist.click( function() {
        $(this).parents('.categorychecklist').find('input[type=checkbox]').attr('checked', false);
        $(this).attr('checked', true);
      });
      // クイック編集のカテゴリー選択を制限
      var quickedit_cat_checklist = $('.cat-checklist input[type=checkbox]');
      quickedit_cat_checklist.click( function() {
        $(this).parents('.cat-checklist').find('input[type=checkbox]').attr('checked', false);
        $(this).attr('checked', true);
      });
      $('.categorychecklist>li:first-child, .cat-checklist>li:first-child').before('<p style="padding-top:5px;">カテゴリーは1つしか選択できません</p>');
    });
  </script>
  <?php
}
//固定ページではビジュアルエディタを利用できないようにする
function disable_visual_editor_in_page(){
  global $typenow;
  if( $typenow == 'page' ){
    add_filter('user_can_richedit', 'disable_visual_editor_filter');
  }
}
function disable_visual_editor_filter(){
  return false;
}
add_action( 'load-post.php', 'disable_visual_editor_in_page' );
add_action( 'load-post-new.php', 'disable_visual_editor_in_page' );