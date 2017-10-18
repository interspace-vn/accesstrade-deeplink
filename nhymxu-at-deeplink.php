<?php
/*
Plugin Name: AccessTrade Deeplink
Plugin URI: http://github.com/nhymxu/accesstrade-deeplink
Description: Chuyển link sản phẩm thành deeplink cho hệ thống của AccessTrade
Author: Dũng Nguyễn (nhymxu)
Version: 0.2.3
Author URI: http://dungnt.net
*/

class nhymxu_at_deeplink {
	public function __construct() {
		add_shortcode( 'at', [$this,'shortcode_callback'] );
		add_action('admin_menu', [$this,'admin_page']);
		add_action("admin_print_footer_scripts", [$this, 'shortcode_button_script']);	
		add_action( 'init', [$this,'tinymce_new_button'] );			
	}

	function admin_page() {
		add_options_page('AccessTrade Deeplink', 'AccessTrade Deeplink', 'manage_options', 'nhymxu_at_deeplink', [$this,'admin_page_callback']);
	}
	function generate( $url ) {
		$option = get_option('nhymxu_at_deeplink', ['uid' => '', 'utmsource' => '']);
		
		if( $option['uid'] == '' ) {
			return $url;
		}
	
		$utm_source = '';
		if( $option['utmsource'] != '' ) {
			$utm_source = '&utm_source='. $option['utmsource'];
		}
	
		return 'https://pub.accesstrade.vn/deep_link/'. $option['uid'] .'?url=' . rawurlencode( $url ) . $utm_source;
	}
	
	function shortcode_callback( $atts, $content = '' ) {
		$a = shortcode_atts( ['url' => ''], $atts );
		
		if( $a['url'] == '' ) {
			return '<a href="'. $this->generate( $content ).'" target="_blank">' . $content . '</a>';		
		} else if( $content != '' ) {
			return '<a href="'. $this->generate( $a['url'] ).'" target="_blank">' . do_shortcode($content) . '</a>';
		}
	}
	
	function admin_page_callback() {
		if( isset( $_POST, $_POST['nhymxu_hidden'] ) && $_POST['nhymxu_hidden'] == 'deeplink' ) {
			$input = [
				'uid'	=> sanitize_text_field($_REQUEST['nhymxu_at_deeplink_uid']),
				'utmsource'	=> sanitize_text_field($_REQUEST['nhymxu_at_deeplink_utmsource'])
			];
	
			update_option('nhymxu_at_deeplink', $input);
			echo '<h1>Cập nhật thành công</h1><br>';
		}
		$option = get_option('nhymxu_at_deeplink', ['uid' => '', 'utmsource' => '']);
	?>
	
	<div>
		<h2>Cài đặt AccessTrade Deeplink</h2>
		<br>
		<form action="options-general.php?page=nhymxu_at_deeplink" method="post">
			<input type="hidden" name="nhymxu_hidden" value="deeplink">
			<table>
				<tr>
					<td>AccessTrade ID*:</td>
					<td><input type="text" name="nhymxu_at_deeplink_uid" value="<?=$option['uid'];?>"></td>
				</tr>
				<tr>
					<td></td>
					<td>Lấy ID tại <a href="https://pub.accesstrade.vn/tools/deep_link" target="_blank">đây</a></td>
				</tr>
				<tr>
					<td>UTM Source:</td>
					<td><input type="text" name="nhymxu_at_deeplink_utmsource" value="<?=$option['utmsource'];?>"></td>
				</tr>
			</table>
			<input name="Submit" type="submit" value="Lưu">
		</form>
	</div>
	<?php
	}

	function shortcode_button_script() {
		if(wp_script_is("quicktags")):
			?>
			<script type="text/javascript">
			//this function is used to retrieve the selected text from the text editor
			function getSel()
			{
				var txtarea = document.getElementById("content");
				var start = txtarea.selectionStart;
				var finish = txtarea.selectionEnd;
				return txtarea.value.substring(start, finish);
			}

			QTags.addButton( 
				"at_shortcode", 
				"AT Deeplink", 
				callback
			);

			function callback()
			{
				var selected_text = getSel();
				if( selected_text == '' ) {
					selected_text = 'dien_ten_san_pham';
				}
				QTags.insertContent('[at url="dien_link_san_pham"]' +  selected_text + '[/at]');
			}
			</script>
			<?php
		endif;
	}

	function tinymce_new_button() {
		add_filter("mce_external_plugins", [$this,'tinymce_add_button']);
		add_filter("mce_buttons", [$this,'tinymce_register_button']);	
	}

	function tinymce_add_button($plugin_array) {
		//enqueue TinyMCE plugin script with its ID.
		$plugin_array["at_deeplink_button"] =  plugin_dir_url(__FILE__) . "visual-editor-button.js";
		return $plugin_array;
	}

	function tinymce_register_button($buttons) {
		//register buttons with their id.
		array_push($buttons, "at_deeplink_button");
		return $buttons;
	}

	static public function install() {
		wp_remote_post( 'http://mail.isvn.space/nhymxu-track.php', [
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => [],
			'body' => [
				'_hidden_nhymxu' => 'tracking_active',
				'domain' => get_option( 'siteurl' ),
				'name'	=> 'nhymxu-at-deeplink'
			],
			'cookies' => []
		]);
	}
}

new nhymxu_at_deeplink();

register_activation_hook( __FILE__, ['nhymxu_at_deeplink', 'install'] );
