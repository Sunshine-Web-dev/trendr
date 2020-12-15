<?php

class Med_Admin {

	private $_page_hook;
	private $_capability;

	private function __construct () {
		$this->_capability = trs_core_do_network_admin()
			? 'manage_network_options'
			: 'manage_options'
		;
	}

	public static function serve () {
		$me = new self;
		$me->_add_hooks();
	}

	private function _add_hooks () {
		add_action(trs_core_admin_hook(), array($this, 'add_menu_page'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_dependencies'));
	}

	public function add_menu_page () {
		$hook = trs_core_do_network_admin()
			? 'settings.php'
			: 'options-general.php'
		;
		$this->_page_hook = add_submenu_page(
			$hook,
			__('trendr Activity Plus', 'med'),
			__('Activity Plus', 'med'),
			$this->_capability,
			'med-settings',
			array($this, 'settings_page')
		);

		$this->_save_settings();
	}

	public function enqueue_dependencies ($hook) {
		if ($hook !== $this->_page_hook) return false;
		trm_enqueue_style('med-admin', MED_PLUGIN_URL . '/css/admin.css');
	}

	private function _save_settings () {
		if (empty($_POST['med'])) return false;
		if (!current_user_can($this->_capability)) return false;
		if (!check_ajax_referer($this->_page_hook)) return false;

		$raw = stripslashes_deep($_POST['med']);
		list($thumb_w,$thumb_h) = Med_Data::get_thumbnail_size(true);
		$raw['thumbnail_size_height'] = !empty($raw['thumbnail_size_height']) && (int)$raw['thumbnail_size_height']
			? (int)$raw['thumbnail_size_height']
			: $thumb_h
		;
		$raw['thumbnail_size_width'] = !empty($raw['thumbnail_size_width']) && (int)$raw['thumbnail_size_width']
			? (int)$raw['thumbnail_size_width']
			: $thumb_w
		;
		$raw['oembed_width'] = !empty($raw['oembed_width']) && (int)$raw['oembed_width']
			? (int)$raw['oembed_width']
			: Med_Data::get('oembed_width')
		;
		$raw['theme'] = !empty($raw['theme'])
			? sanitize_html_class($raw['theme'])
			: ''
		;
		$raw['cleanup_images'] = !empty($raw['cleanup_images'])
			? (int)$raw['cleanup_images']
			: false
		;
		
		update_option('med', $raw);
		trm_safe_redirect(add_query_arg(array('updated' => true)));
	}

	public function settings_page () {
		$theme = Med_Data::get('theme');
		list($thumb_w,$thumb_h) = Med_Data::get_thumbnail_size();
		$oembed_width = Med_Data::get('oembed_width', 450);
		$alignment = Med_Data::get('alignment', 'left');
		$cleanup_images = Med_Data::get('cleanup_images', false);
		?>
<div class="wrap med">
	<?php screen_icon('trendr'); ?>
	<h2><?php echo get_admin_page_title(); ?></h2>
	<form action="" method="POST">
		
		<fieldset class="appearance section">
			<legend><?php _e('Appearance', 'med'); ?></legend>

			<?php if (current_theme_supports('med_interface_style') || current_theme_supports('med_toolbar_icons')) { ?>
			<div class="updated below-h2">
				<p><?php _e('Your trendr theme incorporates Activity Plus style overrides. Respecting the selection you make in the &quot;Appearance&quot; section is entirely up to your theme.', 'med'); ?></p>
			</div>
			<?php } ?>

			<fieldset class="theme option">
				<legend><?php _e('Theme', 'med'); ?></legend>
				<label for="med-theme-default">
					<img src="<?php echo MED_PLUGIN_URL; ?>/img/system/theme-legacy.png" />
					<input type="radio" id="med-theme-default" name="med[theme]" value="" <?php checked($theme, ''); ?> />
					<?php _e('Default (legacy)', 'med'); ?>
				</label>
				<label for="med-theme-new">
					<img src="<?php echo MED_PLUGIN_URL; ?>/img/system/theme-new.png" />
					<input type="radio" id="med-theme-new" name="med[theme]" value="new" <?php checked($theme, 'new'); ?> />
					<?php _e('New', 'med'); ?>
				</label>
				<label for="med-theme-round">
					<img src="<?php echo MED_PLUGIN_URL; ?>/img/system/theme-round.png" />
					<input type="radio" id="med-theme-round" name="med[theme]" value="round" <?php checked($theme, 'round'); ?> />
					<?php _e('Round', 'med'); ?>
				</label>
			</fieldset>
			<fieldset class="alignment option">
				<legend><?php _e('Alignment', 'med'); ?></legend>
				<label for="med-theme-alignment-left">
					<input type="radio" id="med-theme-alignment-left" name="med[alignment]" value="left" <?php checked($alignment, 'left'); ?> />
					<?php _e('Left', 'med'); ?>
				</label>
				<label for="med-theme-alignment-right">
					<input type="radio" id="med-theme-alignment-right" name="med[alignment]" value="right" <?php checked($alignment, 'right'); ?> />
					<?php _e('Right', 'med'); ?>
				</label>
			</fieldset>
		</fieldset>

		
		<fieldset class="functional section">
			<legend><?php _e('Functional', 'med'); ?></legend>
			
			<fieldset class="oembed option">
				<legend><?php _e('oEmbed', 'med'); ?></legend>
				<?php if (defined('MED_THUMBNAIL_IMAGE_SIZE')) { ?>
					<div class="updated below-h2">
						<p><?php printf(__('Your oEmbed dimensions will be dictated by the <code>MED_OEMBED_WIDTH</code> define value (%s). Remove this define to enable this option.', 'med'), MED_OEMBED_WIDTH); ?></p>
					</div>
				<?php } ?>
				<label for="med-oembed-width">
					<?php _e('Width', 'med') ?>
					<input type="text" id="med-oembed-width" name="med[oembed_width]" size="4" value="<?php echo (int)$oembed_width; ?>" <?php echo (defined('MED_OEMBED_WIDTH') ? 'disabled="disabled"' : ''); ?> /> px
				</label>
			</fieldset>
			<fieldset class="thumbnail option">
				<legend><?php _e('Image thumbnails', 'med'); ?></legend>
				<?php if (defined('MED_THUMBNAIL_IMAGE_SIZE')) { ?>
					<div class="updated below-h2">
						<p><?php printf(__('Your thumbnail dimensions will be dictated by the <code>MED_THUMBNAIL_IMAGE_SIZE</code> define value (%s). Remove this define to enable these options.', 'med'), MED_THUMBNAIL_IMAGE_SIZE); ?></p>
					</div>
				<?php } ?>
				<label for="med-thumbnail_size-width">
					<?php _e('Width', 'med') ?>
					<input type="text" id="med-thumbnail_size-width" name="med[thumbnail_size_width]" size="4" value="<?php echo (int)$thumb_w; ?>" <?php echo (defined('MED_THUMBNAIL_IMAGE_SIZE') ? 'disabled="disabled"' : ''); ?> /> px
				</label>
				<label for="med-thumbnail_size-height">
					<?php _e('Height', 'med') ?>
					<input type="text" id="med-thumbnail_size-height" name="med[thumbnail_size_height]" size="4" value="<?php echo (int)$thumb_h; ?>" <?php echo (defined('MED_THUMBNAIL_IMAGE_SIZE') ? 'disabled="disabled"' : ''); ?> /> px
				</label>
			</fieldset>
			<fieldset class="med-misc option">
				<legend><?php _e('Misc', 'med'); ?></legend>
				<label for="med-cleanup_images">
					<input type="checkbox" id="med-cleanup_images" name="med[cleanup_images]" value="1" <?php checked($cleanup_images, true); ?> />
					<?php _e('Clean up images?', 'med'); ?>
				</label>
			</fieldset>
		</fieldset>

		<p>
			<?php trm_nonce_field($this->_page_hook); ?>
			<button class="button button-primary"><?php _e('Save'); ?></button>
		</p>
	</form>
</div>
		<?php
	}
}