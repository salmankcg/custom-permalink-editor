=== Custom Permalink Editor ===
Contributors: Team KCG
Tags: custom permalinks,custom url,permalink,permalinks,url,url editor,address,custom,link,custom post type,custom post url,woocommerce permalink,slug
Tested up to: 5.8
Stable tag: 1.0.3
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Set Custom Permalink Editor on a per-post, per-tag per-page, and per-category basis.

== Description == 

-  This plugin is Developed by Team KCG to change the URL of the post, page, and custom post.
-  This plugin allows you to edit permalink on posts, pages, custom posts.
-  This plugin Do not change the theme default permalink structure
-  You have to edit every post/page manually and edit the permalink. 
-  If you Uninstall this plugin. Your post will back to the default permalink.


== Free Plugin Support == 

-  If you need any custom modifications or for any other queries, feedback if you simply want to get in touch with us please use our <a href="https://kingscrestglobal.com/contact/">contact form.</a> 

== Privacy Policy ==

> This plugin does not collect any user Information
> If you need any custom modification or any other thing contact with https://kingscrestglobal.com/ and mention Custom Permalink Editor


== Screenshots ==
1. screenshot-1.png

== Filters ==

=== Add `PATH_INFO` in `$_SERVER` Variable ===
`
add_filter( 'cp_editor_path_info', '__return_true' );
`

=== Exclude Permalink  ===

To exclude any Permalink to be processed by the plugin, add the filter that looks like this:

`
function team_kcg_exclude_permalink( $permalink ) {
  if ( false !== strpos( $permalink, 'sitemap.xml' ) ) {
    return '__true';
  }

  return;
}
add_filter( 'cp_editor_exclude_permalink', 'team_kcg_exclude_permalink' );
`

=== Exclude Post Type ===

To remove Custom Permalink Editor **form** from any post type, add the filter that looks like this:

`
function team_kcg_exclude_post_type( $post_type ) {
  // Replace 'custompost' with your post type name
  if ( 'custompost' === $post_type ) {
    return '__true';
  }

  return '__false';
}
add_filter( 'cp_editor_exclude_post_type', 'team_kcg_exclude_post_type' );
`

=== Exclude Posts ===

To exclude Custom Permalink Editor **form** from any posts (based on ID, Template, etc), add the filter that looks like this:

`
function team_kcg_exclude_posts( $post ) {
  if ( 1557 === $post->ID ) {
    return true;
  }

  return false;
}
add_filter( 'cp_editor_exclude_posts', 'team_kcg_exclude_posts' );
`

=== Allow Accents Letters ===

To allow accents letters, please add this on your theme `functions.php`:

`
function team_kcg_allow_caps() {
  return true;
}
add_filter( 'cp_editor_allow_accents', 'team_kcg_allow_caps' );
`

=== Thanks for the Support ===


== Installation ==

Follow the following step to Install Custom Permalink Editor through wordpress or Manually from FTP.

**From within WordPress**

1. Visit 'Plugins > Add New'
2. Search for Custom Permalink Editor
3. Activate Custom Permalink Editor from your Plugins page.

**Manually**

1. Upload the `custom-permalink-editor` folder to the `/wp-content/plugins/` directory
2. Activate Custom Permalink Editor through the 'Plugins' menu in WordPress

== How To Use ==

You can change the permalink by following the steps.

- Edit your posts/pages and create SEO friendly custom URL.
- In the permalink box insert your desired permalink and update the post.
- Preview your post and see the post URL is changed.
- If you want to revert to the Wordpress default URL system, just deactivate the plugin.


