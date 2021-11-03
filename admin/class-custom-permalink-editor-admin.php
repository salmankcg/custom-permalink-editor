<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Custom_Permalink_Editor_Admin {

	public function __construct() {
		
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		
	}

	public function admin_menu() {
		add_menu_page(
			'Custom Permalink Editor',
			'Custom Permalink Editor',
			'cp_editor_view_post_permalinks',
			'cp-editor',
			array( $this, 'post_permalinks_page' ),
			'dashicons-admin-links'
		);
		$post_permalinks_hook     = add_submenu_page(
			'cp-editor',
			'Post Types Permalinks',
			'Post Types Permalinks',
			'cp_editor_view_post_permalinks',
			'cp-editor',
			array( $this, 'post_permalinks_page' )
		);
		
		
	}

	public function wp_pm_load_style() {
		wp_enqueue_style(
			'custom-permalink-editor-about-style',
			plugins_url(
				'/assets/css/cp-editor.min.css',
				CP_EDITOR_FILE
			),
			array(),
			CP_EDITOR_VERSION
		);
	}

	
	public  function wp_pm_admin_content(){
		$content = '<div class="wrap">
			<h1 class="wp-heading-inline">
				'.__('Thank you for installing Custom Permalink Editor','custom-permalink-editor').' '.CP_EDITOR_VERSION.'
			</h1>
			
			<hr>
				<div class="kcg_admin_parent_container">
					<div class="kcg_admin_container">
						<h2>'.__('Plugin Interface','custom-permalink-editor').'</h2>
						<p>
						'.__('You can create/edit URL by adding /, - or both at the same time. <br/> Here is a demo screenshot of Custom Permalink Editor.','custom-permalink-editor').'
						</p>
						<img src="'. plugin_dir_url( dirname( __FILE__ ) ).'assets/images/permalink-manager.png'.'" class="plugin_image" alt="">
					</div>
					<div class="kcg_admin_container">
						<h2>
						'.__('Uses Guide','custom-permalink-editor').'
						</h2>
						<p>
							<b>
							'.__('You can change post permalink by the following steps:','custom-permalink-editor').'
							</b>
						</p>
						<ul>
							<li>- '.__('Edit your posts/pages and create SEO friendly custom URL.','custom-permalink-editor').'</li>
							<li>- '.__('In the permalink box insert your desired permalink and update the post.','custom-permalink-editor').'</li>
							<li>- '.__('Preview your post and see the post URL is changed.','custom-permalink-editor').'</li>
							<li>- '.__('If you want to revert to the Wordpress default URL system, just deactivate the plugin.','custom-permalink-editor').'</li>
						</ul>
					</div>
				</div>
			</div>';

			$allowed_html = $this->custom_permalink_allowed_tags();
			echo wp_kses( $content, $allowed_html );
	}

	public function custom_permalink_allowed_tags(){
		$allowed_tags = array(
			'a' => array(
				'class' => array(),
				'href'  => array(),
				'rel'   => array(),
				'title' => array(),
			),
			
			'div' => array(
				'class' => array(),
				'id' => array(),
				'title' => array(),
				'style' => array(),
			),
			'span' => array(
				'id' => true,
				'class' => true,
				'type' => true,
				'value' => true,
				'style' => true,
			),
			'li' => array(),
			'em' => array(),
			'h1' => array(),
			'h2' => array(),
			'h3' => array(),
			'h4' => array(),
			'h5' => array(),
			'h6' => array(),
			'i' => array(),
			'img' => array(
				'alt'    => array(),
				'class'  => array(),
				'height' => array(),
				'src'    => array(),
				'width'  => array(),
			),
			'li' => array(
				'class' => array(),
			),
			'ol' => array(
				'class' => array(),
			),
			'p' => array(
				'class' => array(),
			),
			'q' => array(
				'cite' => array(),
				'title' => array(),
			),
			'span' => array(
				'class' => array(),
				'title' => array(),
				'style' => array(),
			),
			'strike' => array(),
			'strong' => array(),
			'ul' => array(
				'class' => array(),
			),
		);
		
		return $allowed_tags;
	}

	public function post_permalinks_page() {
		$this->wp_pm_load_style();
		$this->wp_pm_admin_content();
		
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 5 );
		
	}

	public function admin_footer_text() {
		$wp_pm_footer_text = __( 'Custom Permalink Editor version', 'custom-permalink-editor' ) .
		' ' . CP_EDITOR_VERSION . ' ' .
		__( 'by', 'custom-permalink-editor' ) .
		' <a href="https://kingscrestglobal.com/" target="_blank">' .
			__( 'Team KCG', 'custom-permalink-editor' ) .
		'</a>' .
		' - ' .
		'Visit Us:' .
		' <a href="https://kingscrestglobal.com/" target="_blank">' .
			__( 'Kings Crest Global', 'custom-permalink-editor' ) .
		'</a>';

		return $wp_pm_footer_text;
	}
	
}

new Custom_Permalink_Editor_Admin();


