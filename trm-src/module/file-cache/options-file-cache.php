<?php
	if (!current_user_can('manage_options')) {
		trm_die(__("You don't have enough privileges to do this", 'sjfilecache'));
	}

	$trm_file_cache = WpFileCache::instance();
	$message = '';
	$error   = '';
	$options = $trm_file_cache->getOptions();

	if (!file_exists(ABSPATH . 'trm-src/object-cache.php')) {
		$error = sprintf(__("%1\$s does not exist. Please make sure that %2\$s is writable by the server.", 'sjfilecache'), ABSPATH . 'trm-src/object-cache.php', ABSPATH . 'trm-src');
	}

	if (isset($_POST['options']) && isset($_POST['submit'])) {
		check_admin_referer('filecache-config');

		foreach ($options as $key => $value) {
			if (isset($_POST['options'][$key])) {
				$options[$key] = stripslashes($_POST['options'][$key]);
			}
			else {
				$options[$key] = '';
			}
		}

		if (empty($options['path'])) {
			$options['path'] = dirname(__FILE__) . '/cache';
		}

		$options['nonpersistent'] = str_replace(' ', '', $options['nonpersistent']);

		if ($trm_file_cache->writeOptions($options)) {
			$message = __("Settings have been successfully updated", 'sjfilecache');
		}
		else {
			$error = $error = sprintf(__("Unable to write to file %s. Please make sure that it is writable by the server.", 'sjfilecache'), ABSPATH . 'trm-src/object-cache.php');
		}

		if (empty($error) && $options['enabled'] && $options['persist']) {
			if (!file_exists($options['path']) || !is_dir($options['path'])) {
				$error = __("Cache directory does not exist!", 'sjfilecache');
			}
			elseif (!is_readable($options['path']) || !is_writable($options['path'])) {
				$error = __("Cache directory must be readable and writable by the server!", 'sjfilecache');
			}
		}
	}
	elseif (isset($_POST['purge'])) {
		check_admin_referer('filecache-config');
		trm_cache_flush();
		$message = __("Cache has been successfully purged", 'sjfilecache');
	}
?>
<div class="wrap">
	<h2><?php _e("TRM File Cache Options", 'sjfilecache'); ?></h2>

	<?php if (!empty($error)) : ?>
	<div class="error"><p><?php echo $error; ?></p></div>
	<?php endif; ?>

	<?php if (!empty($message)) : ?>
	<div class="updated fade"><p><?php echo $message; ?></p></div>
	<?php endif; ?>

	<form method="post" action="">
		<table class="form-table">
			<tbody valign="top">
				<tr>
					<th scope="row"><label for="sjfc_enabled"><?php _e('Enable TRM File Cache', 'sjfilecache'); ?></label></th>
					<td><input type="checkbox" id="sjfc_enabled" name="options[enabled]" value="1"<?php checked(1, $options['enabled']); ?>/></td>
					<td>
						<strong><?php _e('Disabling TRM File Cache can make Trnder crawl!', 'sjfilecache') ?></strong><br/>
						<?php _e("If you disable TRM File Cache, caching will be completely disabled, and Trnder will have to use the database every time it needs data. This is really slow. If you are not a Trnder developer, please do not do this.", 'sjfilecache') ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="sjfc_persist"><?php _e('Save cached data across sessions', 'sjfilecache'); ?></label></th>
					<td><input type="checkbox" id="sjfc_persist" name="options[persist]" value="1"<?php checked(1, $options['persist']); ?>/></td>
					<td>
						<?php _e("If this option is set, TRM File Cache will maintain its cache between sessions to improve overall performance. Actually, this is what this plugin was made for and we strongly recommend that you don't turn this option off.", 'sjfilecache'); ?><br/>
						<small><?php _e("<strong>Boring technical details:</strong> TRM File Cache will save only those data that were not marked as 'non-persistent'.", 'sjfilecache'); ?></small>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="sjfc_path"><?php _e('Cache location', 'sjfilecache'); ?></label></th>
					<td><input type="text" id="sjfc_path" name="options[path]" value=""/></td>
					<td>
						<?php _e("This is the directory where TRM File Cache will store its cache.", 'sjfilecache'); ?><br/>
						<?php _e("<strong>Please note:</strong> this directory must be writable by the web server.", 'sjfilecache'); ?><br/>
						<?php _e("<strong>Security notice:</strong> it is advisable that you keep this directory outside the root of your site to make it inaccessible from the web. If this is not an option, consider restricting access to that directory.", 'sjfilecache'); ?><br/>
						<?php _e('<strong>For Linux geeks:</strong> consider placing the cache to <code>tmpfs</code> file system (or <code>/dev/shm</code>) — this should make things faster.', 'sjfilecache'); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="sjfc_admin_fresh"><?php _e('Do not use cache in the Admin Panel', 'sjfilecache'); ?></label></th>
					<td><input type="checkbox" id="sjfc_admin_fresh" name="options[admin_fresh]" value="1"<?php checked(1, $options['admin_fresh']); ?>/></td>
					<td>
						<?php _e("If this option is set, TRM File Cache will not fetch the data from the cache in the Admin Panel. However, to keep the cache consistent, write cache requests will be satisfied.", 'sjfilecache'); ?><br/>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="sjfc_np"><?php _e('Non-persistent groups', 'sjfilecache'); ?></label></th>
					<td><input type="text" id="sjfc_np" name="options[nonpersistent]" value=""/></td>
					<td>
						<?php _e("Comma-separated list of the cache groups which should never be stored across sessions.", 'sjfilecache'); ?>
					</td>
				</tr>
			</tbody>
		</table>

		<p class="submit submit-top">
			<?php trm_nonce_field('filecache-config'); ?>
			<input type="submit" name="submit" value="<?php _e('Save Changes', 'sjfilecache') ?>" class="button-primary"/>
			<input type="submit" name="purge" value="<?php _e('Purge Cache', 'sjfilecache') ?>" class="deletion" onclick="return confirm('<?php _e("Are you sure?", 'sjfilecache'); ?>')"/>
		</p>
	</form>
</div>
