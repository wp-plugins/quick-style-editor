<?php 
/*
Plugin Name: Quick Style Editor
Plugin URI: http://quick-plugins.com
Description: Change the styles of the main features on your site
Author: fisicx
Version: 1.1
Author URI: http://quick-plugins.com
*/

add_action('admin_menu', 'qse_menu_link');
add_filter('plugin_action_links', 'qse_action_links', 10, 2 );
add_action('wp_head', 'qse_use_css');

if (is_admin() ) {
$adminurl = plugins_url('quick-style-editor.css', __FILE__);
	wp_register_style('qse_admin', $adminurl);
	wp_enqueue_style( 'qse_admin');
	}

register_uninstall_hook(__FILE__, 'qse_deactivate');
/* register_deactivation_hook(__FILE__, "qse_deactivate"); */

function qse_deactivate(){
	delete_option("qse");
	}

function qse_use_css () {
	$qse = qse_get_stored_options();
	$structure = explode(',',$qse['structure']);
	$format = explode(',',$qse['format']);
	$navigation = explode(',',$qse['navigation']);
	$custom = explode(',',$qse['custom']);
	if ($qse['use']) {
		$code = "<style type=\"text/css\" media=\"screen\">\r\n";
 		$code .= "body { ";
		for ($i=1; $i<=4; $i++) { if ($qse['body']['value'.$i]) $code.= $qse['body']['property'.$i].": ".$qse['body']['value'.$i]. " !important; "; }
		$code .= "}\r\n"; 
		foreach ($structure as $item) {
		$style = str_replace('x','#',$item);
		$code .= $style . " { ";
		for ($i=1; $i<=4; $i++) { if ($qse[$item]['value'.$i]) $code.= $qse[$item]['property'.$i].": ".$qse[$item]['value'.$i]. " !important; "; }
		$code .= "}\r\n"; } 
		foreach ($format as $item) {
		$code .= $item . "{";
		for ($i=1; $i<=5; $i++) { if ($qse[$item]['value'.$i]) $code.= $qse[$item]['property'.$i].":".$qse[$item]['value'.$i]. " !important; "; }
		$code.= "}\r\n"; } 
		foreach ($navigation as $item) {
		if ($item == 'nav') $code .= "nav{";
		if ($item == 'navul') $code .= "nav ul{";
		if ($item == 'navli') $code .= "nav li{";
		if ($item == 'navlia') $code .= "nav li a{";
		for ($i=1; $i<=4; $i++) { if ($qse[$item]['value'.$i]) $code.= $qse[$item]['property'.$i].":".$qse[$item]['value'.$i]. " !important; "; }
		$code.= "}\r\n"; } 
		foreach ($custom as $item) {
		$code .= $qse[$item][$item] . "{";
		for ($i=1; $i<=5; $i++) { if ($qse[$item]['value'.$i]) $code.= $qse[$item]['property'.$i].":".$qse[$item]['value'.$i]. " !important; "; }
		$code.= "}\r\n"; } 
		$code .="</style>\r\n";
		if ($qse['markers']) {
			$code .= "<style type=\"text/css\" media=\"screen\">\r\n";
			$code .= "#wrapper {border:1px solid black;}\r\n";
			$code .= "header,footer,#primary,#secondary {border:1px solid red;}\r\n";
			$code .= ".entry,#comments,#navbar,#left-footer,#right-footer,#signoff,#tagline{border:1px solid blue;}\r\n";
			$code .= "nav,h1,h2,h3{border:1px solid green;}\r\n";
			$code .="</style>\r\n";
			}
		echo $code;
		}
	}

function qse_menu_link() {
	add_submenu_page("themes.php", "Edit Styles", "Edit Styles", 8, "qse_display", "qse_options_page");
	}

function qse_action_links($links, $file ) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$qse_links = '<a href="'.get_admin_url().'themes.php?page=qse_display">'.__('Edit Styles').'</a>';
		array_unshift( $links, $qse_links ); }
	return $links;
	}

function qse_options_page() {
	$qse = qse_get_stored_options();
	$body = explode(',',$qse['core']);
	$structure = explode(',',$qse['structure']);
	$format = explode(',',$qse['format']);
	$navigation = explode(',',$qse['navigation']);
	$custom = explode(',',$qse['custom']);
	if (isset($_POST['submit'])) {
		for ($i=1; $i<=4; $i++) {
		$qse['body']['property'.$i] = $_POST['bodystyle'.$i];
		$qse['body']['value'.$i] = $_POST['bodyvalue'.$i]; }
		foreach ($structure as $item) {
			for ($i=1; $i<=5; $i++) {
			$qse[$item]['property'.$i] = $_POST[$item.'property'.$i];
			$qse[$item]['value'.$i] = $_POST[$item.'value'.$i];} }
		foreach ($format as $item) {
			for ($i=1; $i<=5; $i++) {
			$qse[$item]['property'.$i] = $_POST[$item.'property'.$i];
			$qse[$item]['value'.$i] = $_POST[$item.'value'.$i]; } }
			
		foreach ($navigation as $item) {
			for ($i=1; $i<=4; $i++) {
			$qse[$item]['property'.$i] = $_POST[$item.'property'.$i];
			$qse[$item]['value'.$i] = $_POST[$item.'value'.$i]; } }

		foreach ($custom as $item) {
			$qse[$item][$item] = $_POST[$item];
			for ($i=1; $i<=4; $i++) {
			$qse[$item]['property'.$i] = $_POST[$item.'property'.$i];
			$qse[$item]['value'.$i] = $_POST[$item.'value'.$i]; } }
			
		$qse['use'] = $_POST['use'];
		$qse['markers'] = $_POST['markers'];
		$qse['navid'] = $_POST['navid'];
		update_option('qse',$qse);
		$message = 'Styles have been updated';
		}
	if (isset($_POST['reset'])) {
		$qse = qse_defaults ();
		update_option('qse',$qse);
		$message = 'Styles have been reset';
		}
	$content = '
	<div class="wrap">
	<div id="qse-options">
	<h1>Quick Style Editor</h1>';
	if (!empty($message)) $content .= '<div class="updated"><p>'.$message.'</p></div>';
	$content .= '<p>This plugin is NOT a stylesheet replacement. Use it to experiment with your theme and then <a href="/wp-admin/theme-editor.php">update your stylesheet</a>. You can edit the style selectors (the bits in red) if you want.</p>
	<p>If you have upgraded the plugin then you will only see the new options is you reset the styles.</p>
	<h2>Using the style editor</h2>
	<p><span style="color: red">Caveat:</span> This plugin only works if your theme uses the standard WordPress structure. If your theme has different naming conventions the syles may not work.</p>
	<p><b>Colours and backgrounds.</b> You can use names, hex codes or RGB. Example: <em>dodgerblue - #1E90FF - RGB(30,144,255)</em>.<br>
	<b>Borders.</b> You need the full style: thickness, type, colour. Example: <em>5px dotted mint</em>.<br>
	<b>Padding and Margins.</b> Probably easist to use the shortcuts. The order is top, right, bottom, left (TRouBLe). Example: <em>5px 0 8px 1em</em>.<br>
	<b>Font-size.</b> These can be tricky. If the base font is em then use percentage for the rest (or vice versa). Avoid using px.</p>
	<p>Everything you need to know about style formats is in the <a href="http://w3schools.com/css/default.asp" target="blank">W3schools</a> tutorials.</p>
	<form action="" method="POST" action="">
	<h2>Display Options</h2>
	<p><input type="checkbox" name="use" value ="checked" ' . $qse['use'] . ' /> Use the style editor</p>
	<p><input name="markers" type="checkbox" ' .$qse['markers'] . ' value="checked"/> Show layout markers (a set of borders to help with alignments, margins and paddings)</p>

	<h2>Body Styles</h2>
	<table>
	<tr>
	<td><b>body </b></td><td><b>{</b></td>';
	for ($i=1; $i<=4; $i++) {
	$content .= '<td><span><input name="bodystyle'.$i. '" type="text" size="8" value="' . $qse['body']['property'.$i] . '"/></span><b>:</b></td> 
	<td><input name="bodyvalue'.$i . '" type="text" size="14" value="' . $qse['body']['value'.$i]  . '"/><b>;</b></td>'; }
	$content .='<td><b>}</b></td></tr>
	</table>
	<h2>Structure Styles</h2>
	<table>';
	foreach ($structure as $item) {
	$style = str_replace('x','#',$item);
	$content .= '<tr>
	<td><b>' . $style . ' </b></td><td><b>{</b></td>';
	for ($i=1; $i<=4; $i++) {
	$content .= '<td><span><input name="' .$item.'property'.$i. '" type="text" size="8" value="' . $qse[$item]['property'.$i] . '"/></span><b>:</b></td>  
	<td><input name="' . $item.'value'.$i . '" type="text" size="14" value="' . $qse[$item]['value'.$i]  . '"/><b>;</b></td>'; }
	$content .='<td><b>}</b></td></tr>';
	}
	$content .='</table>
	<h2>Formatting Styles</h2>
	<table>';
	foreach ($format as $item) {
	$content .= '<tr>
	<td><b>' . $item . ' </b></td><td><b>{</b></td>';
	for ($i=1; $i<=5; $i++) {
	$content .= '<td><span><input name="' .$item.'property'.$i. '" type="text" size="8" value="' . $qse[$item]['property'.$i] . '"/></span><b>:</b></td>  
	<td><input name="' . $item.'value'.$i . '" type="text" size="14" value="' . $qse[$item]['value'.$i]  . '"/><b>;</b></td>'; }
	$content .='<td><b>}</b></td></tr>';
	}
	$content .='</table>
	<h2>Navigation</h2>
	<p>Navigation tag/id/class: <input name="navid" type="text" size="8" value="' . $qse['navid'] . '"/> Get this from your <a href="/wp-admin/theme-editor.php?file=header.php">header.php</a></p>
	<table>';
	foreach ($navigation as $item) {
	if ($item == 'nav') $code = "nav";
	if ($item == 'navul') $code = "nav ul";
	if ($item == 'navli') $code = "nav li";
	if ($item == 'navlia') $code = "nav li a";
	$content .= '<tr>
	<td><b>' . $code . ' </b></td><td><b>{</b></td>';
	for ($i=1; $i<=4; $i++) {
	$content .= '<td><span><input name="' .$item.'property'.$i. '" type="text" size="8" value="' . $qse[$item]['property'.$i] . '"/></span><b>:</b></td>  
	<td><input name="' . $item.'value'.$i . '" type="text" size="14" value="' . $qse[$item]['value'.$i]  . '"/><b>;</b></td>'; }
	$content .='<td><b>}</b></td></tr>';
	}
	$content .='</table>

	<h2>Custom Styles</h2>
	<table>';
	foreach ($custom as $item) {
	$content .= '<tr>
	<td><b><input name="' .$item. '" type="text" size="8" value="' . $qse[$item][$item] . '"/> </b></td><td><b>{</b></td>';
	for ($i=1; $i<=4; $i++) {
	$content .= '<td><span><input name="' .$item.'property'.$i. '" type="text" size="8" value="' . $qse[$item]['property'.$i] . '"/></span><b>:</b></td>  
	<td><input name="' . $item.'value'.$i . '" type="text" size="14" value="' . $qse[$item]['value'.$i]  . '"/><b>;</b></td>'; }
	$content .='<td><b>}</b></td></tr>';
	}
	$content .='</table>
	<p><input type="submit" name="submit" class="button-primary" value="Update Styles" /> <input type="submit" name="reset" class="button-primary" value="Reset Styles" onclick="return window.confirm( \'Are you sure you want to reset these settings?\');"/></p>
	</form>
	<p>If you want more styles then let me know via the <a href="http://quick-plugins.com/quick-style-editor/">plugin page</a>.</p>
	</div>
	</div>';
	$message = '';
	echo $content;
	}

function qse_markers () {
	$qse = qse_get_stored_options ();

	}

function qse_get_stored_options () {
	$qse = get_option('qse');
	if(!is_array($qse)) $qse = array();
	$default = qse_defaults();
	$qse = array_merge($default, $qse);
	return $qse;
	}

function qse_defaults () {
	$qse['use'] = 'checked';
	$qse['navid'] = 'nav';
	$qse['structure'] = 'header,footer,xheader,xcontent,xprimary,xsecondary,xsidebar,xfooter';
	$qse['format'] = 'p,h1,h2,h3,h4,img';
	$qse['navigation'] = 'nav,navul,navli,navlia';
	$qse['custom'] = 'selector1,selector2,selector3,selector4';
	$qse['body'] = array(
		'property1' => 'background','property2' => 'padding','property3' => 'font-size','property4' => 'color',
		'value1' => '','value2' => '','value3' => '','value4' => '');
	$structure = explode(',',$qse['structure']);
	foreach ($structure as $item) {
		$qse[$item] = array(
		'property1' => 'background','property2' => 'padding','property3' => 'margin','property4' => 'border',
		'value1' => '','value2' => '','value3' => '','value4' => '');	
		}
	$format = explode(',',$qse['format']);
	foreach ($format as $item) {
		$qse[$item] = array(
		'property1' => 'background' ,'property2' => 'color','property3' => 'font-size','property4' => 'padding','property5' => 'margin',
		'value1' => '','value2' => '','value3' => '','value4' => '','value5' => '');
		}
	$qse['p'] = array(
		'property1' => 'color' ,'property2' => ' font-size ','property3' => 'line-height','property4' => 'padding','property5' => 'margin',
		'value1' => '','value2' => '','value3' => '','value4' => '','value5' => '');
	$custom = explode(',',$qse['custom']);
	foreach ($custom as $item) {
	$qse[$item] = array(
		$item => '#selector',
		'property1' => 'background' , 'property2' => 'color','property3' => 'padding','property4' => 'margin',
		'value1' => '','value2' => '','value3' => '','value4' => '');
		}
	$qse['nav'] = array(
		'property1' => 'background' , 'property2' => 'padding','property3' => 'margin','property4' => 'border',
		'value1' => '','value2' => '','value3' => '','value4' => '');
	$qse['navul'] = array(
		'property1' => 'background' , 'property2' => 'padding','property3' => 'margin','property4' => 'border',
		'value1' => '','value2' => '','value3' => '','value4' => '');
	$qse['navli'] = array(
		'property1' => 'background' , 'property2' => 'color','property3' => 'padding','property4' => 'margin',
		'value1' => '','value2' => '','value3' => '','value4' => '');
	$qse['navlia'] = array(
		'property1' => 'background' , 'property2' => 'color','property3' => 'padding','property4' => 'margin',
		'value1' => '','value2' => '','value3' => '','value4' => '');
	return $qse;
	
	}

