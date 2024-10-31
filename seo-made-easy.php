<?php
/*
Plugin Name: SEO Made Easy
Plugin URI: https://veryeasy.io/seo-made-easy
Description: A set and forget solution for WordPress SEO.
Version: 0.4
Requires at least: 5.0
Author: VeryEasy
Author URI: https://veryeasy.io/
License: Public Domain
License URI: https://wikipedia.org/wiki/Public_domain
Text Domain: seo-made-easy
*/

// block direct access to this file
if ( !defined( 'ABSPATH' ) ) {
	http_response_code( 404 );
	die();
}

// custom options for posts
if ( is_admin() ) {
	add_action( 'add_meta_boxes', 'seomadeeasy_meta_box_add' );
	function seomadeeasy_meta_box_add() {
		$post_types = get_post_types( array( 'public' => true ) );
		add_meta_box( 'seomadeeasy_post', 'SEO Made Easy', 'seomadeeasy_post_meta_box', $post_types, 'normal', 'high' );
	}
	function seomadeeasy_post_meta_box( $post ) {
		$values = get_post_custom( $post->ID );
		if ( isset( $values['seomadeeasy_keywords'] ) ) {
			$seomadeeasy_keywords_text = esc_html( $values['seomadeeasy_keywords'][0] );
		}
		if ( isset( $values['seomadeeasy_description'] ) ) {
			$seomadeeasy_description_text = esc_html( $values['seomadeeasy_description'][0] );
		}
		if ( isset( $values['seomadeeasy_canonical'] ) ) {
			$seomadeeasy_canonical_url = esc_url( $values['seomadeeasy_canonical'][0] );
		}
		wp_nonce_field( 'seomadeeasy_meta_box_nonce', 'meta_box_nonce' );
		?>
		<p><input name="seomadeeasy_keywords" type="text" placeholder="<?php _e( 'Enter meta keywords', 'seo-made-easy' ); ?>" class="large-text" value="<?php if ( $seomadeeasy_keywords = get_post_meta( $post->ID, 'seomadeeasy_keywords', true ) ) { echo esc_html( $seomadeeasy_keywords_text ); } ?>" /></p>
		<p><input name="seomadeeasy_description" type="text" placeholder="<?php _e( 'Enter meta description', 'seo-made-easy' ); ?>" class="large-text" value="<?php if ( $seomadeeasy_description = get_post_meta( $post->ID, 'seomadeeasy_description', true ) ) { echo esc_html( $seomadeeasy_description_text ); } ?>" /></p>
		<p><input name="seomadeeasy_canonical" type="url" placeholder="<?php _e( 'Enter canonical URL', 'seo-made-easy' ); ?>" class="large-text" value="<?php if ( $seomadeeasy_canonical = get_post_meta( $post->ID, 'seomadeeasy_canonical', true ) ) { echo esc_url_raw( $seomadeeasy_canonical_url ); } ?>" /></p>
		<p><input name="seomadeeasy_noindex" type="checkbox" value="yes" <?php if ( isset( $values['seomadeeasy_noindex'] ) ) checked( $values['seomadeeasy_noindex'][0], 'yes' ); ?> /> <?php _e( 'Discourage search engines from indexing this', 'seo-made-easy' ); ?></p>
		<?php
	}
	add_action( 'save_post', 'seomadeeasy_meta_box_save' );
	function seomadeeasy_meta_box_save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'seomadeeasy_meta_box_nonce' ) ) return;
		if ( !current_user_can( 'edit_post', $post_id ) ) return;
		if ( isset( $_POST['seomadeeasy_keywords'] ) ) {
			update_post_meta( $post_id, 'seomadeeasy_keywords', sanitize_text_field( wp_unslash( $_POST['seomadeeasy_keywords'] ) ) );
		}
		if ( isset( $_POST['seomadeeasy_description'] ) ) {
			update_post_meta( $post_id, 'seomadeeasy_description', sanitize_text_field( wp_unslash( $_POST['seomadeeasy_description'] ) ) );
		}
		if ( isset( $_POST['seomadeeasy_canonical'] ) ) {
			update_post_meta( $post_id, 'seomadeeasy_canonical', sanitize_text_field( wp_unslash( $_POST['seomadeeasy_canonical'] ) ) );
		}
		if ( isset( $_POST[ 'seomadeeasy_noindex' ] ) ) {
			update_post_meta( $post_id, 'seomadeeasy_noindex', 'yes' );
		} else {
			update_post_meta( $post_id, 'seomadeeasy_noindex', '' );
		}
	}
}

// add SEO, social media, and schema tags
add_action( 'wp_head', 'seomadeeasy', 1, 1 );
function seomadeeasy() {
	global $post;
	$values = get_post_custom( $post->ID );
	if ( isset( $values['seomadeeasy_noindex'] ) ) {
		$seomadeeasy_noindex = esc_html( $values['seomadeeasy_noindex'][0] );
	}
	if ( $seomadeeasy_noindex = get_post_meta( $post->ID, 'seomadeeasy_noindex', true ) ) {
		echo '<meta name="robots" content="noindex" />';
	}
	if ( isset( $values['seomadeeasy_keywords'] ) ) {
		$seomadeeasy_keywords_text = esc_html( $values['seomadeeasy_keywords'][0] );
	}
	if ( $seomadeeasy_keywords = get_post_meta( $post->ID, 'seomadeeasy_keywords', true ) ) {
		echo '<meta name="keywords" content="' . esc_html( $seomadeeasy_keywords_text ) . '" />';
	} else {
		echo '<meta name="keywords" content="' . implode( ', ', wp_get_post_tags( get_the_ID(), array( 'fields' => 'names' ) ) ) . '" />';
	}
	if ( isset( $values['seomadeeasy_description'] ) ) {
		$seomadeeasy_description_text = esc_html( $values['seomadeeasy_description'][0] );
	}
	if ( $seomadeeasy_description = get_post_meta( $post->ID, 'seomadeeasy_description', true ) ) {
		echo '<meta name="description" content="' . esc_html( $seomadeeasy_description_text ) . '" />';
	} else {
		echo '<meta name="description" content="' . ( is_single() ? wp_strip_all_tags( get_the_excerpt(), true ) : get_bloginfo( 'description' ) ) . '" />';
	}
	?>
	<meta property="og:image" content="<?php if ( is_single() && has_post_thumbnail() ) { the_post_thumbnail_url( 'full' ); } elseif ( has_site_icon() ) { echo get_site_icon_url(); } ?>" />
	<meta name="twitter:card" content="photo" />
	<meta name="twitter:site" content="<?php bloginfo( 'name' ); ?>" />
	<meta name="twitter:title" content="<?php if ( is_single() ) { the_title(); } else { bloginfo( 'name' ); } ?>" />
	<meta name="twitter:description" content="<?php if ( is_single() ) { echo wp_strip_all_tags( get_the_excerpt(), true ); } else { bloginfo( 'description' ); } ?>" />
	<meta name="twitter:image" content="<?php if ( is_single() && has_post_thumbnail() ) { the_post_thumbnail_url( 'full' ); } elseif ( has_site_icon() ) { echo get_site_icon_url(); } ?>" />
	<meta name="twitter:url" content="<?php if ( is_single() ) { esc_url( the_permalink() ); } else { echo esc_url( home_url() ) . '/'; } ?>" />
	<meta name="twitter:widgets:theme" content="light" />
	<meta name="twitter:widgets:link-color" content="blue" />
	<meta name="twitter:widgets:border-color" content="#fff" />
	<script type="application/ld+json">
	{
	"@context": "https://www.schema.org/",
	"@type": "WebSite",
	"name": "<?php bloginfo( 'name' ); ?>",
	"url": "<?php echo esc_url( home_url() ); ?>/"
	}
	</script>
	<script type="application/ld+json">
	{
	"@context": "https://www.schema.org/",
	"@type": "Organization",
	"name": "<?php bloginfo( 'name' ); ?>",
	"url": "<?php echo esc_url( home_url() ); ?>/",
	"logo": "<?php if ( has_custom_logo() ) { $custom_logo_id = get_theme_mod( 'custom_logo' ); $logo = wp_get_attachment_image_src( $custom_logo_id , 'full' ); echo esc_url( $logo[0] ); } ?>",
	"image": "<?php if ( has_site_icon() ) { echo get_site_icon_url(); } ?>",
	"description": "<?php bloginfo( 'description' ); ?>"
	}
	</script>
	<?php
	if ( isset( $values['seomadeeasy_canonical'] ) ) {
		$seomadeeasy_canonical_url = esc_url( $values['seomadeeasy_canonical'][0] );
	}
	$protocol    = isset( $_SERVER['HTTPS'] ) ? 'https' : 'http';
	$http_host   = $_SERVER['HTTP_HOST'];
	$request_uri = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
	if ( ( is_singular() ) && ( $seomadeeasy_canonical = get_post_meta( $post->ID, 'seomadeeasy_canonical', true ) ) ) {
		echo '<link rel="canonical" href="' . esc_url_raw( $seomadeeasy_canonical_url ) . '" />';
	} else {
		echo '<link rel="canonical" href="' . esc_url_raw( $protocol . '://' . $http_host ), esc_url( $request_uri ) . '" />';
	}
}

// add primary category option to categories section on the post editor
if ( is_admin() ) {
	add_action( 'edit_form_after_editor', 'seomadeeasy_primary_cat' );
	add_action( 'render_block', 'seomadeeasy_primary_cat' );
	function seomadeeasy_primary_cat( $post ) {
		$values = get_post_custom( $post->ID );
		if ( isset( $values['seomadeeasy_primary'] ) ) {
			$seomadeeasy_primary_cat = esc_html( $values['seomadeeasy_primary'][0] );
		}
		wp_nonce_field( 'seomadeeasy_primary_cat_nonce', 'primary_cat_nonce' );
		?>
		<style>.set-as-primary:after{content:'⇪';color:#0073aa;margin-left:5px}</style>
		<script>
		jQuery(document).ready(function($) {
			$('#categorydiv .inside,.editor-post-taxonomies__hierarchical-terms-list').prepend('<p><input type="text" id="primary-cat" name="seomadeeasy_primary" placeholder="<?php _e( "Primary Category", "seo-made-easy" ); ?>" value="<?php if ( $seomadeeasy_primary = get_post_meta( $post->ID, "seomadeeasy_primary", true ) ) { echo esc_html( $seomadeeasy_primary_cat ); } ?>" /></p>');
			$('#categorydiv .selectit').append('<span title="<?php _e( "Set as primary category", "seo-made-easy" ); ?>" class="set-as-primary"></span>');
			$('#categorydiv .set-as-primary').click(function() {
				var value = $(this).closest('label').text().trim();
				var input = $('#primary-cat');
				input.val(value);
				return false;
			});
		});
		</script>
		<?php
	}
	add_action( 'save_post', 'seomadeeasy_primary_cat_save' );
	function seomadeeasy_primary_cat_save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( !isset( $_POST['primary_cat_nonce'] ) || !wp_verify_nonce( $_POST['primary_cat_nonce'], 'seomadeeasy_primary_cat_nonce' ) ) return;
		if ( !current_user_can( 'edit_post', $post_id ) ) return;
		if ( isset( $_POST['seomadeeasy_primary'] ) ) {
			$seomadeeasy_primary = sanitize_text_field( $_POST['seomadeeasy_primary'] );
			update_post_meta( $post_id, 'seomadeeasy_primary', $seomadeeasy_primary );
		}
	}
}

// replace the default category slug with the primary one when using /%category%/%postname%/
add_filter( 'post_link_category', 'seomadeeasy_link_category', 10, 3 );
function seomadeeasy_link_category( $cat, $cats, $post ) {
	$seomadeeasy_primary_cat = get_post_meta( $post->ID, 'seomadeeasy_primary', true );
	if ( $term = get_term_by( 'name', $seomadeeasy_primary_cat, 'category' ) ) {
		$cat = $term;
	}
	return $cat;
}

// allow shortcodes in text widgets
add_filter( 'widget_text', 'do_shortcode' );

// shortcode to display the primary category
add_shortcode( 'seo-cat', 'seomadeeasy_cat_shortcode' );
function seomadeeasy_cat_shortcode() {
	if ( in_the_loop() ) {
		ob_start();
		global $post;
		if ( $seomadeeasy_primary_cat = get_post_meta( get_the_ID(), 'seomadeeasy_primary', true ) ) {
			echo '<span class="primary-cat">' . esc_html( $seomadeeasy_primary_cat ) . '</span>';
		} elseif ( has_category( 'uncategorized' ) ) {
		} else {
			$category = get_the_category();
			echo '<span class="primary-cat">' . esc_attr( $category[0]->cat_name ) . '</span>';
		}
		$output = ob_get_clean();
		return $output;
	}
}

// shortcode to display breadcrumbs
add_shortcode( 'seo-crumbs', 'seomadeeasy_bread_shortcode' );
function seomadeeasy_bread_shortcode() {
	ob_start();
	global $post;
	if ( !is_home() ) {
		echo '<style>ul#breadcrumbs, ul#breadcrumbs li, ul#breadcrumbs li:before, ul#breadcrumbs li:after{display:inline;content:"";list-style:none;padding:0;margin:0}</style>';
		echo '<ul id="breadcrumbs" itemscope itemtype="https://schema.org/BreadcrumbList"><li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="' . esc_url( home_url() ) . '/" itemprop="item"><span itemprop="name">' . esc_html__( 'Home', 'primary-cat' ) . '</span></a><meta itemprop="position" content="1" /></li> &rarr; ';
		if ( $seomadeeasy_primary_cat = get_post_meta( get_the_ID(), "seomadeeasy_primary", true ) ) {
			echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="' . esc_url( home_url() ) . '/' . esc_html( str_replace( ' ', '-', strtolower( $seomadeeasy_primary_cat ) ) ) . '/" itemprop="item"><span itemprop="name">' . esc_html( $seomadeeasy_primary_cat ) . '</span></a><meta itemprop="position" content="2" /></li>';
		} elseif ( is_single() ) {
			$categories = get_the_category();
			$separator  = ', ';
			$output 	= '';
			if ( ! empty( $categories ) ) {
				foreach( $categories as $category ) {
					$output .= '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="' . esc_url( get_category_link( $category->term_id ) ) . '" itemprop="item"><span itemprop="name">' . esc_attr( $category->name ) . '</span></a><meta itemprop="position" content="2" /></li>' . $separator;
				}
				echo trim( $output, $separator );
			}
		}
		if ( is_single() ) {
			echo ' &rarr; ';
		}
		echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a href="" itemprop="item"><span itemprop="name">';
		remove_all_filters( 'wp_title' );
		wp_title( '' );
		echo '</span></a><meta itemprop="position" content="3" /></li>';
		echo '</ul>';
	}
	$output = ob_get_clean();
	return $output;
}

// shortcode to display share buttons
add_shortcode( 'seo-share', 'seomadeeasy_share' );
function seomadeeasy_share() {
	ob_start();
	echo '<div class="share">';
	echo '<a href="https://www.facebook.com/sharer/sharer.php?t=' . get_the_title() . '&u=' . get_permalink() . '" title="' . esc_attr__( 'Share on Facebook', 'seo-made-easy' ) . '" class="facebook" target="_blank"><span class="icon"><svg viewBox="0 0 24 24" xmlns="https://www.w3.org/2000/svg"><path fill="currentColor" d="m22.676 0h-21.352c-.731 0-1.324.593-1.324 1.324v21.352c0 .732.593 1.324 1.324 1.324h11.494v-9.294h-3.129v-3.621h3.129v-2.675c0-3.099 1.894-4.785 4.659-4.785 1.325 0 2.464.097 2.796.141v3.24h-1.921c-1.5 0-1.792.721-1.792 1.771v2.311h3.584l-.465 3.63h-3.119v9.282h6.115c.733 0 1.325-.592 1.325-1.324v-21.352c0-.731-.592-1.324-1.324-1.324" /></svg></span></a>';
	echo '<a href="https://twitter.com/intent/tweet?text=' . get_the_title() . '&url=' . get_permalink() . '" title="' . esc_attr__( 'Share on Twitter', 'seo-made-easy' ) . '" class="twitter" target="_blank"><span class="icon"><svg viewBox="0 0 24 24" xmlns="https://www.w3.org/2000/svg"><path fill="currentColor" d="m23.954 4.569c-.885.389-1.83.654-2.825.775 1.014-.611 1.794-1.574 2.163-2.723-.951.555-2.005.959-3.127 1.184-.896-.959-2.173-1.559-3.591-1.559-2.717 0-4.92 2.203-4.92 4.917 0 .39.045.765.127 1.124-4.09-.193-7.715-2.157-10.141-5.126-.427.722-.666 1.561-.666 2.475 0 1.71.87 3.213 2.188 4.096-.807-.026-1.566-.248-2.228-.616v.061c0 2.385 1.693 4.374 3.946 4.827-.413.111-.849.171-1.296.171-.314 0-.615-.03-.916-.086.631 1.953 2.445 3.377 4.604 3.417-1.68 1.319-3.809 2.105-6.102 2.105-.39 0-.779-.023-1.17-.067 2.189 1.394 4.768 2.209 7.557 2.209 9.054 0 13.999-7.496 13.999-13.986 0-.209 0-.42-.015-.63.961-.689 1.8-1.56 2.46-2.548z" /></svg></span></a>';
	echo '<a href="mailto:?subject=' . get_the_title() . '&body=' . get_permalink() . '" title="' . esc_attr__( 'Share over Email', 'seo-made-easy' ) . '" class="email" target="_blank"><span class="icon"><svg viewBox="0 0 24 24" xmlns="https://www.w3.org/2000/svg"><path fill="currentColor" d="M21.386 2.614H2.614A2.345 2.345 0 0 0 .279 4.961l-.01 14.078a2.353 2.353 0 0 0 2.346 2.347h18.771a2.354 2.354 0 0 0 2.347-2.347V4.961a2.356 2.356 0 0 0-2.347-2.347zm0 4.694L12 13.174 2.614 7.308V4.961L12 10.827l9.386-5.866v2.347z" /></svg></span></a>';
	echo '<a href="javascript:window.print()" title="' . esc_attr__( 'Print this Article', 'seo-made-easy' ) . '" class="print"><span class="icon"><svg viewbox="0 0 24 24" xmlns="https://www.w3.org/2000/svg"><path fill="currentColor" d="M18,3H6V7H18M19,12A1,1 0 0,1 18,11A1,1 0 0,1 19,10A1,1 0 0,1 20,11A1,1 0 0,1 19,12M16,19H8V14H16M19,8H5A3,3 0 0,0 2,11V17H6V21H18V17H22V11A3,3 0 0,0 19,8Z" /></svg></span></a>';
	echo '</div>';
	echo '<style>.share,.share *{box-sizing:border-box!important;white-space:nowrap!important;-webkit-tap-highlight-color:transparent!important;transition:all .5s ease!important;padding:0!important;border:0!important;margin:0!important}.share{font-size:0!important;margin:30px 0!important}.share a{display:inline-block!important;width:25%!important;min-width:120px!important;font-family:arial!important;font-size:16px!important;color:#fff!important;text-align:center!important;text-decoration:none!important;text-shadow:none!important;line-height:0!important;padding:15px 0!important;background:#000!important;box-shadow:none!important}.share a.facebook{background:#3B5998!important}.share a.twitter{background:#1DA1F2!important}.share a.email{background:#222!important}.share a.print{background:#777!important}.share a:hover{opacity:.8!important}.share .icon{display:inline-block!important;width:20px!important;height:20px!important}.widget-area .share a{min-width:0!important}@media(max-width:576px){.share a{min-width:0!important}}</style>';
	$output = ob_get_clean();
	return $output;
}

// add share buttons to posts
add_filter( 'the_content', 'seomadeeasy_before_after' );
function seomadeeasy_before_after( $content ) {
	if ( is_single() ) {
		$beforecontent = do_shortcode( '[seo-share]' );
		$aftercontent  = do_shortcode( '[seo-share]' );
		$fullcontent   = $beforecontent . $content . $aftercontent;
	} else {
		$fullcontent = $content;
	}
	return $fullcontent;
}

// add sitemap.xml
add_action( 'publish_post', 'seomadeeasy_sitemap' );
add_action( 'publish_page', 'seomadeeasy_sitemap' );
add_action( 'save_post', 'seomadeeasy_sitemap' );
function seomadeeasy_sitemap() {
	$post_types		 = get_post_types( array( 'public' => true ) );
	$postsForSitemap = get_posts( array(
		'numberposts' => -1,
		'orderby' 	  => 'modified',
		'post_type'   => $post_types,
		'order' 	  => 'DESC'
	));
	$sitemap  = '<?xml version="1.0" encoding="UTF-8" ?>';
	$sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
	$sitemap .= '<url><loc>' . get_home_url() . '/</loc><changefreq>daily</changefreq><priority>1.0</priority></url>';
	foreach( $postsForSitemap as $post ) {
		setup_postdata( $post );
		$postdate = explode( " ", $post->post_modified );
		$sitemap .= '<url>' .
		'<loc>' . get_permalink( $post->ID ) . '</loc>' .
		'<lastmod>' . $postdate[0] . '</lastmod>' .
		'<changefreq>monthly</changefreq>' .
		'</url>';
	}
	$sitemap .= '</urlset>';
	$fp = fopen( ABSPATH . 'sitemap.xml', 'w' );
	fwrite( $fp, $sitemap );
	fclose( $fp );
}