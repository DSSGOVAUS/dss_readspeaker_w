<?php
/*
Plugin Name: DSS's ReadSpeaker
Description: Adds a Readspeaker 'Listen' button to the top of specified pages. Intended for use with DSS's WordPress websites. Requires the utils.php template part to use as a hook.
Author: Chris Lamb
Version: 3.1
*/

// On Activate
register_activation_hook(__FILE__,'dss_readspeaker_activate');
function dss_readspeaker_activate() {
	// Plugin defaults
	$dss_readspeaker_settings = array(
		'customerid' => '5931',
		'readid' => 'content',
		'rslang' => 'en_au',
		'region' => 'oc',
		'onindex' => true,
		'onfront' => false,
		'onpage' => true,
		'onsingle' => true,
		'onarchive' => true,
		'onsearch' => true,
		'on404' => false,
		'rsstyles' => false,
	);
	add_option('dss_readspeaker_settings',$dss_readspeaker_settings);
}

// On Uninstall
register_uninstall_hook(__FILE__,'dss_readspeaker_uninstall');
function dss_readspeaker_uninstall() {
	delete_option('dss_readspeaker_settings');
}

// Register and Enqueue Styles and Scripts
add_action('wp_enqueue_scripts', 'dss_readspeaker_enqueue_scripts');
function dss_readspeaker_enqueue_scripts() {
	$dss_readspeaker_settings = get_option('dss_readspeaker_settings');
	$region = $dss_readspeaker_settings['region'];
	$customerid = $dss_readspeaker_settings['customerid'];
	wp_enqueue_script('dss_readspeaker', '//f1-'.$region.'.readspeaker.com/script/'.$customerid.'/ReadSpeaker.js?pids=embhl', 'jquery', NULL );
	// Optionally load custom styles
	if ($dss_readspeaker_settings['rsstyles']) {
		wp_enqueue_script('dss_readspeaker_customjs', plugins_url('ReadSpeakerColorSkin.js', __FILE__));
		wp_enqueue_style('dss_readspeaker_customcss', plugins_url('ReadSpeakerColorSkin.css', __FILE__));
	}
}

// Filter into the utils template part
add_filter('get_template_part_utils', 'dss_readspeaker_render');
function dss_readspeaker_render() {
	$dss_readspeaker_settings = get_option('dss_readspeaker_settings');
	if (
		($dss_readspeaker_settings['onindex'] && is_home()) ||
		($dss_readspeaker_settings['onfront'] && is_front_page()) ||
		($dss_readspeaker_settings['onsingle'] && is_single()) ||
		($dss_readspeaker_settings['onpage'] && is_page() && (!is_front_page())) ||
		($dss_readspeaker_settings['onarchive'] && is_archive()) ||
		($dss_readspeaker_settings['onsearch'] && is_search()) ||
		($dss_readspeaker_settings['on404'] && is_404())
		) {
		// Custom styles
		if ($dss_readspeaker_settings['rsstyles'] { ?>
			<div id="readspeaker_button" class="rs_skip rsbtn_colorskin rs_preserve">
			<a rel="nofollow" class="rsbtn_play" href="//app-<?php echo $dss_readspeaker_settings['region']; ?>.readspeaker.com/cgi-bin/rsent?customerid=<?php echo $dss_readspeaker_settings['customerid']; ?>&amp;lang=<?php echo $dss_readspeaker_settings['rslang']; ?>&amp;readid=<?php echo $dss_readspeaker_settings['readid']; ?>&amp;url=//<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
			<span class="rsbtn_left rspart rsimg"><span class="rsbtn_text"><span>Listen</span></span></span><span class="rsgrad"><span class="rsbtn_right rsplay rspart"></span></span>
			</a>
			</div>
		<?php }
		// Else, Default Readspeaker code
		else { ?>
			<div id="readspeaker_button" class="rs_skip rsbtn rs_preserve">
    	<a rel="nofollow" class="rsbtn_play" href="//app-<?php echo $dss_readspeaker_settings['region']; ?>.readspeaker.com/cgi-bin/rsent?customerid=<?php echo $dss_readspeaker_settings['customerid']; ?>&amp;lang=<?php echo $dss_readspeaker_settings['rslang']; ?>&amp;readid=<?php echo $dss_readspeaker_settings['readid']; ?>&amp;url=//<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
			<span class="rsbtn_left rspart rsimg"><span class="rsbtn_text"><span>Listen</span></span></span><span class="rsbtn_right rsimg rsplay rspart"></span>
			</a>
			</div>
<?php } } }

// Create a menu link to the config page
add_action('admin_menu','dss_readspeaker_admin_link');
function dss_readspeaker_admin_link() {
    add_submenu_page('options-general.php', 'ReadSpeaker', 'ReadSpeaker', 'manage_options', 'dss_readspeaker', 'dss_readspeaker_page');
}

// The WordPress admin page
function dss_readspeaker_page() {
	// Start Form processing
	if(isset($_POST['action'])) {
		check_admin_referer('dss_readspeaker_form');
		if ($_POST['action'] == 'update') {
			$dss_readspeaker_settings = array(
				'customerid' => isset($_POST['customerid']) ? $_POST['customerid'] : '',
				'readid' => isset($_POST['readid']) ? $_POST['readid'] : 'content',
				'rslang' => isset($_POST['rslang']) ? $_POST['rslang'] : 'en_au',
				'region' => isset($_POST['region']) ? $_POST['region'] : 'oc',
				'onindex' => isset($_POST['onindex']) ? true : false,
				'onfront' => isset($_POST['onfront']) ? true : false,
				'onpage' => isset($_POST['onpage']) ? true : false,
				'onsingle' => isset($_POST['onsingle']) ? true : false,
				'onarchive' => isset($_POST['onarchive']) ? true : false,
				'onsearch' => isset($_POST['onsearch']) ? true : false,
				'on404' => isset($_POST['on404']) ? true : false,
				'rsstyles' => isset($_POST['rsstyles']) ? true : false,
			);
			update_option('dss_readspeaker_settings',$dss_readspeaker_settings);
			echo '<div class="updated"><p>Settings saved</p></div>';
		}
	} // End form processing ?>
	<div class="wrap">
    <h2>ReadSpeaker Settings</h2>
	<form action="#" method="post" id="fr_form">
	<?php wp_nonce_field('dss_readspeaker_form'); ?>
	<?php $dss_readspeaker_settings = get_option('dss_readspeaker_settings'); ?>
	<input type="hidden" name="action" value="update" />
	<fieldset><legend>General options</legend>
	<p><label for="customerid">Customer ID:</label>
	<input maxlength="4" size="4" type="text" name="customerid" id="customerid" value="<?php echo $dss_readspeaker_settings['customerid']; ?>" />
    <small>Your ReadSpeaker Cutomer ID</small></p>
	<p><label for="readid">Read ID:</label>
	<input type="text" name="readid" id="readid" value="<?php echo $dss_readspeaker_settings['readid']; ?>" />
    <small>The ID of the page element to be read</small></p>
	<p><label for="rslang">Language:</label>
	<input maxlength="5" size="5" type="text" name="rslang" id="rslang" value="<?php echo $dss_readspeaker_settings['rslang']; ?>" />
    <small>Document language</small></p>
	<p><label for="region">Region:</label>
	<input maxlength="2" size="2" type="text" name="region" id="region" value="<?php echo $dss_readspeaker_settings['region']; ?>" />
    <small> ReadSpeaker Region</small></p>
	</fieldset>
	<fieldset><legend>Template parts to display on</legend>
	<p><input id="onindex" type="checkbox" name="onindex" <?php if ($dss_readspeaker_settings['onindex']) echo ' checked="checked"'; ?> />
    <label for="onindex">Blog index</label><small>index.php</small><br />
	<input id="onfront" type="checkbox" name="onfront" <?php if ($dss_readspeaker_settings['onfront']) echo ' checked="checked"'; ?> />
    <label for="onfront">Front page</label><small>front-page.php</small><br />
	<input id="onsingle" type="checkbox" name="onsingle" <?php if ($dss_readspeaker_settings['onsingle']) echo ' checked="checked"'; ?> />
    <label for="onsingle">Single</label><small>single.php</small><br />
	<input id="onpage" type="checkbox" name="onpage" <?php if ($dss_readspeaker_settings['onpage']) echo ' checked="checked"'; ?> />
    <label for="onpage">Page</label><small>page.php</small><br />
    <input id="onarchive" type="checkbox" name="onarchive" <?php if ($dss_readspeaker_settings['onarchive']) echo ' checked="checked"'; ?> />
    <label for="onarchive">Archives</label><small>archive.php</small><br />
    <input id="onsearch" type="checkbox" name="onsearch" <?php if ($dss_readspeaker_settings['onsearch']) echo ' checked="checked"'; ?> />
    <label for="onsearch">Search results</label><small>search.php</small><br />
	<input id="on404" type="checkbox" name="on404" <?php if ($dss_readspeaker_settings['on404']) echo ' checked="checked"'; ?> />
    <label for="on404">404 page</label><small>404.php</small>
    </p>
	</fieldset>
	<fieldset><legend>Advanced</legend>
	<p><input id="rsstyles" type="checkbox" name="rsstyles" <?php if ($dss_readspeaker_settings['rsstyles']) echo ' checked="checked"'; ?> />
    <label for="rsstyles">Use custom styles</label><small>Uncheck to use ReadSpeaker's default styles</small></p>
	</fieldset>
	<p><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
	</form>
	<?php // Help text if missing the utils.php template part
	if (!locate_template('utils.php')) { ?>
    	<div class="error">
		 <h3>Please ensure that your theme includes a utils.php template part:</h3>
         <p>This plugin won't work without one. Follow these steps:</p>
         <ol>
         <li>Create a new file within your theme dirctory, called 'utils.php'. Open it in a text editor and insert the following code:<br />
         <code>&lt;?php // An empty template part: used as a hook point for the filter 'get_template_part_utils' ?&gt;</code>
         </li>
         <li>Edit your themes header.php file and (in an appropriate position) insert the following code:<br /><code>&lt;?php get_template_part('utils'); ?&gt;</code>
         </li>
         <li>Reload this page. This message should disappear.</li>
         </ol>
    	</div>
	<?php } ?>
	<style>
	#fr_form fieldset {margin: 10px; padding: 5px 15px;}
	#fr_form legend {font-size: 1.4em; font-weight: bold; margin: 10px 0 0 0;}
	#fr_form label {display: inline-block; margin: 4px 0px; width: 130px;}
	</style>
    </div>
<?php }
