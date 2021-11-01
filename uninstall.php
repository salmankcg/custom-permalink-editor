<?php


if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_post_meta_by_key( 'cp_editor' );
delete_option( 'cp_editor_table' );

$wp_role = get_role( 'administrator' );
if ( ! empty( $wp_role ) ) {
	$wp_role->remove_cap( 'cp_editor_view_post_permalinks' );
	$wp_role->remove_cap( 'cp_editor_view_category_permalinks' );
}

$wp_role = get_role( 'cp_editor_mr' );
if ( ! empty( $wp_role ) ) {
	$wp_role->remove_cap( 'cp_editor_view_post_permalinks' );
	$wp_role->remove_cap( 'cp_editor_view_category_permalinks' );

	remove_role( 'cp_editor_mr' );
}

// Clear any cached data that has been removed.
wp_cache_flush();
