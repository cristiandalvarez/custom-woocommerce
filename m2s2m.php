<?php
/*
Plugin Name:  Mautic to s2member (m2s2m)
Plugin URI:   https://developer.wordpress.org/plugins/the-basics/
Description:  Connects Mautic with s2Member membership plugin
Version:      1
Author:       WordPress.org
Author URI:   https://developer.wordpress.org/
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wporg
Domain Path:  /languages
*/

require plugin_dir_path( __FILE__ ) . 'm2s2m-ccaps-mapping.php';
require plugin_dir_path( __FILE__ ) . 'mautic-auth.php';
//include_once(ABSPATH . 'wp-includes/pluggable.php'); // para que funcione is_user_logged_in()


/* 
 * Crea menus de administración
 */
add_action('admin_menu', 'm2s2m_admin_pages');
function m2s2m_admin_pages() {
    add_menu_page(
        'Mautic to s2member', 			//page_title
        'Mautic to s2member',			//menu_title
        'manage_options', 				//capability
        'm2s2m-admin', 					//menu_slug
        'm2s2m_admin_page_callback', 	//function
        ''
    );
    add_submenu_page( 
      	'm2s2m-admin', 					// $parent_slug	
      	'Tag to CCap Mapping', 			// $page_title
      	'TAG to CCap Mapping', 			// $menu_title
      	'manage_options', 				// $capability
      	'ccaps-to-TAG-Mapping', 		// $menu_slug
      	'm2s2m_tag_mapping_plugin_options'// $function
      );
}

/*
 * Captura POST para actualizar:
 * Subscriber tag name
 * Blocked tag name
 * Membersip prefix tag 
 */
 function m2s2m_admin_page_callback() {
 	if(isset($_POST['set_mautic_subscriber_tag_name'])){
		m2s2m_set_subscriber_tag_name($_POST['mautic_subscriber_tag_name']);
	} 	
 	if(isset($_POST['set_mautic_blocked_tag_name'])){
		m2s2m_set_blocked_tag_name($_POST['mautic_blocked_tag_name']);
	}
 	if(isset($_POST['set_mautic_memberlevel_tag_prefix'])){
		m2s2m_set_mautic_memberlevel_tag_prefix($_POST['mautic_memberlevel_tag_prefix']);
	}
    ?>
    <div class="wrap">
		<h1>Mautic to S2member Settings</h1>
		<section class="contacts">
			<form method="post" action="">
				<table>
					<tr>
						<td>Mautic subscriber tag name:</td>
						<td>
							<input type="text" id="mautic_subscriber_tag_name" name="mautic_subscriber_tag_name" value="<?php echo get_option('mautic_subscriber_tag_name');  ?>">								
						</td>
						<td>
							<?php submit_button( 'Guardar', 'small','set_mautic_subscriber_tag_name' ); ?>
						</td>
						<td>Only users with this Mautic tag are allowed to login to the site</td>
					</tr>
				</table>				
			</form>
		</section>		
		<section class="contacts">
			<form method="post" action="">
				<table>
					<tr>
						<td>Mautic blocked tag name:</td>
						<td>
							<input type="text" id="mautic_blocked_tag_name" name="mautic_blocked_tag_name" value="<?php echo get_option('mautic_blocked_tag_name');  ?>">								
						</td>
						<td>
							<?php submit_button( 'Guardar', 'small','set_mautic_blocked_tag_name' ); ?>
						</td>
						<td>
						Users with this tag, will not be able to login to the site.
					</td>
					</tr>
				</table>				
			</form>
		</section>
		<section class="contacts">
			<form method="post" action="">
				<table>
					<tr>
						<td>Mautic membership level tag prefix:</td>
						<td>
							<input type="text" id="mautic_memberlevel_tag_prefix" name="mautic_memberlevel_tag_prefix" value="<?php echo get_option('mautic_memberlevel_tag_prefix');  ?>">								
						</td>
						<td>
							<?php submit_button( 'Salvar', 'small','set_mautic_memberlevel_tag_prefix' ); ?>
						</td>
						<td>
						Crear tags para los niveles de s2member, para poder usar varios sitios con una misma instancia de mautic, agrega el siguiente prefijo a los tags que usará este sitio ex: sitioa_s2member_level1, sitioa_s2member_level2, sitioa_s2member_level3, sitioa_s2member_level4, sitioa_s2member_level5.
					</td>
					</tr>
				</table>				
			</form>
		</section>		
	</div>
    <?php
 }

/*
 * Actualiza Mautic subscriber tag name.
 */
function m2s2m_set_subscriber_tag_name($mautic_subscriber_tag_name){
	update_option( 'mautic_subscriber_tag_name', $mautic_subscriber_tag_name );
}

/*
 * Actualiza Mautic blocked tag name.
 */
function m2s2m_set_blocked_tag_name($mautic_blocked_tag_name){
	update_option( 'mautic_blocked_tag_name', $mautic_blocked_tag_name );
}

/*
 * Actualiza prefijo para tags de s2member levels en mautic.
 */
function m2s2m_set_mautic_memberlevel_tag_prefix($mautic_memberlevel_tag_prefix){
	update_option( 'mautic_memberlevel_tag_prefix', $mautic_memberlevel_tag_prefix );
}

function m2s2m_message_info($message){
	?>
	<div class="notice notice-success is-dismissible"><p><?php _e($message); ?>.</p></div>
	<?php
}


// Realiza las tareas necesarias cuando el usuario inicia sesión
function m2s2m_login_tasks ($user_login, $user){
	$user = new WP_User($user->id);
	$roles = $user->roles;
	if (!in_array('administrator', $roles)) {
		m2s2m_sync_tags_login ($user_login, $user);
		m2s2m_assign_ccap_from_tag_mapping ($user_login, $user); // asigna ccaps de tags
		m2s2m_logout_blocked_user ($user_login, $user);
		m2s2m_only_allow_subscriber_login ($user_login, $user);
		m2s2m_updates_memberlevel_from_tags ($user_login, $user);
		//m2s2m_learndash_login_course_access_manager ( $user_login, $user ); //Asigna o remueve acceso a los cursos, siempre debe ir después de as2_updates_memberlevel_from_tags
		//vb_update_stats_dashboard ($user_login, $user);
	}	
}
add_action('wp_login', 'm2s2m_login_tasks', 10, 2);

/*
 * Salva los tags del usuario en user_options
 */
function m2s2m_sync_tags_login ($user_login, $user){
	if ( !is_admin() ) {
		$contact_tags = m2s2m_retrieve_contact_obj ($user_login, $user);
		update_user_option(	$user->id , 'mautic_user_tags', $contact_tags );
	}
}
/*
 * Retorna el array de tags de Mautic
 */
function m2s2m_retrieve_contact_obj ($user_login, $user){
	//$user = wp_get_current_user();
	//$user_login = $user->user_login;

	$mautic_api_auth = mautic_api_auth ();
	$api = $mautic_api_auth[0];
	$auth = $mautic_api_auth[1];
	$apiUrl = $mautic_api_auth[2];
	$tagApi = $api->newApi("tags", $auth, $apiUrl);
	$contactApi = $api->newApi('contacts', $auth, $apiUrl);
	$searchFilter = $user_login;
	//Prepara arreglo de tags para ser guardado en wordpress en m2s2m_sync_tags_login()
	$contacts = $contactApi->getList($searchFilter,'',1);
	$pre_contact_data = $contacts['contacts']; //Tomamos sub array
	$contact_data = array_values($pre_contact_data); //Reseteamos el array porque usa el ID del usuario de Mautic
	foreach ($contact_data[0]['tags'] as $tag) {
		$tags[] = $tag['tag'];
	}
	return $tags;
}

/*
 * Si el usuario tiene un tag que también está en un mapeo de ccap
 * le asigna ese ccap al usuario
 */
function m2s2m_assign_ccap_from_tag_mapping ($user_login, $user) {
	$tag_to_ccap_mapping = get_option( 'tag_to_ccap_mapping' );
	$user_tag_arr = get_user_option( 'mautic_user_tags', $user->id );
	// Removes all ccaps
	$user->remove_all_caps();
	foreach ($user_tag_arr as $tag) {
		if (in_array($tag, $tag_to_ccap_mapping)) {
			$user = new WP_User($user->id);
			$user->add_cap("access_s2member_ccap_".$tag);
		}
	}
}

/**
 * Si el ususario tiene el tag de bloqueo, no le permite iniciar sesión
 */
function m2s2m_logout_blocked_user ($user_login, $user){
	$mautic_blocked_tag_name = get_option('mautic_blocked_tag_name');
	$local_user_tags = get_user_option( 'mautic_user_tags', $user->id );
	if (in_array($mautic_blocked_tag_name, $local_user_tags)) {
		wp_logout();
	}
}

/**
 * Verifica si el usuario tiene tag de subscriber,
 * de lo contrario no le permite ingresar
 */
function m2s2m_only_allow_subscriber_login ($user_login, $user){
	$subscriber_tag_name = get_option('mautic_subscriber_tag_name');
	$local_user_tags = get_user_option( 'mautic_user_tags', $user->id );
	if (!in_array($subscriber_tag_name, $local_user_tags)) {
		wp_logout();
	}
}

/**
 * Updates s2membership level from CRM tags
 */
function m2s2m_updates_memberlevel_from_tags ($user_login, $user){
	$user = new WP_User($user->ID);
	$local_user_tags = get_user_option( 'mautic_user_tags', $user->id );
	$tag_prefix = get_option('mautic_memberlevel_tag_prefix');
	$s2member_level0 = $tag_prefix.'s2member_level0';
	$s2member_level1 = $tag_prefix.'s2member_level1';
	$s2member_level2 = $tag_prefix.'s2member_level2';
	$s2member_level3 = $tag_prefix.'s2member_level3';
	$s2member_level4 = $tag_prefix.'s2member_level4';
	$s2member_level5 = $tag_prefix.'s2member_level5';

	switch ($local_user_tags) {
		case (in_array($s2member_level5, $local_user_tags)):
			$user->set_role('s2member_level5');
			break;
		case (in_array($s2member_level4, $local_user_tags)):
			$user->set_role('s2member_level4');
			break;
		case (in_array($s2member_level3, $local_user_tags)):
			$user->set_role('s2member_level3');
			break;
		case (in_array($s2member_level2, $local_user_tags)):
			$user->set_role('s2member_level2');
			break;	
		case (in_array($s2member_level1, $local_user_tags)):
			$user->set_role('s2member_level1');
			break;
		case (in_array($s2member_level0, $local_user_tags)):
			$user->set_role('s2member_level0');
			break;										
		default:
			$user->set_role('s2member_level0');
			break;
	}
}