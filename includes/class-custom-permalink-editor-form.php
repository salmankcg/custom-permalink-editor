<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Custom_Permalink_Editor_Form {

	private $js_file_suffix = '.min.js';

	private $permalink_metabox = 0;

	public function init() {
		
		$this->js_file_suffix = '.min.js';

		add_action( 'add_meta_boxes', array( $this, 'permalink_edit_box' ) );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 3 );
		add_action( 'delete_post', array( $this, 'delete_permalink' ), 10 );
		add_action( 'category_add_form', array( $this, 'term_options' ) );
		add_action( 'category_edit_form', array( $this, 'term_options' ) );
		add_action( 'post_tag_add_form', array( $this, 'term_options' ) );
		add_action( 'post_tag_edit_form', array( $this, 'term_options' ) );
		add_action( 'created_term', array( $this, 'save_term' ), 10, 3 );
		add_action( 'edited_term', array( $this, 'save_term' ), 10, 3 );
		add_action( 'delete_term', array( $this, 'delete_term_permalink' ), 10, 3 );
		add_action( 'rest_api_init', array( $this, 'rest_edit_form' ) );
		add_action(
			'update_option_page_on_front',
			array( $this, 'static_homepage' ),
			10,
			2
		);

		add_filter(
			'get_sample_permalink_html',
			array( $this, 'sample_permalink_html' ),
			10,
			2
		);
		add_filter( 'is_protected_meta', array( $this, 'protect_meta' ), 10, 2 );
	}


	private function exclude_permalink_manager( $post ) {
		$args               = array(
			'public' => true,
		);
		$exclude_post_types = apply_filters(
			'cp_editor_exclude_post_type',
			$post->post_type
		);

		$exclude_posts     = apply_filters(
			'cp_editor_exclude_posts',
			$post
		);
		$public_post_types = get_post_types( $args, 'objects' );

		if ( isset( $this->permalink_metabox ) && 1 === $this->permalink_metabox ) {
			$check_availability = true;
		} elseif ( 'attachment' === $post->post_type ) {
			$check_availability = true;
		} elseif ( intval( get_option( 'page_on_front' ) ) === $post->ID ) {
			$check_availability = true;
		} elseif ( ! isset( $public_post_types[ $post->post_type ] ) ) {
			$check_availability = true;
		} elseif ( '__true' === $exclude_post_types ) {
			$check_availability = true;
		} elseif ( is_bool( $exclude_posts ) && $exclude_posts ) {
			$check_availability = true;
		} else {
			$check_availability = false;
		}

		return $check_availability;
	}


	public function permalink_edit_box() {
		add_meta_box(
			'custom-permalink-editor-edit-box',
			__( 'Custom Permalink Editor', 'custom-permalink-editor' ),
			array( $this, 'meta_edit_form' ),
			null,
			'normal',
			'high',
			array(
				'__back_compat_meta_box' => false,
			)
		);
	}

	
	public function protect_meta( $protected, $meta_key ) {
		if ( 'cp_editor' === $meta_key ) {
			$protected = true;
		}

		return $protected;
	}

	
	private function sanitize_permalink( $permalink, $language_code ) {
		
		$check_accents_filter = apply_filters( 'cp_editor_allow_accents', false );

	
		$check_caps_filter = apply_filters( 'cp_editor_allow_caps', false );

		$allow_accents = false;
		$allow_caps    = false;

		if ( is_bool( $check_accents_filter ) && $check_accents_filter ) {
			$allow_accents = $check_accents_filter;
		}

		if ( is_bool( $check_caps_filter ) && $check_caps_filter ) {
			$allow_caps = $check_caps_filter;
		}

		if ( ! $allow_accents ) {
			$permalink = remove_accents( $permalink );
		}

		if ( empty( $language_code ) ) {
			$language_code = get_locale();
		}

		$permalink = wp_strip_all_tags( $permalink );
		// Preserve escaped octets.
		$permalink = preg_replace( '|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $permalink );
		// Remove percent signs that are not part of an octet.
		$permalink = str_replace( '%', '', $permalink );
		// Restore octets.
		$permalink = preg_replace( '|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $permalink );

		if ( 'en' === $language_code || strpos( $language_code, 'en_' ) === 0 ) {
			if ( seems_utf8( $permalink ) ) {
				if ( ! $allow_accents ) {
					if ( function_exists( 'mb_strtolower' ) ) {
						if ( ! $allow_caps ) {
							$permalink = mb_strtolower( $permalink, 'UTF-8' );
						}
					}
					$permalink = utf8_uri_encode( $permalink );
				}
			}
		}

		if ( ! $allow_caps ) {
			$permalink = strtolower( $permalink );
		}

		// Convert &nbsp, &ndash, and &mdash to hyphens.
		$permalink = str_replace( array( '%c2%a0', '%e2%80%93', '%e2%80%94' ), '-', $permalink );
		// Convert &nbsp, &ndash, and &mdash HTML entities to hyphens.
		$permalink = str_replace( array( '&nbsp;', '&#160;', '&ndash;', '&#8211;', '&mdash;', '&#8212;' ), '-', $permalink );

		// Strip these characters entirely.
		$permalink = str_replace(
			array(
				// Soft hyphens.
				'%c2%ad',
				// &iexcl and &iquest.
				'%c2%a1',
				'%c2%bf',
				// Angle quotes.
				'%c2%ab',
				'%c2%bb',
				'%e2%80%b9',
				'%e2%80%ba',
				// Curly quotes.
				'%e2%80%98',
				'%e2%80%99',
				'%e2%80%9c',
				'%e2%80%9d',
				'%e2%80%9a',
				'%e2%80%9b',
				'%e2%80%9e',
				'%e2%80%9f',
				// Bullet.
				'%e2%80%a2',
				// Copy, &reg, &deg, HORIZONTAL ELLIPSIS, and &trade.
				'%c2%a9',
				'%c2%ae',
				'%c2%b0',
				'%e2%80%a6',
				'%e2%84%a2',
				// Acute accents.
				'%c2%b4',
				'%cb%8a',
				'%cc%81',
				'%cd%81',
				// Grave accent, macron, caron.
				'%cc%80',
				'%cc%84',
				'%cc%8c',
			),
			'',
			$permalink
		);

		// Convert &times to 'x'.
		$permalink = str_replace( '%c3%97', 'x', $permalink );
		// Kill entities.
		$permalink = preg_replace( '/&.+?;/', '', $permalink );

		// Avoid removing characters of other languages like persian etc.
		if ( 'en' === $language_code || strpos( $language_code, 'en_' ) === 0 ) {
			// Allow Alphanumeric and few symbols only.
			if ( ! $allow_caps ) {
				$permalink = preg_replace( '/[^%a-z0-9 \.\/_-]/', '', $permalink );
			} else {
				// Allow Capital letters.
				$permalink = preg_replace( '/[^%a-zA-Z0-9 \.\/_-]/', '', $permalink );
			}
		} else {
			$reserved_chars = array(
				'(',
				')',
				'[',
				']',
			);
			$unsafe_chars   = array(
				'<',
				'>',
				'{',
				'}',
				'|',
				'`',
				'^',
				'\\',
			);

			$permalink = str_replace( $reserved_chars, '', $permalink );
			$permalink = str_replace( $unsafe_chars, '', $permalink );
		
			$permalink = urlencode( $permalink );
			// Replace encoded slash input with slash.
			$permalink = str_replace( '%2F', '/', $permalink );

			$replace_hyphen = array( '%20', '%2B', '+' );
			$split_path     = explode( '%3F', $permalink );
			if ( 1 < count( $split_path ) ) {
				// Replace encoded space and plus input with hyphen.
				$replaced_path = str_replace( $replace_hyphen, '-', $split_path[0] );
				$replaced_path = preg_replace( '/(\-+)/', '-', $replaced_path );
				$permalink     = str_replace(
					$split_path[0],
					$replaced_path,
					$permalink
				);
			} else {
				// Replace encoded space and plus input with hyphen.
				$permalink = str_replace( $replace_hyphen, '-', $permalink );
				$permalink = preg_replace( '/(\-+)/', '-', $permalink );
			}
		}

		// Allow only dot that are coming before any alphabet.
		$allow_dot = explode( '.', $permalink );
		if ( 0 < count( $allow_dot ) ) {
			$new_perm   = $allow_dot[0];
			$dot_length = count( $allow_dot );
			for ( $i = 1; $i < $dot_length; ++$i ) {
				preg_match( '/^[a-z]/', $allow_dot[ $i ], $check_perm );
				if ( isset( $check_perm ) && ! empty( $check_perm ) ) {
					$new_perm .= '.';
				}
				$new_perm .= $allow_dot[ $i ];
			}

			$permalink = $new_perm;
		}

		$permalink = preg_replace( '/\s+/', '-', $permalink );
		$permalink = preg_replace( '|-+|', '-', $permalink );
		$permalink = str_replace( '-/', '/', $permalink );
		$permalink = str_replace( '/-', '/', $permalink );
		$permalink = trim( $permalink, '-' );

		return $permalink;
	}


	public function save_post( $post_id, $post ) {
		if ( ! isset( $_REQUEST['_wp_permalink_manager_post_nonce'] )
			&& ! isset( $_REQUEST['cp_editor'] )
		) {
			return;
		}

		$action = 'wp_permalink_manager_' . $post_id;
		
		if ( ! wp_verify_nonce( $_REQUEST['_wp_permalink_manager_post_nonce'], $action ) ) {
			return;
		}

		$cp_frontend   = new Custom_Permalink_Editor_Frontend();
		$original_link = $cp_frontend->original_post_link( $post_id );

		if ( ! empty( $_REQUEST['cp_editor'] )
			&& $_REQUEST['cp_editor'] !== $original_link
		) {
			$language_code = apply_filters(
				'wpml_element_language_code',
				null,
				array(
					'element_id'   => $post_id,
					'element_type' => $post->post_type,
				)
			);

			$permalink = $this->sanitize_permalink(
			
				$_REQUEST['cp_editor'],
				$language_code
			);
			$permalink = apply_filters(
				'wp_permalink_manager_before_saving',
				$permalink,
				$post_id
			);

			update_post_meta( $post_id, 'cp_editor', $permalink );
		}
	}


	public function delete_permalink( $post_id ) {
		delete_metadata( 'post', $post_id, 'cp_editor' );
	}

	
	private function get_permalink_html( $post, $meta_box = false ) {
		$post_id   = $post->ID;
		$permalink = get_post_meta( $post_id, 'cp_editor', true );

		ob_start();

		$cp_frontend = new Custom_Permalink_Editor_Frontend();
		if ( 'page' === $post->post_type ) {
			$original_permalink = $cp_frontend->original_page_link( $post_id );
			$view_post          = __( 'View Page', 'custom-permalink-editor' );
		} else {
			$post_type_name   = '';
			$post_type_object = get_post_type_object( $post->post_type );
			if ( is_object( $post_type_object ) && isset( $post_type_object->labels )
				&& isset( $post_type_object->labels->singular_name )
			) {
				$post_type_name = ' ' . $post_type_object->labels->singular_name;
			} elseif ( is_object( $post_type_object )
				&& isset( $post_type_object->label )
			) {
				$post_type_name = ' ' . $post_type_object->label;
			}

			$original_permalink = $cp_frontend->original_post_link( $post_id );
			$view_post          = __( 'View', 'custom-permalink-editor' ) . $post_type_name;
		}
		$this->get_permalink_form(
			$permalink,
			$original_permalink,
			$post_id,
			false,
			$post->post_name
		);

		$content = ob_get_contents();
		ob_end_clean();

		if ( 'trash' !== $post->post_status ) {
			$home_url = trailingslashit( home_url() );
			if ( isset( $permalink ) && ! empty( $permalink ) ) {
				$view_post_link = $home_url . $permalink;
			} else {
				if ( 'draft' === $post->post_status ) {
					$view_post      = 'Preview';
					$view_post_link = $home_url . '?';
					if ( 'page' === $post->post_type ) {
						$view_post_link .= 'page_id';
					} elseif ( 'post' === $post->post_type ) {
						$view_post_link .= 'p';
					} else {
						$view_post_link .= 'post_type=' . $post->post_type . '&p';
					}
					$view_post_link .= '=' . $post_id . '&preview=true';
				} else {
					$view_post_link = $home_url . $original_permalink;
				}
			}

			if ( true === $meta_box ) {
				$content .= '<style>.editor-post-permalink,.custom-permalink-editor-hidden{display:none;}</style>';
			}
		}

		return '<strong>' . __( 'New Permalink:', 'custom-permalink-editor' ) . '</strong> ' . $content;
	}

	public function sample_permalink_html( $html, $post_id ) {
		$post = get_post( $post_id );

		$disable_wp_pm              = $this->exclude_permalink_manager( $post );
		$this->permalink_metabox = 1;
		if ( $disable_wp_pm ) {
			return $html;
		}

		$output_content = $this->get_permalink_html( $post );

		return $output_content;
	}

	
	public function meta_edit_form( $post ) {
		$disable_wp_pm = $this->exclude_permalink_manager( $post );
		if ( $disable_wp_pm ) {
			wp_enqueue_script(
				'custom-permalink-editor-form',
				plugins_url(
					'/assets/js/script-form' . $this->js_file_suffix,
					CP_EDITOR_FILE
				),
				array(),
				CP_EDITOR_VERSION,
				true
			);

			return;
		}

		$screen = get_current_screen();
		if ( 'add' === $screen->action ) {
			echo '<input value="add" type="hidden" name="custom-permalink-editor-add" id="custom-permalink-editor-add" />';
		}

		$output_content = $this->get_permalink_html( $post, true );

		
		$allowed_html = $this->custom_permalink_allowed_tags();
		echo wp_kses( $output_content, $allowed_html );
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
			'input' => array(
				'id' => true,
				'class' => true,
				'type' => true,
				'value' => true,
				'style' => true,
				'name' => true,
				
			),
			'dl' => array(),
			'style' => array(),
			'dt' => array(),
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
	
	public function term_options( $tag ) {
		$permalink          = '';
		$original_permalink = '';

		if ( is_object( $tag ) && isset( $tag->term_id ) ) {
			$cp_frontend = new Custom_Permalink_Editor_Frontend();
			if ( $tag->term_id ) {
				$permalink          = $cp_frontend->term_permalink( $tag->term_id );
				$original_permalink = $cp_frontend->original_term_link(
					$tag->term_id
				);
			}

			$this->get_permalink_form( $permalink, $original_permalink, $tag->term_id );
		} else {
			$this->get_permalink_form( $permalink, $original_permalink, $tag );
		}

		// Move the save button to above this form.
		wp_enqueue_script( 'jquery' );
		?>
		<script type="text/javascript">
		jQuery(document).ready(function() {
			var button = jQuery('#wp_permalink_manager_form').parent().find('.submit');
			button.remove().insertAfter(jQuery('#wp_permalink_manager_form'));
		});
		</script>
		<?php
	}

	/**
	 * Helper function to render form.
	
	 */
	private function get_permalink_form( $permalink, $original, $id,
		$render_containers = true, $postname = ''
	) {
		$encoded_permalink = htmlspecialchars( urldecode( $permalink ) );
		$home_url          = trailingslashit( home_url() );

		if ( $render_containers ) {
			wp_nonce_field(
				'wp_permalink_manager_' . $id,
				'_cp_editor_term_nonce',
				false,
				true
			);
		} else {
			wp_nonce_field(
				'wp_permalink_manager_' . $id,
				'_wp_permalink_manager_post_nonce',
				false,
				true
			);
		}

	
		echo '<input value="' . esc_url($home_url) . '" type="hidden" name="custom_permalinks_home_url" id="custom_permalinks_home_url" />' .
		'<input value="' . esc_attr($encoded_permalink) . '" type="hidden" name="cp_editor" id="cp_editor" />';

		if ( $render_containers ) {
			echo '<table class="form-table" id="wp_permalink_manager_form">' .
				'<tr>' .
					'<th scope="row">' . esc_html__( 'Custom Permalink Editor', 'custom-permalink-editor' ) . '</th>' .
					'<td>';
		}
		if ( '' === $permalink ) {
			$original = $this->check_conflicts( $original );
		}

		if ( $permalink ) {
			$post_slug            = htmlspecialchars( urldecode( $permalink ) );
			$original_encoded_url = htmlspecialchars( urldecode( $original ) );
		} else {
			$post_slug            = htmlspecialchars( urldecode( $original ) );
			$original_encoded_url = $post_slug;
		}

		wp_enqueue_script(
			'custom-permalink-editor-form',
			plugins_url(
				'/assets/js/script-form' . $this->js_file_suffix,
				CP_EDITOR_FILE
			),
			array(),
			CP_EDITOR_VERSION,
			true
		);
		$postname_html = '';
		if ( isset( $postname ) && '' !== $postname ) {
			$postname_html = '<input type="hidden" id="new-post-slug" class="text" value="' . esc_attr($postname) . '" />';
			$allowed_html = $this->custom_permalink_allowed_tags();
			$postname_html = wp_kses($postname_html, $allowed_html);
		}

		$field_style = 'width: 250px;';
		if ( ! $permalink ) {
			$field_style .= ' color: #ddd;';
		}

		
		echo esc_url($home_url) .
		'<span id="editable-post-name" title="Click to edit this part of the permalink">' .
			$postname_html .
			'<input type="hidden" id="original-permalink" value="' . esc_attr($original_encoded_url) . '" />' .
			'<input type="text" id="custom-permalink-editor-post-slug" class="text" value="' . esc_attr($post_slug) . '" style="' . $field_style . '" />' .
		'</span>';
	

		if ( $render_containers ) {
			echo '<br />' .
			'<small>' .
				esc_html__( 'Leave blank to disable', 'custom-permalink-editor' ) .
			'</small>' .
			'</td>' .
			'</tr>' .
			'</table>';
		}
	}

	public function save_term( $term_id ) {
		$term = get_term( $term_id );

		if ( ! isset( $_REQUEST['_cp_editor_term_nonce'] )
			&& ! isset( $_REQUEST['cp_editor'] )
		) {
			return;
		}

		$action1 = 'wp_permalink_manager_' . $term_id;
		$action2 = 'wp_permalink_manager_' . $term->taxonomy;
		
		if ( ! wp_verify_nonce( $_REQUEST['_cp_editor_term_nonce'], $action1 )
			&& ! wp_verify_nonce( $_REQUEST['_cp_editor_term_nonce'], $action2 )
		) {
			return;
		}
	

		if ( isset( $term ) && isset( $term->taxonomy ) ) {
			$taxonomy_name = $term->taxonomy;
			if ( 'category' === $taxonomy_name || 'post_tag' === $taxonomy_name ) {
				if ( 'post_tag' === $taxonomy_name ) {
					$taxonomy_name = 'tag';
				}

				$new_permalink = ltrim(
				
					sanitize_title( $_REQUEST['cp_editor'] ),
					'/'
				);
				if ( empty( $new_permalink ) || '' === $new_permalink ) {
					return;
				}

				$cp_frontend   = new Custom_Permalink_Editor_Frontend();
				$old_permalink = $cp_frontend->original_term_link( $term_id );
				if ( $new_permalink === $old_permalink ) {
					return;
				}

				$this->delete_term_permalink( $term_id );

				$language_code = '';
				if ( isset( $term->term_taxonomy_id ) ) {
					$term_type = 'category';
					if ( isset( $term->taxonomy ) ) {
						$term_type = $term->taxonomy;
					}

					$language_code = apply_filters(
						'wpml_element_language_code',
						null,
						array(
							'element_id'   => $term->term_taxonomy_id,
							'element_type' => $term_type,
						)
					);
				}

				$permalink = $this->sanitize_permalink( $new_permalink, $language_code );
				$table     = get_option( 'cp_editor_table' );

				if ( $permalink && ! array_key_exists( $permalink, $table ) ) {
					$table[ $permalink ] = array(
						'id'   => $term_id,
						'kind' => $taxonomy_name,
						'slug' => $term->slug,
					);
				}

				update_option( 'cp_editor_table', $table );
			}
		}
	}


	public function delete_term_permalink( $term_id ) {
		$table = get_option( 'cp_editor_table' );
		if ( $table ) {
			foreach ( $table as $link => $info ) {
				if ( $info['id'] === (int) $term_id ) {
					unset( $table[ $link ] );
					break;
				}
			}
		}

		update_option( 'cp_editor_table', $table );
	}

	/**
	 * Check Conflicts and resolve it (e.g: Polylang) UPDATED for Polylang
	 * hide_default setting.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $requested_url Original permalink.
	 *
	 * @return string requested URL by removing the language/ from it if exist.
	 */
	public function check_conflicts( $requested_url = '' ) {
		if ( '' === $requested_url ) {
			return;
		}

		// Check if the Polylang Plugin is installed so, make changes in the URL.
		if ( defined( 'POLYLANG_VERSION' ) ) {
			$polylang_config = get_option( 'polylang' );
			if ( 1 === $polylang_config['force_lang'] ) {
				if ( false !== strpos( $requested_url, 'language/' ) ) {
					$requested_url = str_replace( 'language/', '', $requested_url );
				}

				/*
				 * Check if `hide_default` is `true` and the current language is not
				 * the default. Otherwise remove the lang code from the url.
				 */
				if ( 1 === $polylang_config['hide_default'] ) {
					$current_language = '';
					if ( function_exists( 'pll_current_language' ) ) {
						// get current language.
						$current_language = pll_current_language();
					}

					// get default language.
					$default_language = $polylang_config['default_lang'];
					if ( $current_language !== $default_language ) {
						$remove_lang = ltrim( strstr( $requested_url, '/' ), '/' );
						if ( '' !== $remove_lang ) {
							return $remove_lang;
						}
					}
				} else {
					$remove_lang = ltrim( strstr( $requested_url, '/' ), '/' );
					if ( '' !== $remove_lang ) {
						return $remove_lang;
					}
				}
			}
		}

		return $requested_url;
	}

	/**
	 * Refresh Permalink using AJAX Call.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param object $data Contains post id with some default REST Values.
	 *
	 * @return void
	 */
	public function refresh_meta_form( $data ) {
		if ( isset( $data['id'] ) && is_numeric( $data['id'] ) ) {
			$post                               = get_post( $data['id'] );
			$all_permalinks                     = array();
			$all_permalinks['cp_editor'] = get_post_meta(
				$data['id'],
				'cp_editor',
				true
			);

			if ( ! $all_permalinks['cp_editor'] ) {
				if ( 'draft' === $post->post_status ) {
					$view_post_link = '?';
					if ( 'page' === $post->post_type ) {
						$view_post_link .= 'page_id';
					} elseif ( 'post' === $post->post_type ) {
						$view_post_link .= 'p';
					} else {
						$view_post_link .= 'post_type=' . $post->post_type . '&p';
					}
					$view_post_link .= '=' . $data['id'] . '&preview=true';

					$all_permalinks['preview_permalink'] = $view_post_link;
				}
			} else {
				$all_permalinks['cp_editor'] = htmlspecialchars(
					urldecode(
						$all_permalinks['cp_editor']
					)
				);
			}

			$cp_frontend = new Custom_Permalink_Editor_Frontend();
			if ( 'page' === $post->post_type ) {
				$all_permalinks['original_permalink'] = $cp_frontend->original_page_link(
					$data['id']
				);
			} else {
				$all_permalinks['original_permalink'] = $cp_frontend->original_post_link(
					$data['id']
				);
			}

			echo wp_json_encode( $all_permalinks );
			exit;
		}
	}

	/**
	 * Added Custom Endpoints for refreshing the permalink.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function rest_edit_form() {
		register_rest_route(
			'custom-permalink-editor/v1',
			'/get-permalink/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'refresh_meta_form' ),
				'args'                => array(
					'id' => array(
						'validate_callback' => 'is_numeric',
					),
				),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	/**
	 * Delete the permalink for the page selected as the static Homepage.
	
	 */
	public function static_homepage( $prev_homepage_id, $new_homepage_id ) {
		if ( $prev_homepage_id !== $new_homepage_id ) {
			$this->delete_permalink( $new_homepage_id );
		}
	}
}
