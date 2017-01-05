<?php
/*
Plugin Name: Good Hook
Plugin URI: http://clubwp.ru
Description: Лучшые хуки для оптимизации работы WordPress
Version: 1.0
Author: Garri
Author URI: http://clubwp.ru
*/

/*  Copyright 2016  Garri  (email : info {at} clubwp.ru)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

########################################################################################################


/*=====================РАБОТА НА САЙТЕ В ОДИНОЧКУ================================*/

	

/* 1. Чистка шапки сайта */

remove_filter('comment_text', 'make_clickable', 9);
remove_action( 'wp_head', 'feed_links_extra', 3 );
remove_action( 'wp_head', 'feed_links', 2 );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'index_rel_link' );
remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );
remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );
remove_action( 'wp_head', 'adjacent_posts_rel_link', 10, 0 );
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
 

/* 2. Удаляем версию WP */

function vc_remove_wp_ver_css_js( $src ) {
    if ( strpos( $src, 'ver=' ) )
        $src = remove_query_arg( 'ver', $src );
    return $src;
}
add_filter( 'style_loader_src', 'vc_remove_wp_ver_css_js', 9999 );
add_filter( 'script_loader_src', 'vc_remove_wp_ver_css_js', 9999 );
remove_action('wp_head', 'wp_generator');

/* 3. Загрузка jQuery с Google */

function my_scripts_method() {
	// получаем версию jQuery
	wp_enqueue_script( 'jquery' );
	$wp_jquery_ver = $GLOBALS['wp_scripts']->registered['jquery']->ver; // для версий WP меньше 3.6 'jquery' меняем на 'jquery-core'
	$jquery_ver = $wp_jquery_ver == '' ? '1.11.0' : $wp_jquery_ver;
	
	wp_deregister_script( 'jquery-core' );
	wp_register_script( 'jquery-core', '//ajax.googleapis.com/ajax/libs/jquery/'. $jquery_ver .'/jquery.min.js' );
	wp_enqueue_script( 'jquery' );
}
add_action( 'wp_enqueue_scripts', 'my_scripts_method', 99 );

/* 4. Удаляет стили .recentcomments */

function twentyten_remove_recent_comments_style() {  
        global $wp_widget_factory;  
        remove_action( 'wp_head', array( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style' ) );  
    }  
add_action( 'widgets_init', 'twentyten_remove_recent_comments_style' );

/* Отключаем WP-JSON и OEMBED */

// Отключаем сам REST API
add_filter('rest_enabled', '__return_false');
 
// Отключаем фильтры REST API
remove_action( 'xmlrpc_rsd_apis', 'rest_output_rsd' );
remove_action( 'wp_head', 'rest_output_link_wp_head', 10, 0 );
remove_action( 'template_redirect', 'rest_output_link_header', 11, 0 );
remove_action( 'auth_cookie_malformed', 'rest_cookie_collect_status' );
remove_action( 'auth_cookie_expired', 'rest_cookie_collect_status' );
remove_action( 'auth_cookie_bad_username', 'rest_cookie_collect_status' );
remove_action( 'auth_cookie_bad_hash', 'rest_cookie_collect_status' );
remove_action( 'auth_cookie_valid', 'rest_cookie_collect_status' );
remove_filter( 'rest_authentication_errors', 'rest_cookie_check_errors', 100 );
 
// Отключаем события REST API
remove_action( 'init', 'rest_api_init' );
remove_action( 'rest_api_init', 'rest_api_default_filters', 10, 1 );
remove_action( 'parse_request', 'rest_api_loaded' );
 
// Отключаем Embeds связанные с REST API
remove_action( 'rest_api_init', 'wp_oembed_register_route');
remove_filter( 'rest_pre_serve_request', '_oembed_rest_pre_serve_request', 10, 4 );
 
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );



/*=====================РАБОТА НА САЙТЕ С АВТОРАМИ================================*/
if( is_admin() ) { // Хуки работают только в админке


// 1. Авторы видят только свои записи

add_filter('parse_query', 'my_parse_query_useronly' );
function my_parse_query_useronly( $wp_query ) {
    if ( strpos( $_SERVER[ 'REQUEST_URI' ], '/wp-admin/edit.php' ) !== false ) {
        if ( !current_user_can( 'level_10' ) ) {
            global $current_user;
            $wp_query->set( 'author', $current_user->id );
        }
    }
}

//  Авторам нельзя редактировать запись через 7 дней

add_filter( 'user_has_cap', 'my_limit_editing', 10, 3 );
function my_limit_editing( $allcaps, $cap, $args ) {
    if( 'edit_post' != $args[0] && 'delete_post' != $args[0]
      || !empty( $allcaps['manage_options'] )
      || empty( $allcaps['edit_posts'] ) )
        return $allcaps;
    $post = get_post( $args[2] );
    if( 'publish' != $post->post_status )
        return $allcaps;
    if( strtotime( $post->post_date ) < strtotime( '-7 day' ) ) {
        $allcaps[$cap[0]] = false;
    }
    return $allcaps;
}


// 3. Как разрешить пользователям видеть только те медиафайлы в админке, которые они сами и загрузили

	add_filter('parse_query', 'true_hide_attachments' );
 
	function true_hide_attachments( $wp_query ) {
	global $current_user;
 
	if ( !current_user_can('level_10') // администраторов всё так же не трогаем
	   && isset( $wp_query->query_vars['post_type'] ) // защищаемся от Notices :)
	   && $wp_query->query_vars['post_type']=="attachment" ) // тип поста - вложения
		$wp_query->set( 'author', $current_user->data->ID );
	}	

/* показывать только комментарии автора в WordPress*/

add_filter('pre_get_comments','only_author_comments');
function only_author_comments($query){
    global $pagenow;
    if('edit-comments.php' != $pagenow || $query->is_admin)
        return $query;
 
	global $current_user;
	get_currentuserinfo();
	$userId = $current_user->ID;
	if( !is_super_admin( $userId ) ) {
		$query->query_vars['author__in'] = $userId;
        $query->query_vars['user_id'] = $userId;
    }
    return $query;
}	
	
	
	
/*  Удаляем меню в админке для пользователей	*/

function remove_menus(){
	global $user;
if(!current_user_can('administrator') )
{
	remove_menu_page( 'index.php' );                  //Консоль
	//remove_menu_page( 'upload.php' );                 //Медиафайлы
	remove_menu_page( 'edit.php?post_type=page' );    //Страницы
	//remove_menu_page( 'edit-comments.php' );          //Комментарии
	remove_menu_page( 'themes.php' );                 //Внешний вид
	remove_menu_page( 'plugins.php' );                //Плагины
	remove_menu_page( 'users.php' );                  //Пользователи
	remove_menu_page( 'tools.php' );                  //Инструменты
	remove_menu_page( 'options-general.php' );        //Параметры
	remove_menu_page('wpseo_dashboard');
      
}
}
add_action( 'admin_menu', 'remove_menus' );

/* Закрываем констоль полностью */






}

/*=====================САЙТ ДЛЯ КЛИЕНТОВ================================*/




/*=====================SEO ДЛЯ САЙТА================================*/



?>
