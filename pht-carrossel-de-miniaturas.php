<?php
/**
 * @package WordPress
 * @subpackage PhT Carrossel de Miniaturas
 *
 * Função para inserir carrossel de miniaturas de imagens em um site/blog WordPress, via Shortcode, utlizando o plugin JQuery JCarousel.
 * Documentação do plugin JCarousel: https://sorgalla.com/jcarousel/docs/
 *
 */

/** Inicia opções **/
function pht_theme_options_init(){
	register_setting( 'pht_options', 'pht_theme_options' );
}
add_action( 'admin_init', 'pht_theme_options_init' );

/** Menu no Painel de Controle**/
function pht_theme_options_add_page() {

	// Adciona menu
	add_menu_page(
		__( 'Carrossel de Miniaturas' ),
		__( 'Carrossel de Miniaturas' ),
		'edit_theme_options',
		'pht_theme_options',
		'pht_theme_options_do_page',
		get_stylesheet_directory_uri() . '/images/pht-icon.gif', // The menu icon
		28 // Posição do menu
	);

	// Adciona submenu
	add_submenu_page( 
		__( 'pht_theme_options' ),
		__( 'Adicionar Miniaturas' ),
		__( 'Adicionar' ),
		'edit_theme_options',
		'pht_adicionar',
		'pht_theme_adicionar'
	);

}
add_action( 'admin_menu', 'pht_theme_options_add_page' );

/** Tela de administração do Carrossel de Miniaturas **/
function pht_theme_options_do_page() { ?>
	<div class="wrap">
		<h2>Carrossel de Miniaturas</h2>
		<div class="metabox-holder mbleft"> 
			<div class="postbox"> 
				<h3><?php _e( 'Miniaturas', 'PhT Carrossel de Miniaturas' ); ?></h3>
			</div>		
		</div>  
	</div>
	<?php
}

/** Adicionar "Custom Post Type" para Carrossel de Miniaturas **/
function pht_register_miniatura_post_type() {
 
	$labels = array(
		'name' => __( 'Carrossel de Miniaturas' ),
		'singular_name' => __( 'Carrossel de Miniaturas' ),
		'add_new' => _x( 'Adicionar Nova', 'Carrossel de Miniaturas' ),
		'add_new_item' => __( 'Adicionar Nova Miniatura' ),
		'edit_item' => __( 'Editar Carrossel de Miniaturas' ),
		'new_item' => __( 'Nova Miniatura' ),
		'view_item' => __( 'Ver Miniaturas' ),
		'search_items' => __( 'Buscar Miniaturas' ),
		'not_found' =>  __( 'Não encontrado' ),
		'not_found_in_trash' => __( 'Não encontrado na Lixeira' ),
		'parent_item_colon' => 'Parent Carrossel de Miniaturas'
	);
 
	$args = array(
		'labels' => $labels,
		'description' => 'Imagens exibidas no Carrossel de Miniaturas',
		'show_in_menu' => true,
		'menu_position' => 30,
		'menu_icon' => '/images/pht-icon.gif',
		'capability' => 'post',
		'map_meta_cap' => true,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_nav_menus' => false,
		'has_archive' => true,
		'query_var' => true,
		'rewrite' => true,
		'hierarchical' => false,
		'supports' => array( 'thumbnail' )
	  ); 
 
	register_post_type( 'miniatura' , $args );
}
/* Adicionar a nossa função para o "hook" de inicialização. */
add_action('init', 'pht_register_miniatura_post_type');

/** Tela de publicação das Miniaturas **/
function pht_miniatura_post_metaboxes( $post ) {
    global $wp_meta_boxes;

    remove_meta_box('postimagediv', 'miniatura', 'side');
    add_meta_box('postimagediv', __('Miniatura'), 'post_thumbnail_meta_box', 'miniatura', 'normal', 'high');
}
add_action( 'add_meta_boxes_miniatura', 'pht_miniatura_post_metaboxes' );

/** Adiciona imagem no Carrossel de Miniaturas **/
function pht_miniatura_adicionar() {

	echo '<div class="wrap">';
		screen_icon(); echo '<h2>Adicionar Miniaturas</h2>'; ?>
		<form method="post" action="admin.php?page=pht_adicionar_miniatura" enctype="multipart/form-data">
			<input type="hidden" name="MAX_FILE_SIZE" value="3000000" />
			
			<p><strong>Selecione a imagem</strong></p>
			<input type="file" name="foto" />
			<input type="submit" name="Enviar" />
		</form>

		<?php
		
		if( $_FILES ) { // Verificando se existe o envio de arquivos.
			
			if( $_FILES['foto'] ) { // Verifica se o campo não está vazio.

				// Verifica se o tamanho da imagem é maior que o tamanho permitido
				$tamanho = 1024 * 1024 * 2;
				if($_FILES['foto']['size'] > $tamanho) { 
					echo "<p><b>A imagem deve ter no máximo " . $tamanho . " bytes</b></p>";
					die;
				}
				// Verifica se o arquivo é uma imagem
				if(!preg_match("/^image\/(pjpeg|jpeg|png|gif|bmp)$/", $_FILES['foto']['type'])){
					echo "<p><b>Este arquivo não é uma imagem.</b></p>\n";
					die;
				}
				// Se não houver nenhum erro
				if (count($error) == 0) {
					// Pega extensão da imagem
					preg_match("/\.(gif|bmp|png|jpg|jpeg){1}$/i", $_FILES['foto']['name'], $ext);
					// Faz o upload da imagem para seu respectivo caminho
					if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
					$uploadedfile = $_FILES['foto'];
					$upload_overrides = array( 'test_form' => false );
					add_filter('upload_dir', 'my_upload_dir');
					$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
					remove_filter('upload_dir', 'my_upload_dir');

					if ( $movefile ) {
					    echo "<p><b>Imagem enviada com sucesso.</b></p>\n";
					    echo $uploadedfile;
					} else {
					    echo "<p><b>Possível ataque de upload!</b></p>\n";
					}
				}
			}

		}
	echo '</div>';
}

/** Salva imagem na pasta selecionada **/
function my_upload_dir($upload_dir) {

  $upload_dir['subdir'] = '/miniaturas' . $upload_dir['subdir'];
  $upload_dir['path']   = $upload_dir['basedir'] . $upload_dir['subdir'];
  $upload_dir['url']    = $upload_dir['baseurl'] . $upload_dir['subdir'];
  return $upload_dir;

}

/** Modificar colunas exibidas ao visualizar Miniaturas publicadas **/
function pht_miniatura_posts_edit_columns( $posts_columns ) {

    $tmp = array();

    foreach( $posts_columns as $key => $value ) {
        if( $key == 'title' ) {
            $tmp['miniatura'] = 'Carrossel de Miniaturas';
        } else {
            $tmp[$key] = $value;
        }
    }

    return $tmp;
}
add_filter( 'manage_miniatura_posts_columns', 'pht_miniatura_posts_edit_columns' );

/** Exibir tabela de Miniaturas publicadas. **/
function pht_miniatura_custom_column( $column_name ) {
    global $post;

    if( $column_name == 'miniatura' ) {
        echo "<a href='", get_edit_post_link( $post->ID ), "'>", get_the_post_thumbnail( $post->ID, 'medium' ), "</a>";
        echo "<div class=\"row-actions\">";
        echo "</div>";
    }
}
add_action( 'manage_posts_custom_column', 'pht_miniatura_custom_column' );

/** Carrega o CSS e o JS **/
function pht_miniatura_scripts() {
        wp_enqueue_style( 'pht_miniatura_css', '/css/pht-carrossel-de-miniaturas.css', false, '0.2.0' );
				wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'custom_pht_miniatura_js', '/scripts/jquery.jacarousel.min.js', false, '0.3.7' );
        wp_enqueue_script( 'custom_pht_miniatura_js', '/scripts/jcarousel.responsive.js', false, '0.3.7' );
}
add_action( 'wp_enqueue_scripts', 'pht_miniatura_scripts' );

/** Shortcode do HTML das Miniaturas **/
function pht_miniatura_shortcode () { ?>
		<div class="carrossel">
			<div class="jcarousel-wrapper">
	            <div class="jcarousel">
	                <ul>
						<?php
						$miniaturas_args = array(
							'numberposts' => -1,
							'post_type' => 'cartaz',
							'post_parent' => 0
						);
						$miniaturas = get_posts( $miniaturas_args );
						foreach ( $miniaturas as $image ) {
							$image_atts = array(
								'alt'	=> '',
								'title'	=> '',
							);							
							$img = get_the_post_thumbnail( $image->ID, array(300, 300), $image_atts );
							$url = wp_get_attachment_url( get_post_thumbnail_id($image->ID) );							
							echo '<li><a class="add-bottom" data-rel="lightbox-0" href="'.esc_url($url).'" target="_blank"><div id="lightbox" class="img-thumbnail img-hover">'.$img.'</div></li></a>';
						} ?>
	                </ul>
	            </div>

	            <a href="#" class="jcarousel-control-prev"><div class="seta_esq">&nbsp;</div></a>
	            <a href="#" class="jcarousel-control-next"><div class="seta_dir">&nbsp;</div></a>
	        </div>
		</div>
<?php

add_shortcode ( 'pht_miniatura', 'pht_miniatura_shortcode');

?>