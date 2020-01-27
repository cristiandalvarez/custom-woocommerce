<?php 
    function m2s2m_tag_mapping_plugin_options(){
        if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	if(isset($_POST['add_caps_map'])){
		m2s2m_save_map_caps($_POST['tag_field']);
	}
?>
	<div class="wrap">
	<h1>Mautic Tags to S2member CCaps Mapping</h1>
		<?php
			m2s2m_delete_tag_to_ccaps(); // Verifica si POST indica borrar CCap
			$crm_tags_arr = m2s2m_retrieve_crm_tags ();
			$tag_options="";
			$tag_array;
			// Creates usable array from Mautic Tags
			foreach ($crm_tags_arr['tags'] as $tag_obj) {
			  	$tag_array[] = $tag_obj['tag'];
			}

			echo "<h3>Active CCap to Tag mappings</h3>";
			m2s2m_get_maps_caps($tag_array);
			$tag_to_ccap_mapping = get_option( 'tag_to_ccap_mapping' );
	
			if ($tag_to_ccap_mapping == false) {
				// Si no se ha creado ningún mapeo, tag_to_ccap_mapping está vacío, en este caso se debe usar la lista completa de tags para generar la lista de opciones de tags.
				foreach ($tag_array as $tag) {
					$tag_options .= '<option value="'.$tag.'">'.$tag.'</option>';
				}

			} else {
				// Si ya existe un array en tag_to_ccap_mapping, entonces se comparan y se remueven los mapeos existentes para que no se puedan crear duplicados.
				$filtered_tags = array_diff($tag_array, $tag_to_ccap_mapping);
				// Removes already used tags from list to avoid using the same tag twice
				foreach ($filtered_tags as $ftag) {
					$tag_options .= '<option value="'.$ftag.'">'.$ftag.'</option>';
				}
			}
		?>
	<section class="tags_mapping">
		<h3>Create New Mapping for Caps - Tags</h3>
			<form method="post" action="">
				<table>
					<tr>
						<td>Mautic Tag to acitvate for use as s2menber CCap:</th>
						<td>
							<select id="tag_field" name="tag_field">
								<?php
									echo $tag_options;
								?>
							</select>
						</td>
					</tr>
				</table>
				<?php submit_button( 'Save', 'primary','add_caps_map' );?>
			</form>
	</section>
	</div>
<?php
}

/*
 * Salva un nuevo mapeo de Tag a CCap:
 */
function m2s2m_save_map_caps($tag){
	$tag_to_ccap_mapping = get_option( 'tag_to_ccap_mapping' );
	if ($tag_to_ccap_mapping) { // Verifica si no hay nada en la base de datos
		// Si ya hay valores en la base de datos
		if ( in_array($tag, $tag_to_ccap_mapping) ) {//check if ccap map already exists
			// Si el mapeo ya existe
			m2s2m_message_info('El tag ya está asociado, no se realizaron cambios');
		}else{	
			// Si el mapeo no existe, lo agregamos al array	
			array_push($tag_to_ccap_mapping, $tag);
		}
	}else{
		// Si no hay nada en base de datos, crea el areglo
		$tag_to_ccap_mapping =  array($tag);
	}
	update_option( 'tag_to_ccap_mapping', $tag_to_ccap_mapping );
}

/*
 * Procesa el POST de borrar Tags to CCaps
 */
function m2s2m_delete_tag_to_ccaps(){
	if (isset($_POST)) {
		if (isset($_POST['delete_role_tag'])) {
			$tag_to_ccap_mapping = get_option( 'tag_to_ccap_mapping' );
			$tag = $_POST['tag'];
			if (($key = array_search($tag, $tag_to_ccap_mapping)) !== false) {
				unset($tag_to_ccap_mapping[$key]);
				m2s2m_message_info('El Mapeo de Tag a CCap ha sido eliminado exitosamente');
				update_option('tag_to_ccap_mapping', $tag_to_ccap_mapping);
			}
		}
	}
}

/*
 * Devuelve un arr con los tags de Mautic
 */
function m2s2m_retrieve_crm_tags (){
	$mautic_api_auth = mautic_api_auth ();
	$api = $mautic_api_auth[0];
	$auth = $mautic_api_auth[1];
	$apiUrl = $mautic_api_auth[2];
	$tagApi = $api->newApi("tags", $auth, $apiUrl);
	$mautic_tags_arr = $tagApi->getList('','' ,'' ,'' ,'' , 'publishedOnly','' );
	return $mautic_tags_arr;
}

/*
 * Muestra el mapeo actual de tags a ccaps y permite borrar
 * los mapeos existentes
 */
function m2s2m_get_maps_caps($tag_array){
	$tag_to_ccap_mapping = get_option( 'tag_to_ccap_mapping' );
	if ($tag_to_ccap_mapping) {
		echo '<section class="role_caps_tag">';
		echo '<table class="widefat fixed" cellspacing="0">';
		echo '<tbody>';
		echo "<tr><th><b>Mautic Tag to CCap</b></th></tr>";
		foreach ($tag_to_ccap_mapping as $tag) {
			?>
				<form method="post" action="">
					<input type="hidden" id="tag" name="tag" value="<?php echo $tag; ?>">
						<tr class="alternate">
							<td><?php echo $tag; ?></td>
							<td class="right">
								<?php submit_button( 'Delete Mapping', 'primary','delete_role_tag' ); ?>
							</td>
						</tr>
				</form>
			<?php
		}
		echo '</tbody>';
		echo '</table>';
		echo '</section>';

	}
	
}