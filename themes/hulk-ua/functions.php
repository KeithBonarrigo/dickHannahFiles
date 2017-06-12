<?php
/**
 * This is the only method I found which correctly works with multisite and domainmapper.
 * Only the default htaccess rules that come with WP are necessary, so lets just let
 * WP handle all the redirections internally.
 *
 * @author GHoffman
 * @see http://wordpress.org/support/topic/adding-rewrite-rules-and-wp_rewrite-gtflush_rules
 *
 * $Id: functions.php 1098 2012-05-29 23:31:00Z jgates@dickhannah.com $
 */

/*The following code adds the Store and Human Resources user roles with the capabilities 'read'
 *for both and 'manage_specials' and 'manage_jobs' respectively. This code only needs to run
 *once on each site and can then be commented out so that the server doesn't run it unnecessarily.
 */

//add_role( 'store', 'Store', array( 'read', 'manage_specials') );

//$role = get_role('store');
//$role -> add_cap('read');
//$role -> remove_cap('upload_files');

//end code to add user roles/capabilities

date_default_timezone_set('America/Los_Angeles');

function my_rewrite_rules( $rewrite_rules ) {
	
	$new_rules = array(

        "for-sale/([_0-9a-zA-Z]+)"                             => 'index.php?pagename=for-sale&stock_number=$matches[1]',
        "for-sale/([_0-9a-zA-Z]+)-([_0-9a-zA-Z-]+)"            => 'index.php?pagename=for-sale&stock_number=$matches[1]',
		
        "compliance-check/([_0-9a-zA-Z]+)"                             => 'index.php?pagename=compliance-check&stock_number=$matches[1]',
        "compliance-check/([_0-9a-zA-Z]+)-([_0-9a-zA-Z-]+)"            => 'index.php?pagename=compliance-check&stock_number=$matches[1]',

        "new/([_0-9a-zA-Z]+)/([_0-9a-zA-Z]+)/([0-9]+)"        => 'index.php?pagename=search&car-type=N&make=$matches[1]&model=$matches[2]&start-year=$matches[3]&end-year=$matches[3]',
        "new/([_0-9a-zA-Z]+)/([_0-9a-zA-Z]+)"                 => 'index.php?pagename=search&car-type=N&make=$matches[1]&model=$matches[2]',
        "new/([_0-9a-zA-Z]+)"                                 => 'index.php?pagename=search&car-type=N&make=$matches[1]',

        "used/([_0-9a-zA-Z]+)/([_0-9a-zA-Z]+)/([0-9]+)"       => 'index.php?pagename=search&car-type=U&make=$matches[1]&model=$matches[2]&start-year=$matches[3]&end-year=$matches[3]',
        "used/([_0-9a-zA-Z]+)/([_0-9a-zA-Z]+)"                => 'index.php?pagename=search&car-type=U&make=$matches[1]&model=$matches[2]',
        "used/([_0-9a-zA-Z]+)"                                => 'index.php?pagename=search&car-type=U&make=$matches[1]',

        "search/make/([_0-9a-zA-Z-]+)/year/([0-9]{4})"        => 'index.php?pagename=search&make=$matches[1]&year=$matches[2]',
        "search/make/([_0-9a-zA-Z-]+)"                        => 'index.php?pagename=search&make=$matches[1]' ,

        "search/model/([_0-9a-zA-Z-]+)/year/([0-9]{4})"       => 'index.php?pagename=search&model=$matches[1]&year=$matches[2]',
        "search/model/([_0-9a-zA-Z-]+)"                       => 'index.php?pagename=search&model=$matches[1]' ,

        "search/([_0-9a-zA-Z]+)/([_0-9a-zA-Z-]+)/([0-9]+)"    => 'index.php?pagename=search&car-type=&make=$matches[1]&model=$matches[2]&start-year=$matches[3]&end-year=$matches[3]',
        "search/([_0-9a-zA-Z]+)/([_0-9a-zA-Z-]+)"             => 'index.php?pagename=search&car-type=&make=$matches[1]&model=$matches[2]',
        "search/([_0-9a-zA-Z]+)"                              => 'index.php?pagename=search&car-type=&make=$matches[1]',
		
		"press-releases/([_0-9a-zA-Z-]+)/"               	  => 'index.php?pagename=press-releases&post_type=press_release&slug=$matches[1]' ,
        
        "sitemap.xml" => 'index.php?pagename=sitemap',

        );
	
    $rewrite_rules = $new_rules + $rewrite_rules;
    return $rewrite_rules;
}
add_filter('rewrite_rules_array', 'my_rewrite_rules');

function my_flush_rules()
{
    $rules = get_option( 'rewrite_rules' );

    if ( ! isset( $rules['(project)/(\d*)$'] ) ) {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }
}
/* add_action( 'wp_loaded','my_flush_rules' ); */
add_action( 'init','my_flush_rules' );


function vehicle_variables($public_query_vars) {
   $public_query_vars[] = 'stock_number';
   return $public_query_vars;
}
add_filter('query_vars', 'vehicle_variables');

function create_post_type() {
    global $dealership;
    register_post_type('press_release', array(
        'label' => 'Press Releases',
        'public' => true,
        'show_ui' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'rewrite' => array('slug' => 'press-releases', 'with_front' => false),
        'query_var' => true,
        'supports' => array(
            'title',
            'editor',
            'excerpt',
            'trackbacks',
            'custom-fields',
            'comments',
            'revisions',
            'thumbnail',
            'author',
            'page-attributes',
            )
        )
    );
	 register_post_type('car_dealership', array(
        'label' => 'Car Dealerships',
        'public' => true,
        'show_ui' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'rewrite' => array('slug' => 'auto-blog', 'with_front' => false),
        'query_var' => true,
        'supports' => array(
            'title',
            'editor',
            'excerpt',
            'trackbacks',
            'custom-fields',
            'comments',
            'revisions',
            'thumbnail',
            'author',
            'page-attributes',
            )
        )
    );
	if ($dealership['dealership_id'] == 9) {
		 register_post_type('subieslingers', array(
			'label' => 'Subieslingers',
			'public' => true,
			'show_ui' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'rewrite' => array('slug' => 'subieslingers', 'with_front' => false),
			'query_var' => true,
			'supports' => array(
				'title',
				'editor',
				'excerpt',
				'trackbacks',
				'custom-fields',
				'comments',
				'revisions',
				'thumbnail',
				'author',
				'page-attributes',
				)
			)
		);
		 register_post_type('subieslingerclint', array(
			'label' => 'Subieslinger Clint',
			'public' => true,
			'show_ui' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'rewrite' => array('slug' => 'subieslingerclint', 'with_front' => false),
			'query_var' => true,
			'supports' => array(
				'title',
				'editor',
				'excerpt',
				'trackbacks',
				'custom-fields',
				'comments',
				'revisions',
				'thumbnail',
				'author',
				'page-attributes',
				)
			)
		);	
	}
}

add_action( 'init', 'create_post_type' );

function post_update_alert_send_email( $post_id ) {
	if (get_post_type($post_id) == 'car_dealership' && get_post_status($post_id) == 'publish') {
		// The below code will only be executed if the post in question is of the car_dealership custom post type.

		// If this is just a revision, don't send the email.
		if ( wp_is_post_revision( $post_id ) )
			return;

		$post_title = get_the_title( $post_id );
		$post_url = get_permalink( $post_id );
		$subject = 'An Auto Blog post has been updated';

		$message = "An Auto Blog post has been updated on your website:\n\n";
		$message .= $post_title . ": " . $post_url;

		// Send email to admin.
		wp_mail( 'nhill@dickhannah.com, jschwanke@dickhannah.com', $subject, $message );
	}
}
add_action( 'save_post', 'post_update_alert_send_email' );

add_filter('nav_menu_css_class', 'special_nav_class', 10, 2);
/**
 * Navigation class addition
 * - identify top level menu items that contain sub-menus, function will add "has_subnav" class to the <LI>
 *
 * @param string $classes   Current string of LI classes
 * @param string $item      Inner content of the menu
 * @return string           String of LI classes
 */
function special_nav_class($classes, $item) {
    if($item->title == "Used" || $item->title == "Trade or Sell" || $item->title == "Finance" || $item->title == "Service Center" || $item->title == "Service & Parts" || $item->title == "Specials" || $item->title == "Contact Us" || $item->title == "\"Believe in nice\"") {
        $classes[] = "has_subnav";
    }
    if($item->title == "<img src=\"/wp-content/themes/dh_responsive/images/believe_in_nice_menu.png\" height=\"20\"/>") {
        $classes[] = "image_has_subnav";
    }
    return $classes;
}

function get_content($URL)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $URL);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar() {
	if (!current_user_can('edit_posts') && !is_admin()) {
	  show_admin_bar(false);
	}
}



// {{{ Video PLaylist Awesomeness
if ( ! function_exists( 'get_youtube_videos' ) ) :
function get_youtube_videos($playlist_id)
{
    global $dealership;
    $youtube_uri = "http://gdata.youtube.com/feeds/api/playlists/".$playlist_id."?alt=json";
   //$user = G_YOUTUBE_ACCT;
   //$youtube_uri = "http://gdata.youtube.com/feeds/api/users/{$user}/uploads?alt=json";

   $data = @json_decode(get_content($youtube_uri), true);

   $idx = 0;
   if (!empty($data['feed']) && !empty($data['entry'])) {
	   foreach($data['feed']['entry'] as $vid) {
		  $video_uri = $vid['media$group']['media$content'][0]['url'];
		  $video_thumb = $vid['media$group']['media$thumbnail'][0]['url'];

		  $video_fullDesc = addslashes($vid['media$group']['media$description']['$t']);
			$order   = array("\r\n", "\n", "\r");
			$replace = ' ';
			// Processes \r\n's first so they aren't converted twice.
			$video_desc = str_replace($order, $replace, $video_fullDesc);

		  $video_title = addslashes($vid['title']['$t']);
		  $video_keys = $vid['media$group']['media$keywords']['$t'];
		  //
		  // Filtering Goes Here
		  //

		  $js_out = "\nvideo_array[{$idx}] = {"
						.  "uri:'{$video_uri}',desc:'{$video_desc}',title:'{$video_title}',"
						.  "thumb:'{$video_thumb}'"
						.  "};";
		  echo $js_out;
		  $idx++;
	   }
	}
}
endif;
// }}} End Video Playlist Awesomeness

/*
#####################################
	Paid Ad Tracking Cookies
#####################################
*/
if (!empty($_REQUEST['gclid']) && $_REQUEST['gclid'] != "") {
	setcookie("dh8a775", "1", time()+60*60*24*30, "/"); // expires when browser is closed
	$_POST['dh8a775'] = "1"; // in case user submits a contact from the first page they visit
}
if ($_REQUEST['utm_medium'] == "cpc" AND $_REQUEST['utm_source'] == "bing") {
	setcookie("dh8a775", "2", time()+60*60*24*30, "/"); // expires when browser is closed
	$_POST['dh8a775'] = "2"; // in case user submits a contact from the first page they visit
}
/*
#####################################
	END Paid Ad Tracking Cookies
#####################################
*/

function dh_get_service_link() {
	global $dealership;
	if ($dealership['is_global']) {
		$make = "auto";
	} else {
		$make = strtolower($dealership['make']);
		if ($make == "volkswagen") {
			$make = "vw";
		}
	}
	$linkText = "/".$make."-service-";
		
	$city = $dealership['city'];
	if ($make == "vw") {
		$city = strtolower($dealership['city']);
	} else if ($dealership['is_global']) {
		$city = "portland-vancouver";
	} else if ($city == "Vancouver" || $city == "Portland") {
		$city = "portland-vancouver";
	} else if ($city == "Kelso") {
		$city = "longview-kelso";
	}

	$serviceLink = $linkText.$city;
	
	return $serviceLink;
}



/*
####################################
    USER UNIQUE ID
####################################

if (empty($_COOKIE['dhuid'])) {
	setcookie("dhuid", md5 ($_SERVER['REQUEST_TIME']." | ".rand()." | ".$_SERVER['REMOTE_ADDR']." | ".rand()), time()+60*60*24*30*12*5);
}
*/

/*
####################################
    MOBILE
####################################


if (!empty($_GET['mobileoverride']) AND $_GET['mobileoverride']!="yes") {
        
			//Turn off mobileoverride
            setcookie("dhmxd7", "N", time()+3600, "/"); // expires 1 hour
			$setMXD="N";
	}

 if(empty($_COOKIE['dhuim7']))
{
     // First time here? Lets see if you're mobile
    require_once(TEMPLATEPATH .'/Mobile_Detect.php');
    $user_agent = new Mobile_Detect();
    if ($user_agent->isMobile())
    {
			setcookie('dhuim7','Y',time()+3600, '/'); // DH User Is Mobile = YES 1 hour
			$dhuim = 'Y'; // For the current request
			$_COOKIE['dhuim7'] = 'Y'; // For the current request
    }
    else
    {
        setcookie('dhuim7','N',time()+3600, '/'); // DH User Is Mobile = NO 1 hour
        $dhuim = 'N'; // For the current request
        $_COOKIE['dhuim7'] = 'N'; // For the current request
    }
}

if (($_COOKIE['dhuim7']=='Y' OR (!empty($dhuim) AND $dhuim=='Y')) AND !is_mobile_site())
{
	//all of this code only runs if the user is mobile AND the current rendering page is NOT a mobile page
	if ($_GET['mobileoverride']=="yes") {
        {
			//Explicit override of mobile experience for campaign purposes
            setcookie("dhmxd7", "Y", time()+3600, "/"); // expires after 1 hour
        }
	}
    else
    {
		//placeholder variable in case we want to initiate the override in this code block
		
		if ($_GET['mobileoverride']=="yes") {
			$setMXD="Y";
		}
		else {
			$setMXD="N";
		}
			
		if(strpos($_SERVER['HTTP_REFERER'],"/mobile")!==false AND strpos($_SERVER['REQUEST_URI'],"/mobile")===false)
        {
			//The user clicked a link FROM a mobile page and are now trying to view a non-mobile page
			//We will set a cookie to override the mobile experience
            setcookie("dhmxd7", "Y", time()+3600, "/"); // expires in 1 hour
			$setMXD="Y";
        } else if (empty($_COOKIE['dhmxd7'])) {
			//If the cookie has not been set, let's set it to mobile mode by default
            setcookie("dhmxd7", "N", time()+3600, "/"); // expires in 1 hour
			$setMXD="N";
		}
		
    }
}
*/


 if(empty($_COOKIE['dhuim7']))
{
     // First time here? Lets see if you're mobile
    require_once(TEMPLATEPATH .'/Mobile_Detect.php');
    $user_agent = new Mobile_Detect();
    if ($user_agent->isMobile())
    {
			setcookie('dhuim7','Y',time()+3600, '/'); // DH User Is Mobile = YES 1 hour
			$dhuim = 'Y'; // For the current request
			$_COOKIE['dhuim7'] = 'Y'; // For the current request
    }
    else
    {
        setcookie('dhuim7','N',time()+3600, '/'); // DH User Is Mobile = NO 1 hour
        $dhuim = 'N'; // For the current request
        $_COOKIE['dhuim7'] = 'N'; // For the current request
    }
}

function is_mobile_site()
{
    if(strpos($_SERVER['REQUEST_URI'],"/mobile")===false) {
		//This is NOT a mobile page being served
		return false;
	} else {
		//This is a mobile page being served
		return true;
	}
}

function is_mobile()
{
    if(($_COOKIE['dhuim7']=='Y' OR (!empty($dhuim) AND $dhuim=='Y'))) {
		//This is on a mobile device
		return true;
	} else {
		//The user is not on a mobile device
		return false;
	}
}
/*
####################################
    GLOBAL
####################################
*/

$price_search_options = array(5000, 5500, 6000, 6500, 7000, 7500, 8000, 8500, 9000, 9500, 10000, 12500, 15000, 17500, 20000,  25000, 30000, 40000, 50000, 60000, 70000);
$year_search_options = array();
$current_year = date("Y");
for($i = $current_year - 20; $i < $current_year + 2; $i++)
array_push( $year_search_options, $i);

$dealership = get_row("

SELECT d.*, dpt.manager, dpt.phone, dpt.week_hours, dpt.sat_hours, dpt.sun_hours, gs.*
FROM dealership d JOIN global_settings gs
LEFT OUTER JOIN department dpt ON dpt.dealership_id=d.dealership_id AND dpt.name='Dealership' AND dpt.is_active=1
WHERE blog_id = ".get_current_blog_id()."
GROUP BY gs.global_settings_id "  ) ;

define( 'DEALERSHIP_ID', $dealership["dealership_id"]);
define( 'HEADER_IMAGE', '%s/images/logo.jpg' ); // The default logo located in themes folder
define( 'HEADER_IMAGE_WIDTH', apply_filters( '', 250 ) ); // Width of Logo
define( 'HEADER_IMAGE_HEIGHT', apply_filters( '',  64 ) ); // Height of Logo
define( 'NO_HEADER_TEXT', true );
add_custom_image_header( '', 'admin_header_style' ); // This Enables the Appearance > Header

dh_load_widgets();
add_action('init', 'register_dh_menus' );

register_sidebars( 1, array("name" => "Home" ) );
register_sidebars( 1, array("name" => "Search", 'before_title' => '<span class="h2_spoof widgettitle">', 'after_title' => '</span>' ) );
register_sidebars( 1, array("name" => "Main" ) );
register_sidebars( 1, array("name" => "Vehicle") );
register_sidebars( 1, array("name" => "Blog" ) );
register_sidebars( 1, array("name" => "Opinion" ) );
register_sidebars( 1, array("name" => "Home midLeft", 'before_widget' => '', 'after_widget'  => '', 'before_title' => '<span class="h2_spoof widgettitle">', 'after_title' => '</span>' ) );
register_sidebars( 1, array("name" => "Home midCenter", 'before_widget' => '', 'after_widget'  => '', 'before_title' => '<span class="h2_spoof widgettitle">', 'after_title' => '</span>' ) );
register_sidebars( 1, array("name" => "Home midRight", 'before_widget' => '', 'after_widget'  => '', 'before_title' => '<span class="h2_spoof widgettitle">', 'after_title' => '</span>' ) );
register_sidebars( 1, array("name" => "Search Landing Page" ) );
register_sidebars( 1, array("name" => "Service Revamp" ) );
if ($dealership['dealership_id'] == 1) {
register_sidebars( 1, array("name" => "Giving Trax" ) );
}

function add_home_option_to_pages( $args )
{
    $args['show_home'] = true;
    return $args;
}

add_filter( 'wp_page_menu_args', 'add_home_option_to_pages' );

function register_dh_menus()
{
    register_nav_menus( array(
		'header-menu' => __( 'Header Menu' ),
		'footer-menu' => __( 'Footer Menu' )
		)
	);
}

function dh_load_widgets(){
    foreach(glob(get_theme_root()."/hulk-ua/widgets/*.php") as $widget)
      include($widget);
}

function dh_nospace($input) {
    echo str_replace(array("\n", "\r", "\t"), "", $input);
}

/*
####################################
    META
####################################
*/

function get_master_page($post){
    $synchronized = get_post_custom_values('synchronized', $post->ID);

    if(empty($synchronized))
      return $post;

    switch_to_blog(1);
    $dh_post = get_page_by_title($post->post_title);
    return $dh_post;
}

function dh_h1($the_post=-1) {
    dh_custom_field('h1', $the_post);
}

function dh_title($the_post=-1) {
   global $vehicle;
   global $dealership;
   global $is_mobile;

   if(isset($vehicle)) {
	  $titleText = vehicle_type($vehicle)." {$vehicle["year"]} {$vehicle["make"]} {$vehicle["model"]}";
	   if($dealership["is_global"]=="0") {
			$this_dealership = $dealership; // If the dealership is not global we use dealership info from the site we are on.
		} else {
			$this_dealership = dh_get_dealership_from_stock_number($vehicle["stock_number"]); //if the dealership is global we use dealership info based on where the vehicle is.
		}
	
		$theCity = $this_dealership['city'];
	    if ($this_dealership['dealership_id'] == 10) {
		   $theCity = "Kelso near Vancouver";
	    }
	
	$titleText .= " ".$theCity;
	  
	  if ($is_mobile) {
		$titleText .= " Mobile ";
	  }
	  if (get_the_title() == "mobile images") {
		$titleText .= "Photos ";
	  }
	   $titleText .= " | ".$vehicle['stock_number'];
      echo str_replace('New ', '', $titleText);
	  
   } else if ( is_404() ) {
	  echo "Page Not Found - ".$dealership['name'];
   } else {
      dh_custom_field('title', $the_post);
   }
}

function dh_custom_field($field, $the_post=-1){
    global $post;
    $master_post = get_master_page($the_post > 0 ? $the_post : $post);
    $custom_field = get_post_custom_values($field, $master_post->ID);
    $proposed_title = empty($custom_field) ? get_the_title($master_post) : implode(' ', $custom_field);
    if($proposed_title=='Search' AND isset($_REQUEST['make'])){
        $keyword = ucwords($_REQUEST['make']);
        $final_title = $proposed_title.' Results for '.$keyword;
        if(isset($_REQUEST['model'])){
            $final_title .= ' '. ucwords($_REQUEST['model']);
        }
    }else{
        $final_title = $proposed_title;
    }
    echo $final_title;
    restore_current_blog();
}

function dh_content()
{
    global $post;
    $content = get_master_page($post)->post_content;
    $content = apply_filters('the_content', $content);
    $content = str_replace(']]>', ']]&gt;', $content);
    echo $content;
    restore_current_blog();
}

function permalink($page_name){
    $permalink = get_permalink(get_page_by_title($page_name));
    return $permalink;
}

/*
####################################
    AJAX
####################################
*/

add_action('wp_ajax_vehicle_search', 'vehicle_search');
add_action('wp_ajax_nopriv_vehicle_search', 'vehicle_search');

function vehicle_search()
{
	global $dealership;
		include("ajax/vehicle_search-drs.php");
    die();
}

add_action('wp_ajax_quick_search', 'quick_search');
add_action('wp_ajax_nopriv_quick_search', 'quick_search');

function quick_search()
{
    include("ajax/quick_search.php");
    die();
}

add_action('wp_ajax_thumbnail', 'thumbnail');
add_action('wp_ajax_nopriv_thumbnail', 'thumbnail');

function thumbnail()
{
    include("ajax/thumbnail.php");
    die();
}

/*
####################################
    DB
####################################
*/

function get_var($query)
{
    global $wpdb;
    return $wpdb->get_var($query);
}

function get_results($query)
{
    global $wpdb;
    return $wpdb->get_results($query, ARRAY_A);
}

function get_column($query) {
    global $wpdb;
    return $wpdb->get_col($query);
}

function get_row($query) {
    global $wpdb;
    return $wpdb->get_row($query, ARRAY_A);
}

if ( ! function_exists( 'admin_header_style' ) ) :
    function admin_header_style()
    {
        ?>
        <style type="text/css">
        #headimg {
        height: <?php echo HEADER_IMAGE_HEIGHT; ?>px;
        width: <?php echo HEADER_IMAGE_WIDTH; ?>px;
        }
        #headimg h1, #headimg #desc {
        display: none;
        }
        </style>
        <?php
    }
    endif;

function get_web_ads_by_slot($slot, $limit=0) {
	/* Can't delete this function because it will break the footer from the wp widget section */
}
	
function get_web_tiles_by_slot($slot, $limit=0) {
    global $dealership;
	$currentDate = date("Y-m-d");

   if ($limit!=0) {
      $limitline = "LIMIT ".$limit;
   } else {
      $limitline = "";
   }
    $sql = "SELECT * FROM web_tile wa JOIN dealership_web_tile dwa ON dwa.web_tile_id=wa.web_tile_id WHERE wa.slot='$slot' AND wa.is_active=1 AND dwa.dealership_id=".$dealership['dealership_id']." AND (wa.start_dt <= '$currentDate' OR wa.start_dt IS NULL OR wa.start_dt = '0000-00-00') AND (wa.expiration_dt > '$currentDate' OR wa.expiration_dt IS NULL OR wa.expiration_dt = '0000-00-00') ORDER BY ordernum ASC $limitline";
    return get_results($sql);
}

function get_auto_makes()
{
	global $dealership;
	
    $query = '';
    $query .= select_inventory("i.make, COUNT(*) number_available");
	$query .= apply_string_filter("i.dealer_id", "=", "location");
	if ($dealership['dealership_id'] == 12) {
		$query .= " AND i.model NOT LIKE 'e-Golf'"."\n";
	}
	
    $query .= "GROUP BY i.make\n";
    $query .= "ORDER BY i.make ASC\n";
    return get_results($query);
}

function get_new_makes()
{
	global $dealership;
	
    $query = '';
    $query .= select_inventory("i.make, COUNT(*) number_available, dealer_city, dealer_region");
	$query .= "AND new_used = 'N'\n";
	if ($dealership['dealership_id'] == 12) {
		$query .= " AND i.model NOT LIKE 'e-Golf'"."\n";
	}
	
	$query .= "GROUP BY i.make\n";
    $query .= "ORDER BY i.make ASC\n";
    return get_results($query);
}

function get_used_makes($certified = "")
{
	global $dealership;
	
    $query = '';
    $query .= select_inventory("i.make, COUNT(*) number_available");
	$query .= "AND new_used = 'U'\n";
	if ($certified == "certified") {
		$query .= "AND certified = 'Yes'\n";
	}
    $query .= "GROUP BY i.make\n";
    $query .= "ORDER BY i.make ASC\n";
    return get_results($query);
}

function get_used_models_by_make($make, $certified = "")
{
	global $dealership;

    $query = '';
    $query .= select_inventory("i.model, COUNT(*) number_available");
	$query .= "AND new_used = 'U'\n";
	$query .= "AND make = '{$make}'\n";
	if ($certified == "certified") {
		$query .= "AND certified = 'Yes'\n";
	}
    $query .= "GROUP BY i.model\n";
    $query .= "ORDER BY i.model ASC\n";
    return get_results($query);
}

function get_auto_models()
{
    global $dealership;

    $query = '';
    $query .= select_inventory("i.model, COUNT(*) number_available");
	$query .= apply_string_filter("make", "=", "make");
	$query .= apply_string_filter("i.dealer_id", "=", "location");
	
    $query .= "GROUP BY i.model\n";
    $query .= "ORDER BY i.model ASC\n";
    return get_results($query);
}

function get_auto_bodies()
{
    global $dealership;
	
    $query = '';
    $query .= select_inventory("i.body_type, COUNT(*) number_available");
    $query .= apply_string_filter("make", "=", "make");
    $query .= apply_string_filter("model", "=", "model");
	$query .= apply_string_filter("i.dealer_id", "=", "location");
    $query .= "GROUP BY i.body_type\n";
    $query .= "ORDER BY i.body_type ASC\n";
    return get_results($query);
}

function get_auto_trims()
{
    global $dealership;
	
    $query = '';
    $query .= select_inventory("i.trim, COUNT(*) number_available");
    $query .= apply_string_filter("make", "=", "make");
    $query .= apply_string_filter("model", "=", "model");
    $query .= apply_string_filter("body_type", "=", "body_type");
	$query .= apply_string_filter("i.dealer_id", "=", "location");
    $query .= "GROUP BY i.trim\n";
    $query .= "ORDER BY i.trim ASC\n";
    return get_results($query);
}

function get_auto_transmissions()
{
    global $dealership;
	
	$query = '';
    $query .= select_inventory("tt.transmission_type, COUNT(*) number_available");
    $query .= apply_string_filter("make", "=", "make");
    $query .= apply_string_filter("model", "=", "model");
    $query .= apply_string_filter("body_type", "=", "body_type");
	$query .= apply_string_filter("trim", "=", "trim");
	$query .= apply_string_filter("i.dealer_id", "=", "location");
    $query .= "GROUP BY transmission_type\n";
    $query .= "ORDER BY transmission_type ASC\n";
    return get_results($query);
}

function get_start_years() {
    global $year_search_options;
    if (!isset($_REQUEST['end-year']))
    {
        $_REQUEST['end-year']='';
    }
    $range = get_range($year_search_options, $year_search_options[0], $_REQUEST["end-year"]);
    return array_reverse($range);
}

function get_end_years() {
    global $year_search_options;
    if (!isset($_REQUEST['start-year']))
    {
        $_REQUEST['start-year']='';
    }
    $range = get_range($year_search_options, $_REQUEST["start-year"], $year_search_options[count($year_search_options)-1]);
    return array_reverse($range);
}

function get_min_prices(){
    global $price_search_options;
    if (!isset($_REQUEST['max-price']))
    {
        $_REQUEST['max-price']='';
    }
    return get_range($price_search_options, $price_search_options[0], $_REQUEST["max-price"]);
}

function get_max_prices(){
    global $price_search_options;
    if (!isset($_REQUEST['min-price']))
    {
        $_REQUEST['min-price']='';
    }
    return get_range($price_search_options, $_REQUEST["min-price"], $price_search_options[count($price_search_options)-1]);
}

function get_range($values, $min_value, $max_value){
    $result = array();
    for ($i=0; $i < count($values); $i++){
      if((empty($min_value) || ($values[$i] >= $min_value)) && (($values[$i] <= $max_value) || empty($max_value)))
         array_push($result, $values[$i]);
    }
    return $result;
}

function select_inventory_subselect(){
    global $dealership;
    $subselect = "SELECT dm.make FROM dealership_make dm WHERE dm.dealership_id=".$dealership['dealership_id']."
        UNION SELECT drm.make FROM dealership_relatedmake drm WHERE drm.dealership_id=".$dealership['dealership_id'];
    return $subselect;
}

function select_inventory($select_clause)
{
global $dealership;
/* // For the honda site:
      SELECT i.make, COUNT(*) number_available FROM dealership d
        JOIN inventory i ON i.new_used='U' OR d.is_global=true OR i.make IN ( SELECT dm.make FROM dealership_make dm WHERE dm.dealership_id=5
UNION SELECT drm.make FROM dealership_relatedmake drm WHERE drm.dealership_id=5 )
       WHERE d.blog_id = 6
         AND new_used = 'N'
     */
    $subselect = select_inventory_subselect();
    $query = "SELECT ".$select_clause." FROM dealership d\n";
    $query .= "JOIN inventory i ON i.new_used='U' OR d.is_global=true OR i.make IN ( $subselect )\n";
	$query .= "LEFT JOIN transmissions tt ON tt.transmission = i.transmission\n";
    $query .= "WHERE d.blog_id = ".get_current_blog_id()."\n";
    $query .= apply_string_filter("new_used", "=", "car-type");
    return $query;
}

function select_makes_in_stock()
{
	global $dealership;
    $subselect = select_inventory_subselect();
    $query = "SELECT i.make, COUNT(i.vin) AS total FROM inventory i";
	if($dealership["is_global"]!=1) {
		$query.=" WHERE i.make IN ( $subselect )";
	}
    $query.=" GROUP BY i.make ORDER BY total DESC LIMIT 6 ";
    return $query;
}

function dh_get_template_name() {
	$template = get_page_template();
	$array = explode("/", $template);
	$template = $array[count($array) - 1];
	
	return $template;
}

function dh_get_userinfo() {
	global $current_user;
	get_currentuserinfo();
	if (empty($current_user->user_login)) {
		$userInfo = "<p>Log in to view and edit your information.</p>";
	} else {
	
		$userInfo = "<div id=\"user_info_wrap\">";
		$oddRowStart =	"<div class=\"info_row odd_row\"><div class=\"info_label\">";
		$evenRowStart = "<div class=\"info_row even_row\"><div class=\"info_label\">";
		$rowMiddle = "</div><div class=\"info_value wordwrap\">";
		$rowEnd = "</div><div class=\"clearfix\"></div></div>";
		
		$userInfo .= $oddRowStart . 'Username: ' . $rowMiddle . $current_user->user_login . $rowEnd;
		$userInfo .= $evenRowStart . 'Student email: ' . $rowMiddle . $current_user->user_email . $rowEnd;
		$userInfo .= $oddRowStart . 'First name: ' . $rowMiddle . $current_user->user_firstname . $rowEnd;
		$userInfo .= $evenRowStart . 'Last name: ' . $rowMiddle . $current_user->user_lastname . $rowEnd;
		$userInfo .= $oddRowStart . 'Student phone: ' . $rowMiddle . $current_user->phone1 . $rowEnd;
		$userInfo .= $evenRowStart . 'School: ' . $rowMiddle . ucfirst($current_user->strive_school). " High School" . $rowEnd;
		if (!empty($current_user->write_in_school)) {
			$userInfo .= $evenRowStart . 'School: ' . $rowMiddle . $current_user->write_in_school . $rowEnd;
		}
		$userInfo .= $oddRowStart . 'Class: ' . $rowMiddle . $current_user->strive_class . $rowEnd;
		$userInfo .= $evenRowStart . 'Student birthdate: ' . $rowMiddle . $current_user->strive_birthdate . $rowEnd;
		$userInfo .= $oddRowStart . 'First GPA: ' . $rowMiddle . $current_user->strive_first_gpa . $rowEnd;
		$userInfo .= $evenRowStart . 'Final GPA: ' . $rowMiddle . $current_user->strive_final_gpa . $rowEnd;
		$userInfo .= $oddRowStart . 'Parent/Guardian email: ' . $rowMiddle . $current_user->strive_parent_email . $rowEnd;
		$userInfo .= $evenRowStart . 'Parent/Guardian name: ' . $rowMiddle . $current_user->strive_parent_name . $rowEnd;
		$userInfo .= $oddRowStart . 'Parent/Guardian phone: ' . $rowMiddle . $current_user->strive_parent_phone . $rowEnd;
		
		$userInfo .= "</div>";
	}
	return $userInfo;
}

  add_shortcode( 'User', 'dh_get_userinfo' );

function dh_get_dealership_from_make($make = '') {
   global $wpdb;
   if ($make != '') {
      $make = strtolower($make);
      $dealership = get_row("SELECT d.*, dpt.manager, dpt.phone, dpt.week_hours, dpt.sat_hours, dpt.sun_hours, gs.* FROM dealership d JOIN dealership_make ON dealership_make.dealership_id = d.dealership_id JOIN global_settings gs LEFT OUTER JOIN department dpt ON dpt.dealership_id=d.dealership_id AND dpt.name='Dealership' AND dpt.is_active=1 WHERE dealership_make.make LIKE '%".$make."%' ");
   } else {
      $dealership = get_row("SELECT d.*, dpt.manager, dpt.phone, dpt.week_hours, dpt.sat_hours, dpt.sun_hours, gs.* FROM dealership d JOIN global_settings gs LEFT OUTER JOIN department dpt ON dpt.dealership_id=d.dealership_id AND dpt.name='Dealership' AND dpt.is_active=1 WHERE blog_id = ".get_current_blog_id());
   }
   return $dealership;
}

function dh_get_make_from_dealership()
{
    global $dealership;
    if(isset($dealership['make'])){
        return $dealership['make'];
    }else{
        global $wpdb;
        $row = get_row("SELECT LOWER(dm.make) as make FROM dealership_make dm WHERE dm.dealership_id = ". $dealership['dealership_id']);
        $dealership['make']=$row['make'];
        return $dealership['make'];
    }
}

function dh_get_identifier() {
    global $dealership;
    
	if($dealership['dealership_id']==1) {
	$DealerIdentifier = "dh";
	} else if ($dealership['dealership_id']==2) {
	$DealerIdentifier = "acura";
	} else if ($dealership['dealership_id']==3) {
	$DealerIdentifier = "chrysler";
	} else if ($dealership['dealership_id']==4) {
	$DealerIdentifier = "dodge";
	} else if ($dealership['dealership_id']==5) {
	$DealerIdentifier = "honda";
	} else if ($dealership['dealership_id']==6) {
	$DealerIdentifier = "hyundai";
	} else if ($dealership['dealership_id']==7) {
	$DealerIdentifier = "jeep";
	} else if ($dealership['dealership_id']==8) {
	$DealerIdentifier = "kia";
	} else if ($dealership['dealership_id']==9) {
	$DealerIdentifier = "subaru";
	} else if ($dealership['dealership_id']==10) {
	$DealerIdentifier = "toyota";
	} else if ($dealership['dealership_id']==11) {
	$DealerIdentifier = "scion";
	} else if ($dealership['dealership_id']==12) {
	$DealerIdentifier = "vwv";
	} else if ($dealership['dealership_id']==13) {
	$DealerIdentifier = "vwp";
	} else if ($dealership['dealership_id']==16) {
	$DealerIdentifier = "dsy";
	}  else if ($dealership['dealership_id']==18) {
	$DealerIdentifier = "vtc";
	} else if ($dealership['dealership_id']==21) {
	$DealerIdentifier = "ram";
	} else if ($dealership['dealership_id']==23) {
	$DealerIdentifier = "nissan";
	}
	
	return $DealerIdentifier;
}

function dh_get_StoreIdentifier() {
    global $dealership;
    
	if($dealership['dealership_id']==1) {
	$StoreIdentifier = "Dick Hannah";
	} else if ($dealership['dealership_id']==2) {
	$StoreIdentifier = "Acura";
	} else if ($dealership['dealership_id']==3) {
	$StoreIdentifier = "Chrysler";
	} else if ($dealership['dealership_id']==4) {
	$StoreIdentifier = "Dodge";
	} else if ($dealership['dealership_id']==5) {
	$StoreIdentifier = "Honda";
	} else if ($dealership['dealership_id']==6) {
	$StoreIdentifier = "Hyundai";
	} else if ($dealership['dealership_id']==7) {
	$StoreIdentifier = "Jeep";
	} else if ($dealership['dealership_id']==8) {
	$StoreIdentifier = "Kia";
	} else if ($dealership['dealership_id']==9) {
	$StoreIdentifier = "Subaru";
	} else if ($dealership['dealership_id']==10) {
	$StoreIdentifier = "Toyota";
	} else if ($dealership['dealership_id']==11) {
	$StoreIdentifier = "Scion";
	} else if ($dealership['dealership_id']==12) {
	$StoreIdentifier = "Volkswagen";
	} else if ($dealership['dealership_id']==13) {
	$StoreIdentifier = "Volkswagen";
	} else if ($dealership['dealership_id']==16) {
	$StoreIdentifier = "Dick Says Yes";
	}  else if ($dealership['dealership_id']==18) {
	$StoreIdentifier = "Vancouver Truck Center";
	} else if ($dealership['dealership_id']==21) {
	$StoreIdentifier = "Dick Hannah Ram";
	}  else if ($dealership['dealership_id']==23) {
	$StoreIdentifier = "Dick Hannah Nissan";
	}
	
	return $StoreIdentifier;
}

function dh_get_make_from_dealership_id($id)
{
    $this_dealership = dh_get_dealership_from_id($id);;
    if(isset($this_dealership['make'])){
        return $this_dealership['make'];
    }else{
        global $wpdb;
        $row = get_row("SELECT LOWER(dm.make) as make FROM dealership_make dm WHERE dm.dealership_id = ". $this_dealership['dealership_id']);
        $this_dealership['make']=$row['make'];
        return $this_dealership['make'];
    }
}

function dh_get_domain_from_dealership()
{
    global $dealership;
    $row = get_row('SELECT b.domain FROM wp_blogs b JOIN dealership d ON d.blog_id = b.blog_id WHERE d.dealership_id = '. $dealership['dealership_id']);
    return $row['domain'];
}

function dh_get_domain_from_dealership_id($dealership_id)
{
    $row = get_row('SELECT b.domain FROM wp_blogs b JOIN dealership d ON d.blog_id = b.blog_id WHERE d.dealership_id = '. $dealership_id);
    return $row['domain'];
}


function dh_get_dealership_from_stock_number($stock_number = '')  {
   global $wpdb;
   if ($stock_number != '') {
        /*
      $dealership = get_row("SELECT d.*, dpt.manager, dpt.phone, dpt.week_hours, dpt.sat_hours, dpt.sun_hours
            FROM dealership d
            JOIN department dpt ON d.dealership_id = dpt.dealership_id
            JOIN inventory i ON d.dealer_id = i.dealer_id
            WHERE i.stock_number = '".$stock_number."' ");
            */

        $sql = "

            SELECT d.*, dpt.manager, dpt.phone, dpt.week_hours, dpt.sat_hours, dpt.sun_hours
            FROM dealership d
            JOIN department dpt ON d.dealership_id = dpt.dealership_id
            JOIN dealership_stockprefix dsp
            ON d.dealership_id = dsp.dealership_id
            JOIN inventory i
            ON ( LEFT('".$stock_number."', LENGTH(dsp.stockprefix)) = dsp.stockprefix AND dsp.new_used = i.new_used )
            WHERE i.stock_number = '".$stock_number."'
            ORDER BY LENGTH(dsp.stockprefix) DESC
            LIMIT 1

        ";

        //echo $sql;
        $dealership = get_row($sql);
   } else {
      $dealership = get_row("SELECT d.*, dpt.manager, dpt.phone, dpt.week_hours, dpt.sat_hours, dpt.sun_hours, gs.* FROM dealership d JOIN global_settings gs LEFT OUTER JOIN department dpt ON dpt.dealership_id=d.dealership_id AND dpt.name='Dealership' AND dpt.is_active=1 WHERE blog_id = ".get_current_blog_id());
   }
   return $dealership;

}


function dh_get_dealership_from_id($dealership_id)  {
	global $wpdb;
	$sql = "
		SELECT d.*, dpt.manager, dpt.phone, dpt.week_hours, dpt.sat_hours, dpt.sun_hours
		FROM dealership d
		JOIN department dpt ON d.dealership_id = dpt.dealership_id
		WHERE d.dealership_id = '".$dealership_id."'
		LIMIT 1

	";

	$dealership = get_row($sql);
	return $dealership;
}

function dh_get_phone_number() {
	global $dealership;
	global $vehicle;
	
	$phoneNumber = "";
	/*
		Currently only handles Generic, Mobile, and VDP phone numbers
	*/
	if(!$dealership['is_global']) {
		if(dh_get_template_name() == "vehicle.php") {
			$phoneNumber = $dealership['oaisys'];
		} else if (is_mobile()) {
			$phoneNumber = $dealership['oaisys_mobile'];
		} else {
			$phoneNumber = $dealership['phone'];
		}
	} else {
		if(dh_get_template_name() == "vehicle.php") {
			$this_dealership = dh_get_dealership_from_stock_number($vehicle["stock_number"]);
			$phoneNumber = $this_dealership['oaisys'];
		} else if (is_mobile()) {
			$phoneNumber = $dealership['oaisys_mobile'];
		} else {
			$phoneNumber = $dealership['phone'];
		}
	}
	
	return $phoneNumber;
}

function dh_get_bodytype_icon($new_used, $search_page, $body_type, $body_icon){
    if(dh_dealership_has_body_type($new_used, $body_type)){
    return
        "<a href=\"#\" onclick=\"set_search_type('$search_page','$body_type');return false;\"><img
        src=\"".image_url("cartypes/".$body_icon)."\" alt=\"".$body_type."\" border=\"0\"/><br/>
        ".$body_type."</a>";
    }else{
        return "&nbsp;";
    }
}

function dh_dealership_has_body_type($new_used,$body_type){
    // 'N', 'Pickup Trucks'
    global $dealership;
    $dealershipbodytypes = dh_get_bodytypes_for_dealership($new_used,$dealership['dealership_id']);
    foreach($dealershipbodytypes as $abodytype){
        if($abodytype['body_type'] == $body_type){
            return true;
        }
    }
    return false;
}

function dh_get_bodytypes_for_dealership($new_used='N',$dealership_id=1){
    global $dealership;
    global $wpdb;
    /*
     * The query below works, however joining on dealer_id produces strange results.
     * I think this is due to vauto data/feed errors...
     *
    $query = "SELECT bt.body_type, REPLACE(CONCAT(bt.icon,'.gif'),' ','-') AS icon FROM bodytypes bt
            JOIN inventory i ON bt.body_type = i.body_type
            JOIN dealership d ON i.dealer_id = d.dealer_id
            WHERE d.dealership_id = ".$dealership_id." AND bt.body_type != ''
            AND i.new_used = '".$new_used."'
            GROUP BY icon
             ";
     *
     */
    if($new_used=='N' && ! $dealership['is_global'] ) {
        $query = "SELECT bt.body_type, REPLACE(CONCAT(bt.icon,'.gif'),' ','-') AS icon FROM bodytypes bt
                JOIN inventory i ON bt.body_type = i.body_type
                JOIN dealership_make dm ON i.make = dm.make
                JOIN dealership d ON dm.dealership_id = d.dealership_id
                WHERE d.dealership_id = ".$dealership_id." AND bt.body_type != '' AND bt.body_type != 'RV'
                AND i.new_used = '".$new_used."'
                GROUP BY icon
                 ";
    }else{
        $query = "SELECT bt.body_type, REPLACE(CONCAT(bt.icon,'.gif'),' ','-') AS icon FROM bodytypes bt
                JOIN inventory i ON bt.body_type = i.body_type
                JOIN dealership_make dm ON i.make = dm.make
                JOIN dealership d ON dm.dealership_id = d.dealership_id
                WHERE bt.body_type != '' AND bt.body_type != 'RV'
                AND i.new_used = '".$new_used."'
                GROUP BY icon
                 ";
    }
    $dbresults = get_results($query);
    $i=0;
    foreach($dbresults as $row){
        if($row['body_type']=='Chassis'){
            $results[$i]['body_type']='Truck';
        }else{
            $results[$i]['body_type']=$row['body_type'];
        }
        $results[$i]['icon']=$row['icon'];
        $i++;
    }
    return $results;
}
#############################################################################################
#ChromeStyles Incentive classes/functions
#Used to retrieve and display vehicle incentive info
#written by Keith Bonarrigo 4/20/2016
#############################################################################################
#inline functions for front end script
#############################################################################################
//this is the base front end UI function that checks the result set
//it accepts the $incentives array and checks to see if a nested array exists for the model it's interating on
//if the active exists, it should nest the incentive in with the the appropriate year
//if the year doesn't exist, it should record the active year and then set up the data in the same linear format for that model, but adding the variables for that new year
//accepts: array $row (the database resource), array $incentives, integer $dealership_id
//returns: $incentives
######################################################
function checkIncentives($row, $incentives, $dealership_id){ 
	
	$found = 0; //this is a counter to mark whether we have found this model marked in the master array yet
	$count = 0; //this is a counter that increments whether the model was found in the array
	
	foreach($incentives as $k=>$v){ //loop through and set a flag to show if that model already exists or not
		if( array_key_exists('model', $v) && $incentives[$k]['model'] == $row["Model"]){
			$found++;
		} //end if
		$count++;
	} //end for
	
	if($found==0){ //we dont have this model set up yet - create it
		$nextKey = $count+1;
		$newCar = array('IncentiveId'=>$row['cs_IncentinveInfo'],'model'=>$row['Model'], 'dealership_id'=>$dealership_id);
		$thisUrl = checkImageUrl($row['Model'], $row['Year'], $dealership_id); //check the 'model' for the image info for this year/model

		$newCar['global_cta_url'] = $thisUrl[0]['url'];
		$newCar['global_is_active'] = 1;
		$newCar['rawData'] = array(); //this is a container for basic data stats like make and the years we've organized into the variable set. its for reference in organizing the data into an inline format
		$newCar['rawData']['make'] = $row['Division'];
		$newCar = checkYear($newCar, $row, $nextKey, $thisUrl); //check to see if we've combined in the year for this particular incentive yet
		$incentives[$nextKey] = $newCar;	
	}else{ //we do have this model already
		//determine where we are in the array
		///////////
		foreach($incentives as $incentiveKey=>$incentiveArray){
			if($incentives[$incentiveKey]['model'] === $row["Model"]){ //find the model
				$idWereOn = $incentiveKey; //this is the key that we're on in the larger incentive array - the key that applies to this model
			} //if
		} //for
		///////////
		$thisUrl = checkImageUrl($row['Model'], $row['Year'], $dealership_id);
		$newCar = checkYear($incentives[$idWereOn], $row, $idWereOn, $thisUrl); //we now have to go in and check that 
		$incentives[$idWereOn] = $newCar;
	}
	return $incentives;
} 
######################################################
function checkNameOverride($incentive){
	$sql = "SELECT * FROM cs_ModelNameException WHERE cs_ModelNameAis = '".$incentive['model']."' AND cs_DivisionID = ".$incentive['dealership_id']." LIMIT 1";
	$res = get_results($sql);
		if(count($res)>0){
			$incentive['model'] = $res[0]['cs_ModelNameHannah'];
		}
	return $incentive;
} //end function
######################################################
######################################################
//checks for the registered index  of a model in the incentives array
//accepts a database result row
//returns the array index if this model exists in the $incentives array
######################################################
function checkModelKey($v, $incentives){
	foreach($incentives as $k1=>$v1){
		if($v['Model'] === $v1['model']){
			return $k1;
		} //end if
	} //end for
}
######################################################
//cheks the model array to see if this year is already registered under this model
//accepts a database result row
//returns the array index if this year exists in the incentive array
######################################################
function findYearKey($indiv, $incentives){
	$returnYear = ""; //this is a placeholder for the year index in the rawData array
	
	foreach($incentives as $k=>$v){	//loop through the larger incentive object
		
		if($v['model'] === $indiv['Model']){ //get to the model that we're on
			foreach($v['rawData']['years'] as $k1=>$v1){ //find the index of the year that we're on
				if($v1 === $indiv['Year']){ //this is the year of the incentive that we're on
					$returnYear = $k1;
					return $returnYear;
				} //if
			} //foreach
		} //if
	} //foreach
	
	if(strlen($returnYear)<1){ //we don't have this incentive year on file yet so we need to insert it to return the id
		foreach($incentives as $k=>$v){ //loop through the incentives again
			if($v['model'] === $indiv['Model']){ //this is the model that we're dealing with
				$theKeys = array_keys($v['rawData']['years']); //get the current keys for the years in this model's incentives
				$theKeysCount = count($theKeys); //get a basic count so we know what to increment
				$theNextCount = $theKeysCount+1; //get the next integer to assign as a key to the array
				$v['rawData']['years'][$theNextCount] = $indiv['Year']; //insert the year to this model's year array
				//$theKeys = array_keys($v['rawData']['years']); 
				return $theNextCount;
			}
		} //end foreach
	} //end if
	
}
######################################################
//this is where we loop through the object to try to match the '1_1 or 2_1 naming convention to know where to put the current incentive into the single row convention
######################################################
function checkIncentiveOrderNumber($modelKey, $thisYearKey, $indiv, $incentives){ 
	$nextItem = 1;
		foreach($incentives[$modelKey] as $k=>$v){
			$test = 'item_'.$thisYearKey;
			if(strstr($k, $test)) $nextItem++;
		}
	$incentiveToPlace = "item_".$thisYearKey."_".$nextItem;
	$bulletToPlace = "bullet_".$thisYearKey."_".$nextItem;
	$disclaimerToPlace = "item_disclaimer_".$thisYearKey."_".$nextItem;
	$incentives[$modelKey][$incentiveToPlace] = $indiv['ProgramText'];
	$incentives[$modelKey][$bulletToPlace] = $indiv['Bullet1'];
	$incentives[$modelKey][$disclaimerToPlace] = $indiv['Disclaimer'];
	$incentives[$modelKey][$bulletToPlace] = $indiv['Bullet1'];
	return $incentives;
}
######################################################
//this checks the result set against the rawData array that we store to see where in the variable set this fits - into what year
//it arranges the current in the right naming convention like 1_1 or 2_1 and returns the incentive array
######################################################
function checkRaw($modelKey, $indiv, $incentives){
	$thisYearKey = findYearKey($indiv, $incentives);
	$incentives = checkIncentiveOrderNumber($modelKey, $thisYearKey, $indiv, $incentives);
	return $incentives;
}
######################################################
//this appends the year that we're dealing with to the rawData array that we use to filter back through the data in a later pass to arrange the incentives into a single row format
######################################################
function checkYear($newCar, $indivIncent, $index, $thisUrl){	
	
	$counter = 0; //simple loop counter
	$yearCounter = 0; //this is the number we'll append to the field name if we need to create a new key
	$yearCounterArray = array(); //this is the array we'll use to track the years that we've already stored
	
	foreach($newCar as $k=>$v){ //check this particular incentive to see if this year has been recorded for this model

		if(strstr($k, 'model_year_')){
			$yearCounter++;
			$yearCounterArray[$yearCounter]=$v;
		} //end if
		$counter++;
		
	} //end for
	$newCar['rawData']['years'] = $yearCounterArray; //add the year array to this model
	
	
	if(count($yearCounterArray)==0){ //we don't a year array registered so we need to add the years array to the model and populate it
		$newCar['rawData']['years'][1] = $indivIncent['Year'];
	}
	
	//we're done with the loop to check for model_year_X values - now check to see if we found any
	if($yearCounter == 0){ //we didn't find any other years yet so set up the first key
		$fieldNameNumber = $yearCounter+1; 
		$fieldName = 'model_year_'.$fieldNameNumber;
		$imageName = 'image_url_'.$fieldNameNumber;
		$newCar[$fieldName] = $indivIncent['Year']; //add this year in the linear format that the model had originally
		$fullImagePath = dh_upload_path("baseurl", "Model", $thisUrl[0], 'image_file'); //go to the dickhannah plugin functions to fetch the model specific info
		$isActiveField = 'is_active_'.$fieldNameNumber;
		$newCar[$isActiveField] = 1;
	
	}else{ //we do have the model - now we have to see if the year exists in the array 
		if(!in_array($indivIncent['Year'], $yearCounterArray)){ //we need to set up the year
		  $yearCounterUp = $yearCounter + 1; 
		  $fieldName = 'model_year_'.$yearCounterUp;
		  $imageName = 'image_url_'.$yearCounterUp;
		  $isActiveField = 'is_active_'.$yearCounterUp;
		  $newCar[$fieldName] = $indivIncent['Year']; 
		  $fullImagePath = dh_upload_path("baseurl", "Model", $thisUrl[0], 'image_file'); //go to the dickhannah plugin functions to fetch the model specific info 		  
		  $newCar[$isActiveField] = 1;
		}
	}
	########################
	#this is put here temporarily for dev purposes so that the images show up, pulling from the live server
	$trimmedImagePath = str_replace("dev.", "", $fullImagePath);
	$fullImagePath = $trimmedImagePath;
	########################
	$newCar[$imageName] = $fullImagePath; //add this image path we've retrieved to the individual car array for that year				  
	return $newCar;
}
######################################################
//this checks the model table for the presence of an image for this vehicle 
//returns the resultset if successful
######################################################
function checkImageUrl($model, $year, $dealershipId){
	$sql = "SELECT * FROM model WHERE model_year = $year and Model = '".$model."' and dealership_id=$dealershipId LIMIT 1";	
	$urlRes = get_results($sql);
	$returnArray = array();
	foreach($urlRes as $k=>$v){ //copy the result set from the model table in an exact key to value format
			$returnArray[$k]=$v;
	}
	return $returnArray; //return the result set back 
}
#############################################################################################
#end front end inline functions
#############################################################################################
$inc = get_theme_root()."/hulk-ua/chromeStyleIncludes/chromeStylesClasses.php";
require_once($inc);
//////////////////////////////////////////////
//////////////////////////////////////////////
function getUiQuery($dealership){
	global $wpdb; //get our global database connection object
	$nameSql = "Select * FROM cs_offerlogixDivision WHERE divisionId = ".$dealership['dealership_id']." LIMIT 1";
	//echo $nameSql;
	$makeNameRes = get_results($nameSql);
	$makeName = ucfirst(strtolower($makeNameRes[0]['divisionName']));
	
	//echo $makeName;
	///////
	$atts = shortcode_atts(array('mode' => 'nomode', 'visibleIncentives'=>'none'), $atts, 'acurachromeincentives' );
	$thisIncentiveBatch = new VehicleIncentiveGroup();
	$thisIncentiveBatch->division = $makeName;
	$thisIncentiveBatch->divisionId = $makeNameRes[0]['divisionID'];
	//$thisIncentiveBatch->weblink = "www.dickhannahacuraofportland.com";
	//$thisIncentiveBatch->year = "2016";
	$sqlToReturn = $thisIncentiveBatch->createSql();
	//echo $sqlToReturn;
	
	return $sqlToReturn;
	///////
}
//////////////////////////////////////////////
//////////////////////////////////////////////
add_shortcode('acurachromeincentives', 'show_acura_cs_incentives');
add_shortcode('chrylserchromeincentives', 'show_chrysler_cs_incentives');
add_shortcode('dodgechromeincentives', 'show_dodge_cs_incentives');
add_shortcode('hondachromeincentives', 'show_honda_cs_incentives');
add_shortcode('hyundaichromeincentives', 'show_hyundai_cs_incentives');
add_shortcode('jeepchromeincentives', 'show_jeep_cs_incentives');
add_shortcode('kiachromeincentives', 'show_kia_cs_incentives');
add_shortcode('subaruchromeincentives', 'show_subaru_cs_incentives');
add_shortcode('toyotachromeincentives', 'show_toyota_cs_incentives');
add_shortcode('scionchromeincentives', 'show_scion_cs_incentives');
add_shortcode('volkswagenchromeincentives', 'show_volkswagen_cs_incentives');
add_shortcode('ramchromeincentives', 'show_ram_cs_incentives');
add_shortcode('nissanchromeincentives', 'show_nissan_cs_incentives');
/////////////////////////////////////////////
/////////////////////////////////////////////
function show_acura_cs_incentives($atts){ //shows ChromeStyles incentives for Acura division
	$atts = shortcode_atts(array('mode' => 'nomode', 'visibleIncentives'=>'none'), $atts, 'acurachromeincentives' );
	$thisIncentiveBatch = new VehicleIncentiveGroup();
	$thisIncentiveBatch->division = "Acura";
	$thisIncentiveBatch->divisionId = 2;
	$thisIncentiveBatch->weblink = "www.dickhannahacuraofportland.com";
	$thisIncentiveBatch->year = "2016";
	$IncentiveOutput = $thisIncentiveBatch->getIncentives($atts);
	
	if($atts['mode'] == "ui"){ $thisIncentiveBatch->showHtml(); }
	if($atts['mode'] == "plugin"){ $thisIncentiveBatch->showPluginPreview($atts['visibleIncentives']); }
}
/////////////////////////////////////////////
/////////////////////////////////////////////
function show_chrysler_cs_incentives($atts){ //shows ChromeStyles incentives for Dodge division
	$atts = shortcode_atts(array('mode' => 'nomode', 'visibleIncentives'=>'none'), $atts, 'chryslerchromeincentives' );
	$thisIncentiveBatch = new VehicleIncentiveGroup();
	$thisIncentiveBatch->division = "Chrysler";
	$thisIncentiveBatch->divisionId = 3;
	$thisIncentiveBatch->weblink = "/www.hannahchryslerjeep.com/";
	$thisIncentiveBatch->year = "2016";
	$IncentiveOutput = $thisIncentiveBatch->getIncentives();
	
	if($atts['mode'] == "ui"){ $thisIncentiveBatch->showHtml(); }
	if($atts['mode'] == "plugin"){ $thisIncentiveBatch->showPluginPreview($atts['visibleIncentives']); }
}
/////////////////////////////////////////////
/////////////////////////////////////////////
function show_dodge_cs_incentives($atts){ //shows ChromeStyles incentives for Dodge division
	$atts = shortcode_atts(array('mode' => 'nomode', 'visibleIncentives'=>'none'), $atts, 'dodgechromeincentives' );
	$thisIncentiveBatch = new VehicleIncentiveGroup();
	$thisIncentiveBatch->division = "Dodge";
	$thisIncentiveBatch->divisionId = 4;
	$thisIncentiveBatch->weblink = "www.dickhannahdodge.com";
	$thisIncentiveBatch->year = "2016";
	$IncentiveOutput = $thisIncentiveBatch->getIncentives();
	
	if($atts['mode'] == "ui"){ $thisIncentiveBatch->showHtml(); }
	if($atts['mode'] == "plugin"){ $thisIncentiveBatch->showPluginPreview($atts['visibleIncentives']); }
}
/////////////////////////////////////////////
/////////////////////////////////////////////
function show_honda_cs_incentives($atts){ //shows ChromeStyles incentives for Honda division
	$atts = shortcode_atts(array('mode' => 'nomode', 'visibleIncentives'=>'none'), $atts, 'hondachromeincentives' );
	$thisIncentiveBatch = new VehicleIncentiveGroup();
	$thisIncentiveBatch->division = "Honda";
	$thisIncentiveBatch->divisionId = 5;
	$thisIncentiveBatch->weblink = "www.dickhannahhonda.com";
	$thisIncentiveBatch->year = "2016";
	$IncentiveOutput = $thisIncentiveBatch->getIncentives();
	
	if($atts['mode'] == "ui"){ $thisIncentiveBatch->showHtml(); }
	if($atts['mode'] == "plugin"){ $thisIncentiveBatch->showPluginPreview($atts['visibleIncentives']); }
}
/////////////////////////////////////////////
/////////////////////////////////////////////
function show_hyundai_cs_incentives($atts){ //shows ChromeStyles incentives for Honda division
	$atts = shortcode_atts(array('mode' => 'nomode', 'visibleIncentives'=>'none'), $atts, 'hyundaichromeincentives' );	
	$thisIncentiveBatch = new VehicleIncentiveGroup();
	$thisIncentiveBatch->division = "Hyundai";
	$thisIncentiveBatch->divisionId = 6;
	$thisIncentiveBatch->weblink = "www.hyundaiofportland.com";
	$thisIncentiveBatch->year = "2016";
	$IncentiveOutput = $thisIncentiveBatch->getIncentives();
	
	if($atts['mode'] == "ui"){ $thisIncentiveBatch->showHtml(); }
	if($atts['mode'] == "plugin"){ $thisIncentiveBatch->showPluginPreview($atts['visibleIncentives']); }
}
/////////////////////////////////////////////
/////////////////////////////////////////////
function show_jeep_cs_incentives($atts){ //shows ChromeStyles incentives for Honda division
	$atts = shortcode_atts(array('mode' => 'nomode', 'visibleIncentives'=>'none'), $atts, 'jeepchromeincentives' );	
	$thisIncentiveBatch = new VehicleIncentiveGroup();
	$thisIncentiveBatch->division = "Jeep";
	$thisIncentiveBatch->divisionId = 7;
	$thisIncentiveBatch->weblink = "www.dickhannahjeep.com";
	$thisIncentiveBatch->year = "2016";
	$IncentiveOutput = $thisIncentiveBatch->getIncentives();
	
	if($atts['mode'] == "ui"){ $thisIncentiveBatch->showHtml(); }
	if($atts['mode'] == "plugin"){ $thisIncentiveBatch->showPluginPreview($atts['visibleIncentives']); }
}
/////////////////////////////////////////////
/////////////////////////////////////////////
function show_kia_cs_incentives($atts){ //shows ChromeStyles incentives for Kia division
	$atts = shortcode_atts(array('mode' => 'nomode', 'visibleIncentives'=>'none'), $atts, 'kiachromeincentives' );	
	$thisIncentiveBatch = new VehicleIncentiveGroup();
	$thisIncentiveBatch->division = "Kia";
	$thisIncentiveBatch->divisionId = 8;
	$thisIncentiveBatch->weblink = "www.dickhannahkia.com";
	$thisIncentiveBatch->year = "2016";
	$IncentiveOutput = $thisIncentiveBatch->getIncentives();
	
	if($atts['mode'] == "ui"){ $thisIncentiveBatch->showHtml(); }
	if($atts['mode'] == "plugin"){ $thisIncentiveBatch->showPluginPreview($atts['visibleIncentives']); }
}
/////////////////////////////////////////////
/////////////////////////////////////////////
function show_subaru_cs_incentives($atts){ //shows ChromeStyles incentives for Subaru division
	$atts = shortcode_atts(array('mode' => 'nomode', 'visibleIncentives'=>'none'), $atts, 'kiachromeincentives' );	
	$thisIncentiveBatch = new VehicleIncentiveGroup();
	$thisIncentiveBatch->division = "Subaru";
	$thisIncentiveBatch->divisionId = 9;
	$thisIncentiveBatch->weblink = "www.dickhannahsubaru.com";
	$thisIncentiveBatch->year = "2016";
	$IncentiveOutput = $thisIncentiveBatch->getIncentives();
	
	if($atts['mode'] == "ui"){ $thisIncentiveBatch->showHtml(); }
	if($atts['mode'] == "plugin"){ $thisIncentiveBatch->showPluginPreview($atts['visibleIncentives']); }
}
/////////////////////////////////////////////
/////////////////////////////////////////////
function show_toyota_cs_incentives($atts){ //shows ChromeStyles incentives for Toyota division
	$atts = shortcode_atts(array('mode' => 'nomode', 'visibleIncentives'=>'none'), $atts, 'kiachromeincentives' );	
	$thisIncentiveBatch = new VehicleIncentiveGroup();
	$thisIncentiveBatch->division = "Toyota";
	$thisIncentiveBatch->divisionId = 10;
	$thisIncentiveBatch->weblink = "www.dickhannahtoyota.com";
	$thisIncentiveBatch->year = "2016";
	$IncentiveOutput = $thisIncentiveBatch->getIncentives();
	
	if($atts['mode'] == "ui"){ $thisIncentiveBatch->showHtml(); }
	if($atts['mode'] == "plugin"){ $thisIncentiveBatch->showPluginPreview($atts['visibleIncentives']); }
}
/////////////////////////////////////////////
/////////////////////////////////////////////
function show_scion_cs_incentives($atts){ //shows ChromeStyles incentives for Scion division
	$atts = shortcode_atts(array('mode' => 'nomode', 'visibleIncentives'=>'none'), $atts, 'kiachromeincentives' );	
	$thisIncentiveBatch = new VehicleIncentiveGroup();
	$thisIncentiveBatch->division = "Scion";
	$thisIncentiveBatch->divisionId = 11;
	$thisIncentiveBatch->weblink = "www.dickhannahscion.com";
	$thisIncentiveBatch->year = "2016";
	$IncentiveOutput = $thisIncentiveBatch->getIncentives();
	
	if($atts['mode'] == "ui"){ $thisIncentiveBatch->showHtml(); }
	if($atts['mode'] == "plugin"){ $thisIncentiveBatch->showPluginPreview($atts['visibleIncentives']); }
}
/////////////////////////////////////////////
/////////////////////////////////////////////
function show_volkswagen_cs_incentives($atts){ //shows ChromeStyles incentives for Volkswagen division
	$atts = shortcode_atts(array('mode' => 'nomode', 'visibleIncentives'=>'none'), $atts, 'kiachromeincentives' );	
	$thisIncentiveBatch = new VehicleIncentiveGroup();
	$thisIncentiveBatch->division = "Volkswagen";
	$thisIncentiveBatch->divisionId = 12;
	$thisIncentiveBatch->weblink = "www.dickhannahvolkswagen.com";
	$thisIncentiveBatch->year = "2016";
	$IncentiveOutput = $thisIncentiveBatch->getIncentives();
	
	if($atts['mode'] == "ui"){ $thisIncentiveBatch->showHtml(); }
	if($atts['mode'] == "plugin"){ $thisIncentiveBatch->showPluginPreview($atts['visibleIncentives']); }
}
/////////////////////////////////////////////
/////////////////////////////////////////////
function show_ram_cs_incentives($atts){ //shows ChromeStyles incentives for Ram division
	$atts = shortcode_atts(array('mode' => 'nomode', 'visibleIncentives'=>'none'), $atts, 'kiachromeincentives' );	
	$thisIncentiveBatch = new VehicleIncentiveGroup();
	$thisIncentiveBatch->division = "Ram";
	$thisIncentiveBatch->divisionId = 21;
	$thisIncentiveBatch->weblink = "www.dickhannahdodge.com";
	$thisIncentiveBatch->year = "2016";
	$IncentiveOutput = $thisIncentiveBatch->getIncentives();
	
	if($atts['mode'] == "ui"){ $thisIncentiveBatch->showHtml(); }
	if($atts['mode'] == "plugin"){ $thisIncentiveBatch->showPluginPreview($atts['visibleIncentives']); }
}
/////////////////////////////////////////////
/////////////////////////////////////////////
function show_nissan_cs_incentives($atts){ //shows ChromeStyles incentives for Nissan division
	$atts = shortcode_atts(array('mode' => 'nomode', 'visibleIncentives'=>'none'), $atts, 'kiachromeincentives' );	
	$thisIncentiveBatch = new VehicleIncentiveGroup();
	$thisIncentiveBatch->division = "Nissan";
	$thisIncentiveBatch->divisionId = 23;
	$thisIncentiveBatch->weblink = "www.dickhannahnissan.com";
	$thisIncentiveBatch->year = "2016";
	$IncentiveOutput = $thisIncentiveBatch->getIncentives();
	
	if($atts['mode'] == "ui"){ $thisIncentiveBatch->showHtml(); }
	if($atts['mode'] == "plugin"){ $thisIncentiveBatch->showPluginPreview($atts['visibleIncentives']); }
}
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
wp_enqueue_style( 'chromeIncentives', get_template_directory_uri() . '/css/chromeIncentives.css' );
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
function vehicle_type($vehicle){
    return $vehicle["new_used"] == "N" ? "New" : ($vehicle["new_used"] == "U" ? "Used" : "");
}

function vehicle_label($vehicle)
{
    $vehicle_type = vehicle_type($vehicle);
	$seriesDetail = "";
	if ($vehicle_type == "New") {
		if ($vehicle['make'] == 'Volkswagen' || $vehicle['make'] == 'Hyundai' || $vehicle['make'] == 'Honda' || $vehicle['make'] == 'Nissan') {
			$seriesDetail = $vehicle['series_detail'];
		}
	}
    return "$vehicle_type {$vehicle["year"]} {$vehicle["make"]} {$vehicle["model"]} {$vehicle["trim"]} {$seriesDetail}";
}

function vehicle_image($url)
{
	if( isset($_SERVER['HTTPS'] ) ) {
		return $url ? str_replace("http:", "", $url) : image_url("image-coming-soon.jpg");
	} else {
		return $url ?: image_url("image-coming-soon.jpg");
	}
}

function vehicle_price($price){
	global $dealership;
		return $price == 0 ? "Contact Dealer" : "$".number_format($price);	
}


function vehicle_thumbnail($image_url){
    if(empty($image_url))
      return image_url("image-coming-soon.jpg");

    return str_replace(".jpg", "-thumb.jpg", str_replace("http:", "", $image_url));
}

function vehicle_url($vehicle, $type = '')
{
	global $dealership;
		$newOrUsed = "used";
		if($vehicle['new_used'] == "N") {
		$newOrUsed = "new";
		}
		$vehicleYear = strtolower($vehicle['year']);
		$vehicleMake = strtolower($vehicle['make']);
		$vehicleModel = str_replace("/", "", strtolower($vehicle['model']));
		$vTrim = "";
		if(!empty($vehicle['trim'])) {
			$vTrim = "-".strtolower(str_replace("/", "-", $vehicle['trim']));
		}
		$vehicleStockNumber = strtolower($vehicle['stock_number']);
		echo make_pretty_url(get_bloginfo( 'wpurl' )."/for-sale/{$newOrUsed}-{$vehicleYear}-{$vehicleMake}-{$vehicleModel}{$vTrim}-{$vehicleStockNumber}");	
}

function vehicle_url_returned($vehicle, $type = '')
{	
	global $dealership;
		$newOrUsed = "used";
		if($vehicle['new_used'] == "N") {
		$newOrUsed = "new";
		}
		$vehicleYear = strtolower($vehicle['year']);
		$vehicleMake = strtolower($vehicle['make']);
		$vehicleModel = strtolower($vehicle['model']);
		$vTrim = "";
		if(!empty($vehicle['trim'])) {
			$vTrim = "-".strtolower($vehicle['trim']);
		}
		$vehicleStockNumber = strtolower($vehicle['stock_number']);
		return make_pretty_url(get_bloginfo( 'wpurl' )."/for-sale/{$newOrUsed}-{$vehicleYear}-{$vehicleMake}-{$vehicleModel}{$vTrim}-{$vehicleStockNumber}");

}

function apply_filter($column, $operator, $field_id)
{
    $filter = @ $_REQUEST[$field_id] == '' ? '' : " AND ".$column." ".$operator." ".safe($field_id)."\n";
    return $filter;
}

function apply_string_filter($column, $operator, $field_id)
{
    if(!isset($_REQUEST[$field_id])) {
        $filter = '';
    }
    $filter = $_REQUEST[$field_id] == '' ? '' : " AND ".$column." ".$operator." '".safe($field_id)."'\n";
    return $filter;
}

function apply_keyword_filter($column, $keywords)
{
    return "(".$column." LIKE '%".implode("%' OR ".$column." LIKE '%", $keywords)."%')";
}

function apply_price_filter()
{
    if( @ $_REQUEST["min-price"] == '' && @ $_REQUEST["max-price"] == '' ) {
    } elseif( @ $_REQUEST["min-price"] == '' ) {
        $filter = " AND ( ( price <= ".safe("max-price")." AND price > 0 ) OR ( price = 0 AND compare_to_price <= ".safe("max-price").") )";
    } elseif( @ $_REQUEST["max-price"] == '' ) {
        $filter = " AND ( ( price >= ".safe("min-price")." AND price > 0 ) OR ( price = 0 AND compare_to_price >= ".safe("min-price").") )";
    } else {
        $filter = " AND ( ( price <= ".safe("max-price")." AND price >= ".safe("min-price")." ) OR ( price = 0 AND compare_to_price <= ".safe("max-price")." AND compare_to_price >= ".safe("min-price").") )";
    }
    return $filter;
}

function safe($form_variable)
{
      if(isset($_REQUEST[$form_variable])){
          if($form_variable=='model'){
                 return sqlSafeModelName($_REQUEST[$form_variable]);
              }
          return mysql_real_escape_string($_REQUEST[$form_variable]);
      }elseif(isset($_COOKIE[$form_variable])){
          return dh_soft_get_cookie($form_variable);
      }
      return null; //$form_variable;
}

function image_url($image){
    return get_bloginfo( 'template_url' )."/images/".$image;
}

function child_image_url($image){
    return get_bloginfo( 'stylesheet_directory' )."/images/".$image;
}

function skin_url($file, $specified_skin=''){
    global $dealership;
    $skin =empty( $specified_skin) ? $dealership["skin"] : $specified_skin;
    return get_bloginfo("template_url")."/skins/{$skin}/$file";
}

function make_pretty_url($url){
    return str_replace(array(" ", "&"), array("-", "and"), $url);
}

function include_css($stylesheet){
    echo "<link rel='stylesheet' href='".get_bloginfo( 'template_url' )."/".$stylesheet."' />\r\n";
}

function include_js($js){
    echo "<script type='text/javascript' src='".get_bloginfo( 'template_url' )."/".$js."'></script>\r\n ";
}

function get_auto_colors(){
	
	$conditions = "";
	if (!empty($_REQUEST['make'])) {
		$conditions .= apply_string_filter("make", "=", "make");
	}
	if (!empty($_REQUEST['model'])) {
		$conditions .= apply_string_filter("model", "=", "model");
	}
    return get_results("SELECT DISTINCT exterior_base_color FROM inventory WHERE exterior_base_color != ''{$conditions} ORDER BY exterior_base_color ASC");
}

function get_fuels(){
    return get_results("SELECT DISTINCT fuel FROM inventory WHERE fuel != '' ORDER BY fuel ASC");
}


function enable_pretty_photo(){
    add_action('wp_head', function() {
      /* include_js("prettyPhoto/js/jquery.prettyPhoto.js");
      include_css("prettyPhoto/css/prettyPhoto.css");
	   */
      ?>
      <script type="text/javascript">
          jQuery(document).ready(function() {
            jQuery(".prettyPhoto").prettyPhoto();
          });
      </script>
    <?php
    });
}


function excerpt($limit) {

$permalink = get_permalink($post->ID);
$excerpt = explode(' ', get_the_excerpt(), $limit);

if (count($excerpt)>=$limit) {
	array_pop($excerpt);
	$excerpt = implode(" ",$excerpt).'...<a href="'.$permalink.'">Read more</a>';
	} else {
	$excerpt = implode(" ",$excerpt);
	}
	$excerpt = preg_replace('`\[[^\]]*\]`','',$excerpt);
	return $excerpt;
}

function content($limit) {
	$content = explode(' ', get_the_content(), $limit);
	
	if (count($content)>=$limit) {
	array_pop($content);
	$content = implode(" ",$content).'...';
	} else {
	$content = implode(" ",$content);
	}
	
	$content = preg_replace('/\[.+\]/','', $content);
	$content = apply_filters('the_content', $content); 
	$content = str_replace(']]>', ']]&gt;', $content);
	return $content;
}







// numbered pagination
function pagination($pages = '', $range = 4) {
	$showitems = ($range * 2)+1;

	global $paged;
	if(empty($paged)) $paged = 1;

	if($pages == '') {
		global $wp_query;
		$pages = $wp_query->max_num_pages;
		
		if(!$pages) {
			$pages = 1;
		}
	}
	
	echo "<div class=\"pagination\">";
	
	if ($paged > 1) echo "<a href=\"".get_pagenum_link($paged - 1)."\">&lsaquo; Previous</a>";
	if ($paged > 2) echo "<a href='".get_pagenum_link(1)."'>&laquo;</a>";
	
	if(1 != $pages) {
		echo "<span>Page ".$paged." of ".$pages."</span>";
		echo "<div class=\"PagNumbs\">";

		for ($i=1; $i <= $pages; $i++) {
			if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems )) {
				echo ($paged == $i)? "<span class=\"current\">".$i."</span>":"<a href='".get_pagenum_link($i)."' class=\"inactive\">".$i."</a>";
			}
		}
		
		echo "</div>";
		
		if ($paged < $pages-1) echo "<a href='".get_pagenum_link($pages)."'>&raquo;</a>";
		if ($paged < $pages) echo "<a href=\"".get_pagenum_link($paged + 1)."\">Next &rsaquo;</a>";
		
	}
	
	echo "</div>\n";
}









  /*
  function get_youtube_videos($playlist_id){
    global $dealership;
    $old_inc = ini_get('include_path');
    $new_inc = $old_inc .PATH_SEPARATOR.get_theme_root()."/dickhannah";
    ini_set('include_path',$new_inc);
    require_once 'Zend/Loader.php';

    Zend_Loader::loadClass('Zend_Gdata_YouTube');
    $yt = new Zend_Gdata_YouTube();
    $yt->setMajorProtocolVersion(2);
    $playlistVideoFeed = $yt->getPlaylistVideoFeed("http://gdata.youtube.com/feeds/api/playlists/$playlist_id");
    $videos = array();

    foreach ($playlistVideoFeed as $videoEntry){
      $videoThumbnails = $videoEntry->getVideoThumbnails();
      if(count($videoThumbnails) > 0){
        $videoThumbnail = $videoThumbnails[0];
        array_push($videos, "<a class='prettyPhoto' href='http://www.youtube.com/watch?v={$videoEntry->getVideoId()}'><img src='{$videoThumbnail['url']}' width='{$videoThumbnail['width']}' height='{$videoThumbnail['height']}' /></a>");
      }
    }

    return $videos;
  }
  */

  class PagedResult {
    public $record_count;
    public $page_count;
    public $current_page;
    public $records;
    public $navigation;
    public $showing;
  }
  /*****************************************************************************************************************************************************/
  /**
   * Vehicle Search
   *
   * @param String $count_query
   * @param String $query
   * @return PagedResult
   */
  function paged_result($count_query, $query) {
    $result = new PagedResult();
    $results_per_page = empty($_REQUEST['results_per_page']) ? 10 : $_REQUEST['results_per_page'];
	if (get_the_title() == "Text Customer Reviews") {

        $results_per_page = 20;

    }
    $current_page = empty($_REQUEST['page_no']) ? 1 : $_REQUEST['page_no'];
    $offset = ($current_page - 1) * $results_per_page;

    $result->current_page = $current_page;
    $result->record_count = get_var($count_query);
    $max_page = ceil($result->record_count/$results_per_page);
    $result->page_count = $max_page;
    $nav  = '';

    $min_linked_page = max(1, $current_page > $max_page - 3 ? $max_page - 4 : $current_page - 2);
    $max_linked_page = min($max_page, $current_page < 4 ? 5 : $current_page + 2);

    for($page = $min_linked_page; $page <= $max_linked_page; $page++){
      if ($page == $current_page)
        $nav .= "<span class='current_page page_nav'>$page</span>";
      else
        $nav .= " <span class='Pag_Number page_nav' onclick='viewPage($page)'>$page</span> ";
    }

    if ($current_page > 1){
      $page  = $current_page - 1;
      $prev  = "<span class='Pag_Enabled' onclick='viewPage($page)'><img alt='&lt;' src='".skin_url("prev-page.png?v=7")."'/></span> ";
      $first = "<span class='Pag_Enabled' onclick='viewPage(1)'><img alt='&lt;&lt;' src='".skin_url("first-page.png?v=7")."'/></span> ";
    }
    else {
      $prev  = "<span class='Pag_Disabled'><img alt='&lt;' src='".skin_url("prev-page-disabled.png?v=7")."'/></span>";
      $first  = "<span class='Pag_Disabled'><img alt='&lt;&lt;' src='".skin_url("first-page-disabled.png?v=7")."'/></span>";
    }

    if ($current_page < $max_page){
      $page = $current_page + 1;
      $next = "<span class='Pag_Enabled' onclick='viewPage($page)'><img alt='&gt;' src='".skin_url("next-page.png?v=7")."'/></span> ";
      $last = "<span class='Pag_Enabled' onclick='viewPage($max_page)'><img alt='&gt;&gt;' src='".skin_url("last-page.png?v=7")."'/></span> ";
    }
    else {
      $next  = "<span class='Pag_Disabled'><img alt='&gt;' src='".skin_url("next-page-disabled.png?v=7")."'/></span> ";
      $last  = "<span class='Pag_Disabled'><img alt='&gt;&gt;' src='".skin_url("last-page-disabled.png?v=7")."'/></span> ";
    }
	
	$TopPag = "<div class='SRP_Pag_Top'>";
	$BottomPag = "<div class='SRP_Pag_Bottom'>";
	$EndDiv = "</div>";
	
    $min_ellide = '';
    $max_ellide = '';
    if($min_linked_page != 1)
      //$min_ellide = "<span class='page_nav Pag_Disabled'>...</span>";
    if($max_linked_page != $max_page)
      //$max_ellide = "<span class='page_nav Pag_Disabled'>...</span>";

    $start_number = $results_per_page * ($current_page - 1) + 1;
    $end_number = min($result->record_count, $results_per_page * $current_page);
    $showing = $start_number == $end_number ? "$start_number" : "$start_number - $end_number";
    $result->showing = "Showing $showing of <span class='result_hilite'>{$result->record_count}</span> results.";

    if($max_page != 1)
      $result->nav = $TopPag . $first . $prev . $next . $EndDiv . $BottomPag . $min_ellide . $nav . $max_ellide. $EndDiv;

    $result->records = get_results("$query LIMIT $offset, $results_per_page");
    return $result;
  }

/*****************************************************************************************************************************************************/

/**
 * If the user enters two or more keywords, the above search function doesn't work.
 *
 * @param String $count_query
 * @param String $query
 * @param Array $keywords
 * @return PagedResult
 */
  function filtered_paged_result($count_query, $query, $keywords) {
    global $wpdb;
    $time = date('Ymdhis',time());
    $temptable = 'inventory_search_'.$time;
    $temporary = 'TEMPORARY'; //''
    $tempquery = " CREATE $temporary TABLE
    `$temptable` (
    `dealer_id` varchar(256) DEFAULT NULL,
    `dealer_name` varchar(256) DEFAULT NULL,
    `dealer_address` varchar(2000) DEFAULT NULL,
    `dealer_city` varchar(2000) DEFAULT NULL,
    `dealer_region` varchar(2000) DEFAULT NULL,
    `dealer_postal_code` varchar(20) DEFAULT NULL,
    `marketing_phone_number` varchar(20) DEFAULT NULL,
    `vehicle_id` varchar(32) DEFAULT NULL,
    `vin` varchar(17) DEFAULT NULL,
    `new_used` char(1) DEFAULT NULL,
    `stock_number` varchar(20) DEFAULT NULL,
    `year` int(11) DEFAULT NULL,
    `make` varchar(50) DEFAULT NULL,
    `model` varchar(50) DEFAULT NULL,
    `body` varchar(50) DEFAULT NULL,
    `body_door_count` varchar(50) DEFAULT NULL,
    `trim` varchar(50) DEFAULT NULL,
    `exterior_base_color` varchar(50) DEFAULT NULL,
    `interior_color` varchar(50) DEFAULT NULL,
    `interior_material` varchar(50) DEFAULT NULL,
    `engine` varchar(256) DEFAULT NULL,
    `fuel` varchar(50) DEFAULT NULL,
    `drivetrain_desc` varchar(256) DEFAULT NULL,
    `transmission` varchar(256) DEFAULT NULL,
    `odometer` int(11) DEFAULT NULL,
    `compare_to_price` int(11) DEFAULT NULL,
    `price` int(11) DEFAULT NULL,
    `certified` varchar(50) DEFAULT NULL,
    `features` varchar(4000) DEFAULT NULL,
    `photo_url_list` varchar(4000) DEFAULT NULL,
    `inventory_date` date DEFAULT NULL,
    `autowriter_description` varchar(4000) DEFAULT NULL,
    `city_mpg` int(11) DEFAULT NULL,
    `hwy_mpg` int(11) DEFAULT NULL,
    `body_type` varchar(32) DEFAULT NULL,
	`RealDeal` varchar(50) DEFAULT NULL,
    KEY `IX_inventory_new_used` (`new_used`),
    KEY `IX_inventory_year` (`year`),
    KEY `IX_inventory_make` (`make`),
    KEY `IX_inventory_model` (`model`),
    KEY `IX_inventory_body` (`body`),
    KEY `IX_inventory_trim` (`trim`),
    KEY `IX_inventory_exterior_base_color` (`exterior_base_color`),
    KEY `IX_inventory_odometer` (`odometer`),
    KEY `IX_inventory_price` (`price`),
    KEY `IX_inventory_hwy_mpg` (`hwy_mpg`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;

    ";
    //error_log($tempquery);
    $wpdb->query($tempquery);
    $populate_temp_table = ' INSERT INTO `'. $temptable .'` '. $query;
    $wpdb->query($populate_temp_table);

    $result = new PagedResult();
    $results_per_page = empty($_REQUEST['results_per_page']) ? 10 : $_REQUEST['results_per_page'];
    $current_page = empty($_REQUEST['page_no']) ? 1 : $_REQUEST['page_no'];
    $offset = ($current_page - 1) * $results_per_page;
    $result->current_page = $current_page;

    // Special multi-keyword search {{{
    $find_in_subset = " SELECT * FROM $temptable WHERE 1=1 AND ";
    $check_fields = array('vin','stock_number','make','model','body','trim','exterior_base_color','body_type','interior_material','engine','drivetrain_desc','transmission','features','autowriter_description');
    foreach($keywords as $kw){
        $filter_array = array();
        $filterstr = ' (';
        foreach($check_fields as $cf){
            $filter_array[] = " $cf LIKE '%$kw%' ";
        }
        $filterstr .= implode(' OR ',$filter_array);
        $filterstr .= ')';
        $queryfilters[] = $filterstr;
    }
    $filters = implode(' AND ',$queryfilters);
    $subset_query = $find_in_subset . $filters .' LIMIT '. $offset .','. $results_per_page;
    //error_log($subset_query);
    $count_subset_query = " SELECT count(*) FROM $temptable WHERE 1=1 AND $filters ";
    $result->record_count = get_var($count_subset_query);
    // }}} Special multi-keyword search

    $max_page = ceil($result->record_count/$results_per_page);
    $result->page_count = $max_page;
    $nav  = '';

    $min_linked_page = max(1, $current_page > $max_page - 3 ? $max_page - 4 : $current_page - 2);
    $max_linked_page = min($max_page, $current_page < 4 ? 5 : $current_page + 2);

    for($page = $min_linked_page; $page <= $max_linked_page; $page++){
      if ($page == $current_page)
        $nav .= "<span class='current_page page_nav'>$page</span>";
      else
        $nav .= "<span class='Pag_Number page_nav' onclick='viewPage($page)'>$page</span> ";
    }

    if ($current_page > 1){
      $page  = $current_page - 1;
      $prev  = "<span class='Pag_Enabled' onclick='viewPage($page)'><img alt='&lt;' src='".skin_url("prev-page.png")."'/></span> ";
      $first = "<span class='Pag_Enabled' onclick='viewPage(1)'><img alt='&lt;&lt;' src='".skin_url("first-page.png")."'/></span> ";
    }
    else {
      $prev  = "<span class='Pag_Disabled'><img alt='&lt;' src='".skin_url("prev-page-disabled.png")."'/></span>";
      $first  = "<span class='Pag_Disabled'><img alt='&lt;&lt;' src='".skin_url("first-page-disabled.png")."'/></span>";
    }

    if ($current_page < $max_page){
      $page = $current_page + 1;
      $next = "<span class='Pag_Enabled' onclick='viewPage($page)'><img alt='&gt;' src='".skin_url("next-page.png")."'/></span> ";
      $last = "<span class='Pag_Enabled' onclick='viewPage($max_page)'><img alt='&gt;&gt;' src='".skin_url("last-page.png")."'/></span> ";
    }
    else {
      $next  = "<span class='Pag_Disabled'><img class='Pag_Disabled' alt='&gt;' src='".skin_url("next-page-disabled.png")."'/></span>";
      $last  = "<span class='Pag_Disabled'><img class='Pag_Disabled' alt='&gt;&gt;' src='".skin_url("last-page-disabled.png")."'/></span> ";
    }
	
	$TopPag = "<div class='SRP_Pag_Top'>";
	$BottomPag = "<div class='SRP_Pag_Bottom'>";
	$EndDiv = "</div>";
	
    if($min_linked_page != 1)
      //$min_ellide = "<span class='page_nav Pag_Disabled'>...</span>";
    if($max_linked_page != $max_page)
      //$max_ellide = "<span class='page_nav Pag_Disabled'>...</span>";

    $start_number = $results_per_page * ($current_page - 1) + 1;
    $end_number = min($result->record_count, $results_per_page * $current_page);
    $showing = $start_number == $end_number ? "$start_number" : "$start_number - $end_number";


    $result->showing = "Showing $showing of {$result->record_count} results.";

    if($max_page != 1)
	  $result->nav = $TopPag . $first . $prev . $next . $EndDiv . $BottomPag . $min_ellide . $nav . $max_ellide. $EndDiv;
    //$result->records = $wpdb->get_results("$query LIMIT $offset, $results_per_page", ARRAY_A);
    //var_dump($result);
    $result->records = $wpdb->get_results($subset_query, ARRAY_A);
    return $result;
  }
  /*****************************************************************************************************************************************************/

  function contact_form($submission_type, $stock_number=""){
  global $dealership;
  global $is_mobile;
  $service_dept = get_row("SELECT * FROM department WHERE is_active=1 AND name='Service' AND dealership_id={$dealership["dealership_id"]}");
  ?>
<script type="text/javascript">
var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
var phoneNumberPattern = /^\(?(\d{3})\)?[- ]?(\d{3})[- ]?(\d{4})$/;
$(document).ready(function() {
    $('#mysubmit').bind('click', function() {
        var ok=true;
        if ($('#_3m4nt5r1f').val()=='') {
            ok=false;
        }
        if ($('#_3m4nt541').val()=='') {
            ok=false;
        }
        var email_val = $('#_1i4m3').val();
        if( ! emailPattern.test(email_val) ){
            ok=false;
        }
		var phone_val = $('#_3n0hp').val();
        if( ! phoneNumberPattern.test(phone_val) && ! phone_val == ''){
            ok=false;
        }
        if ($('#dealership_id').val()=='') {
            ok=false;
        }
        return ok;
    });
    
	
	
	// START Required Status?
	
	$("#_3m4nt5r1f").bind("keyup blur", function(){
		if($("#_3m4nt5r1f").val()==""){
			$('#Input_FirstName').addClass('RequiredStatus').removeClass('UnRequiredStatus');
		}else{
			$('#Input_FirstName').addClass('UnRequiredStatus').removeClass('RequiredStatus');
		}
	});
	
	$("#_3m4nt541").bind("keyup blur", function(){
		if($("#_3m4nt541").val()==""){
			$('#Input_LastName').addClass('RequiredStatus').removeClass('UnRequiredStatus');
		}else{
			$('#Input_LastName').addClass('UnRequiredStatus').removeClass('RequiredStatus');
		}
	});
	
	$("#_1i4m3").bind("keyup blur", function(){
		var email_val = $('#_1i4m3').val();
		if( ! emailPattern.test(email_val) ){
			$('#Input_Email').addClass('RequiredStatus').removeClass('UnRequiredStatus');
		}else{
			$('#Input_Email').addClass('UnRequiredStatus').removeClass('RequiredStatus');
		}
	});
	
	$("#_3n0hp").bind("keyup blur", function(){
		var phone_val = $('#_3n0hp').val();
		if( ! phoneNumberPattern.test(phone_val) && ! phone_val == ''){
			$('#Input_Phone').addClass('RequiredStatus').removeClass('UnRequiredStatus');
		}else{
			$('#Input_Phone').addClass('UnRequiredStatus').removeClass('RequiredStatus');
		}
	});
	
	$("#dealership_id").bind("keyup blur change", function(){
		if($("#dealership_id").val()==""){
			$('#Input_Dealership_id').addClass('RequiredStatus').removeClass('UnRequiredStatus');
		}else{
			$('#Input_Dealership_id').addClass('UnRequiredStatus').removeClass('RequiredStatus');
		}
	});
	
	
	
	// START Placeholder Text - IMPORTANT ~ this code needs to be wrapped in a "$(document).ready(function() {"
	var PHfirstnamefunction = function () {
		if($("#_3m4nt5r1f").val()==""){
		$('#MetaFirstName').addClass('Appear').removeClass('Disappear');
		}else {
		$('#MetaFirstName').addClass('Disappear').removeClass('Appear');
		}
	}
	
	var PHlastnamefunction = function () {
		if($("#_3m4nt541").val()==""){
		$('#MetaLastName').addClass('Appear').removeClass('Disappear');
		}else {
		$('#MetaLastName').addClass('Disappear').removeClass('Appear');
		}
	}
	
	var PHemailfunction = function () {
		if($("#_1i4m3").val()==""){
		$('#MetaEmail').addClass('Appear').removeClass('Disappear');
		}else {
		$('#MetaEmail').addClass('Disappear').removeClass('Appear');
		}
	}
	
	var PHphonefunction = function () {
		if($("#_3n0hp").val()==""){
		$('#MetaPhone').addClass('Appear').removeClass('Disappear');
		}else {
		$('#MetaPhone').addClass('Disappear').removeClass('Appear');
		}
	}
	
	var PHcommentsfunction = function () {
		if($("#comments").val()==""){
		$('#MetaComments').addClass('Appear').removeClass('Disappear');
		}else {
		$('#MetaComments').addClass('Disappear').removeClass('Appear');
		}
	}
	
	$('#_3m4nt5r1f').ready(PHfirstnamefunction).keyup(PHfirstnamefunction).blur(PHfirstnamefunction);
	$('#_3m4nt541').ready(PHlastnamefunction).keyup(PHlastnamefunction).blur(PHlastnamefunction);
	$('#_1i4m3').ready(PHemailfunction).keyup(PHemailfunction).blur(PHemailfunction);
	$('#_3n0hp').ready(PHphonefunction).keyup(PHphonefunction).blur(PHphonefunction);
	$('#comments').ready(PHcommentsfunction).keyup(PHcommentsfunction).blur(PHcommentsfunction);
	// END Placeholder Text
	
	
});
</script>
<?php
if(!empty($stock_number)) {
	$contact_type="vdp";
} else {
	$contact_type="general";
}
?>

<div class="ContactUsContent_Wrapper">
	<h1>Let's Connect!</h1>

	<p>
	Please <a href="/contact/hours-and-directions">choose a specific department</a> or call <?php echo get_the_title() != "Service" ? $dealership['phone'] : $service_dept['phone']; ?> to be redirected by a helpful representative.
	</p>

	<p>
	Thanks for visiting us. In addition to our "believe in nice" philosophy we believe in complete transparency as well as clear, honest and helpful communication. Please call or utilize this form and we'll quickly connect you with the representative most qualified to accommodate your specific needs.
	</p>
</div>

<div class="ContactFormDivWrapper">
    <form id="contact_form_XXXX" class="dh color_box contact" action="<?php echo $is_mobile == true ? permalink( 'mobile contact confirmation' ) : permalink( 'Contact Confirmation' )?>?contact-type=<?php echo $contact_type?>" method="post">
        <input type="hidden" name="submission_type" value="<?php echo $submission_type ?>"/>
      <?php if(!empty($stock_number)) {?>
        <input type="hidden" name="stock_number" value="<?php echo $stock_number ?>"/>
      <?php } ?>
	  
      
	<div class="MetaInputWrapper">
		<div onclick="document.getElementById('_3m4nt5r1f').focus();" class="MetaInputText" id="MetaFirstName">
		First name (required)
		</div>
		
		<input type="text" name="first_name" value="" class="hp" />
		<div class="InputV2" id="Input_FirstName"><?php text_box("_3m4nt5r1f"); ?></div>
	</div>
	
	<div class="MetaInputWrapper">
		<div onclick="document.getElementById('_3m4nt541').focus();" class="MetaInputText" id="MetaLastName">
		Last name (required)
		</div>
		
		<input type="text" name="last_name" value="" class="hp" />
		<div class="InputV2" id="Input_LastName"><?php text_box("_3m4nt541"); ?></div>
	</div>
	
	<div class="MetaInputWrapper">
		<div onclick="document.getElementById('_1i4m3').focus();" class="MetaInputText" id="MetaEmail">
		Email (required)
		</div>
		
		<input type="text" name="email" value="" class="hp" />
		<div class="InputV2" id="Input_Email"><?php text_box("_1i4m3"); ?></div>
	</div>
	
	<div class="MetaInputWrapper">
		<div onclick="document.getElementById('_3n0hp').focus();" class="MetaInputText" id="MetaPhone">
		Phone
		</div>
		
		<input type="text" name="phone" value="" class="hp" />
		<div class="InputV2" id="Input_Phone"><?php text_box("_3n0hp"); ?></div>
	</div>
	
	
	
      <?php
	  global $mdealership;
      if($dealership["is_global"]==1 && !isset($mdealership)) {
            $locations = get_results("SELECT * FROM dealership WHERE is_active=1 AND is_global=0 ORDER BY name ASC");
      ?>
		<div class="MetaInputWrapper">
			<div class="InputV2" id="Input_Dealership_id">
				<select name="dealership_id" id="dealership_id">
				<option value="">Please select a dealership (required)</option>
				<?php
				foreach($locations as $location) { ?>
					<option value="<?php echo $location["dealership_id"]?>"><?php echo $location["name"]?></option>
				<?php
				} ?>
				</select>
			</div>
		</div>
      <?php
      } else if (isset($mdealership)) {
      ?>
          <input type="hidden" name="dealership_id" value="<?php echo $mdealership["dealership_id"]; ?>" />
      <?php
	  }	else {
      ?>
          <input type="hidden" name="dealership_id" value="<?php echo $dealership["dealership_id"]; ?>" />
      <?php
      }
      ?>
	  
	<div class="MetaInputWrapper">
		<div onclick="document.getElementById('comments').focus();" class="MetaInputText" id="MetaComments">
		Comments/Questions?
		</div>
		<div class="InputV2" id="Input_Comments"><?php text_area("comments"); ?></div>
	</div>
	
      
      <?php 
	  if($submission_type == "contact" AND $is_mobile == true) { ?>
		<input type="checkbox" name="interests" value="mobilecontact" checked='checked' class="hp" />
	  <?php }
	  elseif($submission_type == "contact" AND $is_mobile != true) { ?>
		<input type="checkbox" name="interests" value="contact" checked='checked' class="hp" />
	  <?php }
	  if($submission_type == "trade-in") { ?>
		<input type="checkbox" name="interests" value="trade-in" checked='checked' class="hp" />
	  <?php } 
	  if($submission_type == "mobile" AND !empty($stock_number)) { ?>
	    <input type="checkbox" name="interests" value="mobilevdp" checked='checked' class="hp" />
	  <?php } ?>
      <div class="Btn_3d_LightGreen NoWebkitInputStyling" style="max-width: 300px;">
        <input id="mysubmit" type="submit" name="submit" value="Send Email" />
      </div>
	  <div class="buttons">
         <p style="margin-top: 15px; font-size: 11px;">We will never rent or sell your personal info</p>
      </div>
    </form>
</div>
<div class="clearfix"></div>
  <?php
  }
  
  add_shortcode( 'contact_form', 'contact_form' );
  
  function review_stars() { 
	global $dealership;
	
	/* Google Reviews */
	$googlereviewsql = "SELECT * FROM dealership_google_reviews WHERE dealership_id='{$dealership['dealership_id']}' AND review_rating >= 3.0 ORDER BY review_rating DESC LIMIT 5";
	$google_reviews =  get_results($googlereviewsql);
  
	/* DealerRater Reviews */
	$dealerratereviewsql = "SELECT * FROM dealership_dealerrater_reviews WHERE dealership_id='{$dealership['dealership_id']}' AND review_rating >= 3 ORDER BY review_rating DESC LIMIT 5";
	$dealerrater_reviews =  get_results($dealerratereviewsql);
  
	$review_output=""."\n"; 
	$review_output.="<script type='text/javascript'>"."\n"; 
	$review_output.="function starRATER (rating, stardiv) {"."\n"; 
	$review_output.=""."\n";
	$review_output.="var PulledNumber = +$(rating).text();"."\n";
	$review_output.="var NotANumber = isNaN(PulledNumber);"."\n";
	$review_output.="var howmanystars;"."\n";
	$review_output.=""."\n";
	$review_output.="if (NotANumber == false){"."\n"; 
	$review_output.="switch (true){"."\n";
	$review_output.="case (PulledNumber >= 0 && PulledNumber < 0.25): howmanystars = 0;break;"."\n";
	$review_output.="case (PulledNumber >= 0.25 && PulledNumber < 0.75): howmanystars = 0.5;break;"."\n";
	$review_output.="case (PulledNumber >= 0.75 && PulledNumber < 1.25): howmanystars = 1;break;"."\n";
	$review_output.="case (PulledNumber >= 1.25 && PulledNumber < 1.75): howmanystars = 1.5;break;"."\n";
	$review_output.="case (PulledNumber >= 1.75 && PulledNumber < 2.25): howmanystars = 2;break;"."\n";
	$review_output.="case (PulledNumber >= 2.25 && PulledNumber < 2.75): howmanystars = 2.5;break;"."\n";
	$review_output.="case (PulledNumber >= 2.75 && PulledNumber < 3.25): howmanystars = 3;break;"."\n";
	$review_output.="case (PulledNumber >= 3.25 && PulledNumber < 3.75): howmanystars = 3.5;break;"."\n";
	$review_output.="case (PulledNumber >= 3.75 && PulledNumber < 4.25): howmanystars = 4;break;"."\n";
	$review_output.="case (PulledNumber >= 4.25 && PulledNumber < 4.75): howmanystars = 4.5;break;"."\n";
	$review_output.="case (PulledNumber >= 4.75 && PulledNumber <= 5): howmanystars = 5;break;"."\n";
	$review_output.="default: alert('Number not in the range of 0-5');break;"."\n";
	$review_output.="}"."\n";
	$review_output.="}"."\n";
	$review_output.=""."\n"; 
	$review_output.="if (howmanystars % 1 != 0) {"."\n";
	$review_output.="var notwholenumber = true;"."\n";
	$review_output.="}"."\n";
	$review_output.=""."\n";
	$review_output.="var RoundedDownStars = Math.floor(howmanystars); "."\n";
	$review_output.="var RoundedUpStars = Math.ceil(howmanystars);"."\n";
	$review_output.=""."\n"; 
	$review_output.="for (n=1; n <= RoundedDownStars; n++) {"."\n";
	$review_output.="$(stardiv).append(\"<img src='".get_bloginfo("template_directory")."/images/review-star.png' alt='Full Star' />\");"."\n";
	$review_output.="}"."\n";
	$review_output.=""."\n";
	$review_output.="if (notwholenumber == true) {"."\n";
	$review_output.="$(stardiv).append(\"<img src='".get_bloginfo("template_directory")."/images/review-star-half.png' alt='Half Star' />\");"."\n";
	$review_output.="}"."\n";
	$review_output.=""."\n";
	$review_output.="for (m=(RoundedUpStars+1); m <= 5; m++) {"."\n";
	$review_output.="$(stardiv).append(\"<img src='".get_bloginfo("template_directory")."/images/review-star-empty.png' alt='Empty Star' />\");"."\n";
	$review_output.="}"."\n";
	$review_output.="}"."\n";
	$review_output.=""."\n"; 
	$review_output.="$(document).ready(function() {"."\n";
	$review_output.="starRATER(\"#GoogleStarRating\", \"#GoogleRoundedStars\");"."\n";
	$review_output.="starRATER(\"#DRStarRating\", \"#DRRoundedStars\");"."\n";
	$review_output.="});"."\n";
	$review_output.="</script>"."\n";
	$review_output.="<div class=\"review_ratings\">"."\n";
	$review_output.="<div class=\"WhyDH_Review_Left_Wrapper\">"."\n";
	$review_output.="<div class=\"review_section\">"."\n";
	$review_output.="<div id=\"GoogleStarRating\" class=\"rating_number\">".$dealership['google_rating']."</div>"."\n";  
	$review_output.="<div class=\"stars_and_total\">"."\n";
	$review_output.="<div id=\"GoogleRoundedStars\" class=\"rating_stars\"></div>"."\n";
	$review_output.="<div class=\"review_total\">".$dealership['google_reviews_total']." Google reviews</div>"."\n";
	$review_output.="</div>"."\n";
	$review_output.="<div class=\"Review_Button\">"."\n";
	$review_output.="<a title=\"Read Reviews\" href=\"#\" onclick=\"ShowLightBox('single13');\">Read Reviews</a>"."\n";
	$review_output.="</div>"."\n";
	$review_output.="</div>"."\n";
	$review_output.="</div>"."\n";
	$review_output.="<div class=\"WhyDH_Review_Right_Wrapper\">"."\n";
	$review_output.="<div class=\"review_section\">"."\n";
	$review_output.="<div id=\"DRStarRating\" class=\"rating_number\">".$dealership['dealerrater_rating']."</div>"."\n";
	$review_output.="<div class=\"stars_and_total\">"."\n";
	$review_output.="<div id=\"DRRoundedStars\" class=\"rating_stars\"></div>"."\n";
	$review_output.="<div class=\"review_total\">".$dealership['dealerrater_total_reviews']." DealerRater reviews</div>"."\n";
	$review_output.="</div>"."\n";
	$review_output.="<div class=\"Review_Button\">"."\n";
	$review_output.="<a title=\"Read Reviews\" href=\"#\" onclick=\"ShowLightBox('single14');\">Read Reviews</a>"."\n";
	$review_output.="</div>"."\n";
	$review_output.="</div>"."\n";
	$review_output.="</div>"."\n";
	$review_output.="<div class=\"clearfix\"></div>"."\n";
	$review_output.="<div class=\"clearfix\"></div>"."\n";
	$review_output.=""."\n";
	$review_output.="<div id=\"fade\" class=\"LB-black-overlay\" onclick=\"if (!is_modal) HideLightBox(); return false;\"></div>"."\n";
	$review_output.=""."\n";
	$review_output.="<div id=\"single14\" class=\"LB-white-content_small_long\">"."\n";
	$review_output.="<div class=\"StickyBarCloseWrapper\">"."\n";
	$review_output.="<div class=\"close\" onclick=\"HideLightBox(); return false;\"><div class=\"InnerCloseDiv\">X</div></div>"."\n";
	$review_output.="</div>"."\n";
	$review_output.=""."\n";
	$review_output.="<div class=\"StickyBarTitleWrapper\">"."\n";
	$review_output.="<div class=\"StickyTitle\">DealerRater Reviews</div>"."\n";
	$review_output.="</div>"."\n";
	$review_output.=""."\n";
	$review_output.="<div class=\"LightboxContent_small_long\">"."\n"; 
	foreach ($dealerrater_reviews as $review) {
		$VDP_AuthorRenamed = str_replace(" ","",$review['review_author']); // Delete Spaces
		$VDP_AuthorRenamed = preg_replace("/^\d+/","",$VDP_AuthorRenamed); // Delete digits only if they are at the beginning of string
		$VDP_AuthorRenamed = preg_replace("/[^\w]/i","",$VDP_AuthorRenamed); // Delete any charactors that aren't letters, numbers, underscores or dashes
		$review_output.="<div class=\"VDP_ReviewTop\">"."\n";
		$review_output.="<div class=\"VDP_ReviewAuthor\">"."\n";
		$review_output.=$review['review_author']."\n";
		$review_output.="</div>"."\n";
		$review_output.=""."\n";
		$review_output.="<div class=\"VDP_ReviewRating\">"."\n";
		$review_output.="<div id=\"VDP_Rating_".$VDP_AuthorRenamed."\" ";
		$review_output.="class=\"VDP_StarRating\">".$review['review_rating']."</div>"."\n";
		$review_output.=""."\n";
		$review_output.="<div id=\"VDP_Stars_".$VDP_AuthorRenamed."\" class=\"VDP_StarImgWrap\"></div>"."\n";
		$review_output.=""."\n";
		$review_output.="</div>"."\n";
		$review_output.=""."\n";
		$review_output.=""."\n";
		$review_output.="<div class=\"clearfix\"></div>"."\n";
		$review_output.="</div>"."\n";
		$review_output.=""."\n";
		$review_output.="<div class=\"VDP_ReviewText\">"."\n";
		$review_output.=$review['review_text']."\n";
		$review_output.="</div>"."\n";
	}
	$review_output.="</div>"."\n";
	$review_output.="</div>"."\n";
	$review_output.=""."\n";
	$review_output.=""."\n";
	$review_output.="<div id=\"single13\" class=\"LB-white-content_small_long\">"."\n";
	$review_output.="<div class=\"StickyBarCloseWrapper\">"."\n";
	$review_output.="<div class=\"close\" onclick=\"HideLightBox(); return false;\"><div class=\"InnerCloseDiv\">X</div></div>"."\n";
	$review_output.="</div>"."\n";
	$review_output.=""."\n";
	$review_output.="<div class=\"StickyBarTitleWrapper\">"."\n";
	$review_output.="<div class=\"StickyTitle\">Google Reviews</div>"."\n";
	$review_output.="</div>"."\n";
	$review_output.=""."\n";
	$review_output.="<div class=\"LightboxContent_small_long\">"."\n";
	foreach ($google_reviews as $review) {
		$VDP_AuthorRenamed = str_replace(" ","",$review['review_author']); // Delete Spaces
		$VDP_AuthorRenamed = preg_replace("/^\d+/","",$VDP_AuthorRenamed); // Delete digits only if they are at the beginning of string
		$VDP_AuthorRenamed = preg_replace("/[^\w]/i","",$VDP_AuthorRenamed); // Delete any charactors that aren't letters, numbers, underscores or dashes
		$review_output.="<div class=\"VDP_ReviewTop\">"."\n";
		$review_output.="<div class=\"VDP_ReviewAuthor\">"."\n";
		$review_output.=$review['review_author']."\n";
		$review_output.="</div>"."\n";
		$review_output.=""."\n";
		$review_output.="<div class=\"VDP_ReviewRating\">"."\n";
		$review_output.="<div id=\"VDP_Rating_".$VDP_AuthorRenamed."\" ";
		$review_output.="class=\"VDP_StarRating\">".$review['review_rating']."</div>"."\n";
		$review_output.=""."\n";
		$review_output.="<div id=\"VDP_Stars_".$VDP_AuthorRenamed."\" class=\"VDP_StarImgWrap\"></div>"."\n";
		$review_output.=""."\n";
		$review_output.="</div>"."\n";
		$review_output.=""."\n";
		$review_output.=""."\n";
		$review_output.="<div class=\"clearfix\"></div>"."\n";
		$review_output.="</div>"."\n";
		$review_output.=""."\n";
		$review_output.="<div class=\"VDP_ReviewText\">"."\n";
		$review_output.=$review['review_text']."\n";
		$review_output.="</div>"."\n";
	}
	$review_output.="</div>"."\n";
	$review_output.="</div>"."\n";
	$review_output.="</div>"."\n";
	
	return $review_output;
  }
  
  

  add_shortcode( 'review_stars', 'review_stars' );
  
  function model_contact_form($atts){ 
	global $dealership;
	$modelInfo = shortcode_atts( array('year' => 2000, 'make' => 'make', 'model' => 'model'), $atts );
	$year = $modelInfo['year'];
	$make = $modelInfo['make'];
	$model = $modelInfo['model'];
  ?>
  
<script type="text/javascript">
var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
var phoneNumberPattern = /^\(?(\d{3})\)?[- ]?(\d{3})[- ]?(\d{4})$/;
$(document).ready(function() {
    $('#mysubmit').bind('click', function() {
        var ok=true;
        if ($('#_3m4nt5r1f').val()=='') {
            ok=false;
        }
        if ($('#_3m4nt541').val()=='') {
            ok=false;
        }
        var email_val = $('#_1i4m3').val();
        if( ! emailPattern.test(email_val) ){
            ok=false;
        }
		var phone_val = $('#_3n0hp').val();
        if( ! phoneNumberPattern.test(phone_val) && ! phone_val == ''){
            ok=false;
        }
        return ok;
    });
    
	
	
	// START Required Status?
	
	$("#_3m4nt5r1f").bind("keyup blur", function(){
		if($("#_3m4nt5r1f").val()==""){
			$('#Input_FirstName').addClass('RequiredStatus').removeClass('UnRequiredStatus');
		}else{
			$('#Input_FirstName').addClass('UnRequiredStatus').removeClass('RequiredStatus');
		}
	});
	
	$("#_3m4nt541").bind("keyup blur", function(){
		if($("#_3m4nt541").val()==""){
			$('#Input_LastName').addClass('RequiredStatus').removeClass('UnRequiredStatus');
		}else{
			$('#Input_LastName').addClass('UnRequiredStatus').removeClass('RequiredStatus');
		}
	});
	
	$("#_1i4m3").bind("keyup blur", function(){
		var email_val = $('#_1i4m3').val();
		if( ! emailPattern.test(email_val) ){
			$('#Input_Email').addClass('RequiredStatus').removeClass('UnRequiredStatus');
		}else{
			$('#Input_Email').addClass('UnRequiredStatus').removeClass('RequiredStatus');
		}
	});
	
	$("#_3n0hp").bind("keyup blur", function(){
		var phone_val = $('#_3n0hp').val();
		if( ! phoneNumberPattern.test(phone_val) && ! phone_val == ''){
			$('#Input_Phone').addClass('RequiredStatus').removeClass('UnRequiredStatus');
		}else{
			$('#Input_Phone').addClass('UnRequiredStatus').removeClass('RequiredStatus');
		}
	});
	
	
	
	
	// START Placeholder Text - IMPORTANT ~ this code needs to be wrapped in a "$(document).ready(function() {"
	var PHfirstnamefunction = function () {
		if($("#_3m4nt5r1f").val()==""){
		$('#MetaFirstName').addClass('Appear').removeClass('Disappear');
		}else {
		$('#MetaFirstName').addClass('Disappear').removeClass('Appear');
		}
	}
	
	var PHlastnamefunction = function () {
		if($("#_3m4nt541").val()==""){
		$('#MetaLastName').addClass('Appear').removeClass('Disappear');
		}else {
		$('#MetaLastName').addClass('Disappear').removeClass('Appear');
		}
	}
	
	var PHemailfunction = function () {
		if($("#_1i4m3").val()==""){
		$('#MetaEmail').addClass('Appear').removeClass('Disappear');
		}else {
		$('#MetaEmail').addClass('Disappear').removeClass('Appear');
		}
	}
	
	var PHphonefunction = function () {
		if($("#_3n0hp").val()==""){
		$('#MetaPhone').addClass('Appear').removeClass('Disappear');
		}else {
		$('#MetaPhone').addClass('Disappear').removeClass('Appear');
		}
	}
	
	var PHcommentsfunction = function () {
		if($("#comments").val()==""){
		$('#MetaComments').addClass('Appear').removeClass('Disappear');
		}else {
		$('#MetaComments').addClass('Disappear').removeClass('Appear');
		}
	}
	
	$('#_3m4nt5r1f').ready(PHfirstnamefunction).keyup(PHfirstnamefunction).blur(PHfirstnamefunction);
	$('#_3m4nt541').ready(PHlastnamefunction).keyup(PHlastnamefunction).blur(PHlastnamefunction);
	$('#_1i4m3').ready(PHemailfunction).keyup(PHemailfunction).blur(PHemailfunction);
	$('#_3n0hp').ready(PHphonefunction).keyup(PHphonefunction).blur(PHphonefunction);
	$('#comments').ready(PHcommentsfunction).keyup(PHcommentsfunction).blur(PHcommentsfunction);
	// END Placeholder Text
	
	
});
</script>

<div id="LightboxForm_wrapper">
	
	<div id="fade" class="LB-black-overlay" onclick="if (!is_modal) HideLightBox(); return false;"></div>
	
	<div id="LightboxForm_single1" class="LB-white-content">   
		<a class="close" href="" onclick="HideLightBox(); return false;">X</a>
		
		<div id="LightboxForm_Form">
		
		<label>Get a Quote</label>
				   <form id="contact_form" class="dh sidebar color_box" action="<?php echo permalink("Model Contact Confirmation") ?>" method="post">
				  <input type="hidden" name="dealership_id" value="<?php echo $dealership["dealership_id"]; ?>" />
					<input type="hidden" name="submission_type" value="model_lead"/>
						  <input type="hidden" id="year" name="year" value="<?php echo $year; ?>"/>
						  <input type="hidden" id="make" name="make" value="<?php echo $make; ?>"/>
						  <input type="hidden" id="model" name="model" value="<?php echo $model; ?>"/>
				  <div class="MetaInputWrapper">
					  <div onclick="document.getElementById('_3m4nt5r1f').focus();" class="MetaInputText" id="MetaFirstName">
					  First name*
					  </div>
					  <input type="text" name="first_name" value="" class="hp"/>
					  <?php
					text_box("_3m4nt5r1f");
					  ?>
					<div class="error_first_name errortext"></div>
				  </div>

				  <div class="MetaInputWrapper">
					<div onclick="document.getElementById('_3m4nt541').focus();" class="MetaInputText" id="MetaLastName">
					Last name*
					</div>
					  <input type="text" name="last_name" value="" class="hp"/>
					<?php
					text_box("_3m4nt541");
					?>
					<div class="error_last_name errortext"></div>
				  </div>

				  <div class="MetaInputWrapper">
					<div onclick="document.getElementById('_1i4m3').focus();" class="MetaInputText" id="MetaEmail">
					Email*
					</div>
					  <input type="text" name="email" value="" class="hp"/>
					<?php
					text_box("_1i4m3")
					?>
					<div class="error_email errortext"></div>
				  </div>

				  <div class="MetaInputWrapper">
					<div onclick="document.getElementById('_3n0hp').focus();" class="MetaInputText" id="MetaPhone">
					Phone
					</div>
					<input type="text" name="phone" value="" class="hp"/>
					<?php
					text_box("_3n0hp");
					?>
					<div class="error_phone errortext"></div>
				  </div>

				  <div class="MetaInputWrapper">
					<div onclick="document.getElementById('comments').focus();" class="MetaInputText" id="MetaTextarea">
					What would you like to know about this vehicle? (Optional)
					</div>
					<textarea name="comments" id="comments"></textarea>
				  </div>
				  <!--
				  <div style="padding:0 0 .5em 1em;" class="checkboxes">
					  <input type="checkbox" name="interests[]" value="quote"  /> Get a Quote<br/>

					  <input type="checkbox" name="interests[]" value="test_drive"  /> Schedule a Test Drive<br/>
					  <input type="checkbox" name="interests[]" value="financing"  /> Get Financed<br/>
				  </div>
				  -->
				  <div class="Btn_3d_LightGreen NoWebkitInputStyling" style="max-width: 300px;">
					<input id="mysubmit" type="submit" value="Send Email" name="submit" />
				  </div>
				  <div class="SidebarContactFormDiv">
					<p style="margin-top: 15px; font-size: 11px;">We will never rent or sell your personal info</p>
				  </div>
				</form>
		
		</div>
		
	</div>

</div>

<?php
  }

  add_shortcode( 'ModelContact', 'model_contact_form' );
  
  function dh_soft_set_cookie($name,$value){
    //if(!isset($_COOKIE[$name])){
        error_log('Setting cookie '.$name.' to '.$value.' for 30 days.');
        setcookie($name,base64_encode($value),time()+60*60*24*30,'/'); // 30 days
        $_SESSION[$name]=$value;
    //}
  }

  function dh_soft_get_cookie($name){
      if(WP_DEBUG){
          error_log('~~~~~ Checking for a cookie named '.$name);
          $msg = 'Cookie Data: ';
          foreach($_COOKIE as $K=>$V){
              $msg .=$K.'=>'.$V.'|';
          }
          error_log(' inside $_COOKIE : '.$msg);
      }
      if(isset($_COOKIE[$name])){
          return base64_decode($_COOKIE[$name]);
      }
      return '';
  }

  function contact_confirmation(){
    /* We've now implemented a honeypot
     * first_name = _3m4nt5r1f
     * last_name = _3m4nt541
     * email = _1i4m3
     * phone = _3n0hp
     */
    if(
        (isset($_POST['email'])&&$_POST['email']!='')             ||
        (isset($_POST['first_name'])&&$_POST['first_name']!='')   ||
        (isset($_POST['last_name'])&&$_POST['last_name']!='')     ||
        (isset($_POST['phone'])&&$_POST['phone']!='')
      )
    {
        // BAD ROBOT
        //if(WP_DEBUG){
            error_log('############################# '.$_POST['first_name'].' '.$_POST['last_name'].' '. $_POST['email'] .' '. $_POST['phone'] .' is a bad robot! #############################');
        //}
        echo "<h1>Thank You</h1>\n<p>Your message has been sent<!-- to the recycle bin -->!</p>\n";
        die();
    }
    else if(
        (!isset($_POST['_1i4m3']) OR $_POST['_1i4m3']=='')             ||
        (!isset($_POST['_3m4nt5r1f']) OR $_POST['_3m4nt5r1f']=='')   ||
        (!isset($_POST['_3m4nt541']) OR $_POST['_3m4nt541']=='')
      )
    {
        echo "<h1>We're sorry.</h1>\n<p>Your message has not been sent. There was some information missing from your submission, please try again.</p>\n";
        die();
    }
	else
    {
        global $dealership;
        if(WP_DEBUG){
            error_log('############################# '.$_POST['_3m4nt5r1f'].' '.$_POST['_3m4nt541'].' #############################');
        }
        dh_soft_set_cookie('first_name', $_POST['_3m4nt5r1f']);
        dh_soft_set_cookie('last_name', $_POST['_3m4nt541']);
        dh_soft_set_cookie('email', $_POST['_1i4m3']);
        dh_soft_set_cookie('phone', $_POST['_3n0hp']);

        $submission_type = $_POST['submission_type'];
        if (empty($submission_type))
        {
            $submission_type = empty($_POST['stock_number']) ? "contact" : "vehicle_lead";
        }
        save_form_submission($submission_type);
		
		if ($dealership['is_global'] && !empty($_POST['stock_number'])) {
			$contact_dealership = dh_get_dealership_from_stock_number($_POST['stock_number']);
		} else if($dealership["is_global"]==1) {
			$contact_dealership = dh_get_dealership_from_id($_POST['dealership_id']);
		} else {
			$contact_dealership = $dealership;
		}

        if(!isset($_POST['interests'])){
            $_POST['interests'] = array();
        }
        $service = $submission_type;
        $car = get_row("SELECT * FROM inventory where stock_number='{$_POST["stock_number"]}'");
		$comments = $_POST["comments"];
		
		$subject = "New Prospect";
		
		if($submission_type == "model_lead"){
		  $car = array('year' => $_REQUEST['year'], 'make' => $_REQUEST['make'], 'model' => $_REQUEST['model']);
		  $comments = "THIS FORM SUBMITTED FROM A MODELS PAGE \n".$_POST["comments"];
		  $subject = "New Model Prospect";
		}
		
		if($submission_type == "service") {
			$subject = "Service Lead: ".htmlspecialchars($_POST['_3m4nt5r1f'])." ".htmlspecialchars($_POST['_3m4nt541']);
			$comments .= "\n".htmlspecialchars($_POST['_3m4nt5r1f'])." ".htmlspecialchars($_POST['_3m4nt541'])."\n".htmlspecialchars($_POST['_1i4m3'])."\n".htmlspecialchars($_POST['_3n0hp']);
		}
		
        if(isset($_POST["submit"])) {
			if($submission_type == "service") {
				mail("FastSpecialtiesvm@dickhannah.com", $subject, $comments);
			} else {
				send_adf($contact_dealership["crm_email"], $subject, $service, $car, $comments);
			}
        }
    }
}

function send_adf($to, $subject, $service, $car, $comments){
    global $dealership;
	if($_COOKIE['dh8a775'] == '1') {
		$adSource = '-AdWords';
	} else if ($_COOKIE['dh8a775'] == '2') {
		$adSource = '-BingAd';
	} else {
		$adSource = '';
	}
	
	if (!empty($_GET['vehicle-type']) && !empty($_GET['contentSource'])) {
		$contentSource = "-".$_GET['vehicle-type'];
	} else {
		$contentSource = "";
	}	

	if (!empty($_GET['contentSource'])) {
		$contentSource .= "-".$_GET['contentSource'];
	} else {
		$contentSource .= "";
	}
	
    if ($dealership['is_global'] && !empty($_POST['stock_number'])) {
			$contact_dealership = dh_get_dealership_from_stock_number($_POST['stock_number']);
	} else if($dealership["is_global"]==1) {
            $contact_dealership = dh_get_dealership_from_id($_POST['dealership_id']);
    } else {
            $contact_dealership = $dealership;
    }
    $phone = htmlspecialchars($_POST["_3n0hp"]);
    $adf = new SimpleXMLExtended("<adf></adf>");
    $prospect = $adf->addChild("prospect");
    $prospect->addChild("requestdate", date("c"));
    $vehicle = $prospect->addChild("vehicle");
    $vehicle->addAttribute("status", strtolower(vehicle_type($car)));
    $vehicle->addChild("year", $car["year"]);
    $vehicle->addChild("make", htmlspecialchars($car["make"]));
    $vehicle->addChild("model", htmlspecialchars($car["model"]));
	$vehicle->addChild("body", $car["body"]);
	$vehicle->addChild("odometer", $car["odometer"]);
    $vehicle->addChild("stock", $car["stock_number"]);
	$vehicle->addChild("vin", $car["vin"]);
    $customer = $prospect->addChild("customer");
    $contact = $customer->addChild("contact");
    $contact->addChild("name", htmlspecialchars($_POST["_3m4nt5r1f"]))->addAttribute("part", "first");
    $contact->addChild("name", htmlspecialchars($_POST["_3m4nt541"]))->addAttribute("part", "last");
    $contact->addChild("phone", $phone);
    $contact->addChild("email", htmlspecialchars($_POST["_1i4m3"]));
	$address = $contact->addChild("address");
	$address->addChild("street", htmlspecialchars($_POST["address_one"]));
	$address->addChild("apartment", htmlspecialchars($_POST["address_two"]));
	$address->addChild("city", htmlspecialchars($_POST["city"]));
	$address->addChild("regioncode", htmlspecialchars($_POST["state"]));
	$address->addChild("postalcode", htmlspecialchars($_POST["zipcode"]));
    $customer->addChild("comments")->addCData($comments);
    $vendor = $prospect->addChild("vendor");
    $vendor_contact = $vendor->addChild("contact");
    $vendor_contact->addChild("name", $contact_dealership["name"])->addAttribute("part", "full");
    $provider = $prospect->addChild("provider");
	if($service == "Newsletter") {
		$provider->addChild("name", $service)->addAttribute("part", "full");
	} else {
		$provider->addChild("name", $_SERVER['HTTP_HOST'].'-'.$service.$contentSource.$adSource)->addAttribute("part", "full");
    }
    if($service == "Newsletter") {
        $provider->addChild("service", $service);
    } else {
        $provider->addChild("service", $_SERVER['HTTP_HOST'].'-'.$service.$contentSource.$adSource);
    }

    $message = "<?ADF VERSION \"1.0\"?".">\r\n".$adf->prettyPrint();
    mail($to, $subject.": ".$_POST["_1i4m3"], $message);
    mail('leads@dickhannah.com', $subject.": ".$_POST["_1i4m3"], $message);
}

/*
#########################################
	Conversion Tracking
#########################################
*/
// Called from contact-confirmation ###GOOGLE ECOM CONVERSION###
function gatc_conversion_tracker($auto)
{
      $time = time();
      $vehicle_price = 20; //min((int)$auto['price'],(int)$auto['compare_to_price']);
      if($vehicle_price==0){ $vehicle_price = max((int)$auto['price'],(int)$auto['compare_to_price']); }
      if($vehicle_price==0){ return; }
      $vin_number = $auto['vin'];
      if (isset($auto['new_or_used'])){
          $new_or_used = $auto['new_or_used']=='N' ? 'New' : 'Used';
      }else{
          $new_or_used = 'NA';
      }
      $product_name = vehicle_label($auto);
      ?>

<script type="text/javascript">
  _gaq.push(['_addTrans',
    '<?php echo $time ?>',          // order ID - required
    'DickHannah',                   // affiliation or store name
    '<?php echo $vehicle_price ?>', // total - required
    '',                             // tax
    '',                             // shipping
    '',                             // city
    '',                             // state or province
    ''                              // country
  ]);

   // add item might be called for every item in the shopping cart
   // where your ecommerce engine loops through each item in the cart and
   // prints out _addItem for each
  _gaq.push(['_addItem',
    '<?php echo $time ?>',            // order ID - required
    '<?php echo $vin_number ?>',      // SKU/code - required
    '<?php echo $product_name ?>',    // product name
    '<?php echo $new_or_used ?>',     // category or variation
    '<?php echo $vehicle_price ?>',   // unit price - required
    '1'                               // quantity - required
  ]);
  _gaq.push(['_trackTrans']); //submits transaction to the Analytics servers

</script>

<?php
  }
  
function bing_conversion_tracker() {
	global $dealership;
	
	if($dealership['bing_conv']){
		echo str_replace('&quot;', '"', $dealership['bing_conv']);
	}
}
  
/*
##################################
	END Conversion Tracking
##################################
*/


  function newsletter_signup_form($first, $last, $email, $dealership_id = 1){?>
    <form id="newsletter_signup_form" class="dh" action="<?php echo get_permalink(get_page_by_title('Newsletter Confirmation')->ID)?>" method="post">
      <?php if(!empty($email)) {?>
        <input type="hidden" name="_3m4nt5r1f" value="<?php echo $first ?>"/>
        <input type="hidden" name="_3m4nt541" value="<?php echo $last ?>"/>
        <input type="hidden" name="_1i4m3" value="<?php echo $email ?>"/>
        <input type="hidden" name="dealership_id" value="<?php echo $dealership_id ?>"/>
      <?php }
      else { ?>
      <div>
        <label class="right" for="first_name">First name:</label>
          <?php
          text_box("first_name",'class="hp"');
          text_box('_3m4nt5r1f');
          ?>
      </div>
      <div>
        <label class="right" for="last_name">Last name:</label>
        <?php
        text_box("last_name",'class="hp"');
        text_box("_3m4nt541");
        ?>
      </div>
        <div>
          <label class="right" for="email">Email:</label>
          <?php
          text_box("email",'class="hp"');
          text_box("_1i4m3")
          ?>
        </div>
      <?php
      }?>
      <input type="submit" name="submit" value="Sign me up" class="btn_medium"/>
    </form>
  <?php
  }

  function google_map($location){
    $map_address = str_replace(" ", "+", "{$location["address"]}, {$location["city"]}, {$location["state"]} {$location["zip"]}" );
    $map_name = str_replace(" ", "+", $location["name"]);
    return "http://www.google.com/maps?f=q&source=s_q&hl=en&geocode=&q=$map_name&aq=&ie=UTF8&hq=&hnear=$map_address&daddr=$map_address&z=17";
  }

  function save_form_submission($submission_type, $dealership_id = 0){
    global $dealership;
    global $wpdb;
    date_default_timezone_set('America/Los_Angeles');
    $time = date('Y-m-d H:i:s',time());
    $form_values = http_build_query($_POST);

    if ($dealership_id == 0) { $dealership_id = $dealership["dealership_id"]; }
    if($_POST)
    {
        $wpdb->insert(
          "form_submission",
          array(
            'dealership_id' => $dealership_id, //$dealership["dealership_id"],
            'submission_dt' => $time,
            'type' => $submission_type,
            'submitted_by' => $_POST["_1i4m3"],
            'form_values' => $form_values
          ),
          array('%d', '%s', '%s', '%s', '%s'));
    }
    return $form_values;
  }
  /*
  ####################################
        FORMS
  ####################################
  */

  function select_option_js($select_id) {
    if(isset($_REQUEST[$select_id]))
      echo "$('#$select_id').val( '{$_REQUEST[$select_id]}' ).attr('selected',true).change();\r\n";
  }

  function radio_yes_no($id){
    radio_button($id, "yes", "Yes");
    radio_button($id, "no", "No");
  }

  function radio_button($id, $value, $label=null) {
    $label = $label ?: $value;
    echo "<input type='radio' name='$id' id='$id' value='$value' ".(safe($id) == $value ? "checked='checked'" : "")." $attrs/> $label";
  }

  function check_box_group($id, $value, $attrs='') {
    $group = $_REQUEST[$id];

    if(empty($group))
      $selected = false;
    else
      $selected = in_array($value, $group);

    echo "<input type='checkbox' name='{$id}[]' value='$value' ".($selected ? "checked='checked'" : "")." $attrs/>";
  }

  function text_box($id, $attrs='') {
    echo "<input type='text' name='$id' id='$id' value='".(safe($id))."' $attrs/>";
  }

  function text_area($id){
    echo "<textarea name='$id' id='$id'>".(safe($id))."</textarea>";
  }

  function select_option($value, $select_id, $text, $single = 0) {

   $searchedValue = trim(strtolower(safe($select_id)));

   if($select_id == 'model' && $searchedValue != ''){
       $value = sqlSafeModelName($value);
   }

    if ($searchedValue == trim(strtolower($value))) {
        $selected = ' selected="selected"';
    } elseif ($single == 1) {
        $selected = ' selected="selected"';
    } else {
        $selected = '';
    }

    echo "<option value=\"$value\"$selected>$text</option>\r\n";
  }

  function get_select_option($value, $select_id, $text) {
    if (trim(strtolower($select_id)) == trim(strtolower($value))) {
        $selected = ' selected="selected"';
    }else{
        $selected = '';
    }
    $incoming = array(strtolower($value));
    echo "<option value=\"".$value."\"$selected>$text</option>\r\n";
  }

  function form_select_radio($radio_name) {
    if(isset($_REQUEST[$radio_name]))
      echo "$('input[name=".$radio_name."][value=".$_REQUEST[$radio_name]."]:radio').attr('checked', 'checked');";
  }

  class SimpleXMLExtended extends SimpleXMLElement{
    public function addCData($cdata_text){
      $dom = dom_import_simplexml($this);
      $doc = $dom->ownerDocument;
      $dom->appendChild($doc->createCDATASection($cdata_text));
    }

    public function prettyPrint(){
      $dom = new DOMDocument();
      $dom->loadXML($this->asXML());
      $dom->formatOutput = true;
      return $dom->saveXML();
    }

  }

/*
####################################
    MISC
####################################
*/


function dealership_is_global(){
    global $dealership;
    return (! $dealership['is_global']=='0');
}

function dh_get_fqdn_from_dealership()
{
    return 'http://'.dh_get_domain_from_dealership();
}

function dh_get_certified_logos()
{
    $url = get_bloginfo('template_directory') .'/images/certified/';
    //var_dump('URL: '.$url.'<br/>');
    $path = get_theme_root() .'/dh_responsive/images/certified/';
    //var_dump('Path: '.$path.'<br/>');
    $logos = array();
    $list = scandir($path);
    //var_dump('List: '.$list.'<br/>');
    foreach($list as $file){
        if($file != '.' AND $file != '..'){
            $brand = str_replace('.png','',$file);
            $logos[$brand] = $url.$file;
        }
    }
    return $logos;
}

function dh_get_certified_logo_for_vehicle($vehicle){
    $url = get_bloginfo('template_directory') .'/images/certified/';
    $certified_logo = $vehicle['make'].'_sm.gif';
    return $url . $certified_logo ;
}

function dh_get_stocknumber_list(){
    global $wpdb;
    $sql = " SELECT stock_number FROM inventory ";
    $results = $wpdb->get_results($sql, ARRAY_A);
    foreach($results as $row){
        $stocknumbers[] = "'".$row['stock_number']."'";
    }
    return $stocknumbers;
}

/**
 * Returns label for pricing information
 *
 * @global Array $dealership    Global plugin array
 * @param String $new_used      New or Used car
 * @param String $price_source  Source of Dick Hannah or outside source
 * @return String
 */
function dh_get_price_label( $new_used = 'N', $price_source = 'dh' ){
    global $dealership;

    if ( $new_used == 'N' && $price_source == 'dh' && !empty( $dealership['dealer_price_lbl'] ) ) {
        return $dealership['dealer_price_lbl'].': ';
    } elseif ( $new_used == 'U' && $price_source == 'dh' && !empty( $dealership['dealer_used_price_lbl'] ) ) {
        return $dealership['dealer_used_price_lbl'].': ';
    } elseif ( $price_source == 'dh' ) {
        return 'Our Price: ';
    }

    if ( $new_used == 'N' && !empty( $dealership['comparison_new_price_lbl'] ) ) {
        return $dealership['comparison_new_price_lbl'].': ';
    } elseif ( $new_used == 'N' ) {
        return 'MSRP: ';
    }

    if ( $new_used == 'U' && !empty( $dealership['comparison_used_price_lbl'] ) ) {
        return $dealership['comparison_used_price_lbl'].': ';
    } elseif ( $new_used == 'U' ) {
        return 'Kelley Blue Book: ';
    }
}

function dh_get_price_amount($price=0){
    if (intval($price)<=1) $price=0;
    return vehicle_price($price);
}

function dh_get_welcome_headline(){
    global $dealership;
    if( is_mobile_site() || dealership_is_global() )
    {
        return 'Dick Hannah Dealerships';
    }
    else
    {
        return $dealership['name'];
    }
}

function dh_get_cities(){
    global $dealership;
    if( is_mobile_site() || dealership_is_global() )
    {
        return 'Portland, Vancouver, Kelso';
    }
    else
    {
        return $dealership["city"].", ".$dealership["state"];
    }
}

function dh_get_vehicle_intro($vehicle){
    return neat_trim($vehicle['autowriter_description'],250);
}

function dh_get_bbb_script() {
    global $dealership;

    if ( !empty( $dealership['bbb_script'] ) ) {
        $bbb_temp = htmlspecialchars_decode( $dealership['bbb_script'] );
        $bbb_temp = preg_replace("/&rsquo;/", "'", $bbb_temp);
        return $bbb_temp;
    } else {
        return '<a href="http://www.bbb.org/oregon/business-reviews/auto-dealers-new-cards/dick-hannah-in-vancouver-wa-50003328" title="Dick Hannah Auto Dealerships are BBB Accredited Businesses" target="_blank"><img src="'.image_url("bbb-seal.png").'" width="159" height="60" alt="Accredited Portland & Vancouver Car Dealerships" /></a>';
    }
}

/**
 * Cut string to n symbols and add delim but do not break words.
 * @see http://www.justin-cook.com/wp/2006/06/27/php-trim-a-string-without-cutting-any-words/
 *
 * Example:
 * <code>
 *  $string = 'this sentence is way too long';
 *  echo neat_trim($string, 16);
 * </code>
 *
 * Output: 'this sentence is...'
 *
 * @access public
 * @param string string we are operating with
 * @param integer character count to cut to
 * @param string|NULL delimiter. Default: '...'
 * @return string processed string
 **/
function neat_trim($str, $n, $delim='...') {
   $len = strlen($str);
   if ($len > $n)
   {
       preg_match('/(.{'.$n.'}.*?)\b/',$str,$matches);
       return rtrim($matches[1]) . $delim;
   }
   else
   {
       return $str;
   }
}


function googlesitesearch(){
    $allmakes = get_results('SELECT DISTINCT make FROM inventory');
    $makes=array();
    foreach($allmakes as $row){
        if(trim($row['make'])!='')
        $makes[] = "'".strtolower(trim($row['make']))."'";
    }
    $allmodels = get_results('SELECT DISTINCT model FROM inventory');
    $models = array();
    foreach($allmodels as $row){
        if(trim($row['model'])!=''){
            $models[] = "'".strtolower(trim($row['model']))."'";
        }
    }
    $years = array();
    for($i=1970; $i<=(int)(date('Y')+1); $i++){
        $years[] = "'".$i."'";
    }
    ?>
<script type="text/javascript">
function gss(){
    //console.log('clicked');
    var defaultstring = 'New, used, makes, etc.';
    var uri=new String('');
    // Build ignored array
    var ignored=new Array('car','cars','auto','autos');
    // Build conditions array
    var conditions=new Array('new','used');
    var matched_conditions = new Array();
    // Build years array
    var years=new Array(<?php echo implode(',',$years) ?>);
    var matched_years=new Array();
    // Build makes array
    var makes=new Array(<?php echo implode(',',$makes) ?>);
    var matched_makes=new Array();
    // Build models array
    var models=new Array(<?php echo implode(',',$models) ?>);
    var matched_models=new Array();
    // Build the stock number array
    var stocknumbers=new Array(<?php echo implode(',',dh_get_stocknumber_list()) ?>);
    var matched_stocknumbers=new Array();

    var q = $('#gssk').val();
    if (q != defaultstring) {
        var keywords = new String(q);
        // strip ignored words
        for (var found in ignored) {
            keywords = keywords.replace(ignored[found],'');
        }
        keywords = keywords.replace('   ',' ');
        keywords = keywords.replace('  ',' ');
        keywords = keywords.split(' ');
        //if there is at least 1 keyword
        if (keywords.length>0) {
            for (var i=0;i<keywords.length;i++) {
                var keyword = keywords[i];
                keyword = keyword.toLowerCase();
                for (var c in conditions) {
                    if(keyword==conditions[c]){
                        matched_conditions.push(keyword);
                    }
                }
                for (var y in years) {
                    if(keyword==years[y]){
                        matched_years.push(keyword);
                    }
                }
                for (var k in makes) {
                    if (keyword==makes[k]) {
                        matched_makes.push(keyword);
                    }
                }
                for (var d in models) {
                    if (keyword==models[d]) {
                        matched_models.push(keyword);
                    }
                }
                for (var s in stocknumbers) {
                    if(keyword.toUpperCase()==stocknumbers[s]){
                        matched_stocknumbers.push(keyword.toUpperCase());
                    }
                }
            }
            if (matched_conditions.length>0) {
                var condition_search=matched_conditions.pop();
            }
            if (matched_years.length>0) {
                var year_search=matched_years.pop();
            }
            if (matched_makes.length>0) {
                var make_search=matched_makes.pop();
            }
            if (matched_models.length>0) {
                var model_search=matched_models.pop();
            }
            if (matched_stocknumbers.length>0) {
                var stocknumber_search=matched_stocknumbers.pop();
            }
            if (stocknumber_search) {
                uri+='/for-sale/'+stocknumber_search;
            }else{
                if (condition_search) {
                    uri+='/'+condition_search;
                    if (make_search) {
                        uri+='/'+make_search;
                    }
                    if (model_search) {
                        uri+='/'+model_search;
                    }
                }else{
                    if (make_search) {
                        uri='/search/make/'+make_search;
                    }
                    if (model_search) {
                        uri='/search/model/'+model_search;
                    }
                    if (year_search) {
                       uri+='/search/year/'+year_search
                    }
                }
            }
            if(uri.length>0){
                window.location=uri
                return false;
            }else{
                // Nothing useful was found in the search.
                // Return true so that Google Site Search can try.
                console.log('Google Search');
                return true;
            }
        }else{
            alert('Search for something... like `new honda`, `used kia` or `directions`');
            return false;
        }
    }else{
        $('#gssk').val('New, used, makes, etc.');
        return false;
    }
}
</script>
<?php
}

function get_parts_info($field=NULL)
{
    global $dealership;
    $row = get_row("SELECT * FROM department WHERE dealership_id = ". $dealership['dealership_id'] ." AND name='Parts' ");
    if(is_null($field)){
        return $row;
    }
    else
    {
        return $row[$field];
    }
}
function get_service_info($field=NULL)
{
    global $dealership;
    $row = get_row("SELECT * FROM department WHERE dealership_id = ". $dealership['dealership_id'] ." AND name='Service' ");
    if(is_null($field)){
        return $row;
    }
    else
    {
        return $row[$field];
    }
}

function sqlSafeModelName($urlModelName){
    $modelnamelike = preg_replace('#[_\s\-]#','%',$urlModelName);
    global $wpdb;
    $models = array();
    // SELECT DISTINCT model FROM inventory WHERE model LIKE '%%town%%%country%%'
    $distinct_models = get_results(" SELECT DISTINCT model FROM inventory WHERE model LIKE '". $modelnamelike ."' ");
    return $distinct_models[0]['model'];

}

function get_new_model_years()
{
    global $dealership;
    global $wpdb;
    if($dealership['is_global'])
    {
        $results = get_results( " SELECT DISTINCT year FROM inventory ORDER BY year DESC LIMIT 3 ");
    }else{
        $results = get_results( " SELECT DISTINCT year FROM inventory
                                  JOIN dealership_make ON inventory.make = dealership_make.make
                                  JOIN dealership ON dealership_make.dealership_id = dealership.dealership_id
                                  WHERE dealership.dealership_id = ". $dealership['dealership_id'] ."
                                  ORDER BY year DESC LIMIT 3
                                             ");
    }
    $years = array();
    foreach($results as $row){
        $years[] = $row['year'];
    }
    return $years;
}

/**
 * Check whether the dealership sells a make
 * @global Array $dealership
 * @global Instance $wpdb
 * @param String $make
 * @return Boolean TRUE if so, FALSE otherwise
 */
function dealership_sells_make($make)
{
    global $dealership;
    $sql = " SELECT dealership_make.make FROM dealership_make
        JOIN dealership ON dealership_make.dealership_id = dealership.dealership_id
        WHERE dealership.dealership_id = ". $dealership['dealership_id'] ;
    $results = get_results($sql);
    if(is_array($results) && count($results)>=1)
    {
        foreach($results as $row)
        {
            if(strtolower($row['make'])==strtolower($make))
            {
                return true;
            }
        }
    }
    return false;
}

/**
 * Modeling shortcode for displaying inventory items
 *
 * @global Array $dealership    Global dealership variable
 * @param type $atts            Incoming attributes from shortcode
 * @return string               HTML string to display items

 */
function model_widget_func( $atts ) {
    global $dealership;
    $model_found = false;
    $model_where = "";
    extract( shortcode_atts( array(
        'title'     => '',
        'model'     => '',
        'new_used'  => '',
        'order_by'  => 'random',
        'sort_by'   => ''
    ), $atts ) );

    if( "{$model}" != '' ) {
        $model_found = true;
        $model_where = " WHERE i.model='{$model}' ";
    }

    switch( strtolower("{$new_used}") ) {
        case "new":
            if( $model_found ) {
                $model_where .= " AND i.new_used='N'";
            } else {
                $model_where = " WHERE i.new_used='N' ";
            }
            $permalink_name = "New";
            $car_type = "N";
            break;
        case "used":
            if( $model_found ) {
                $model_where .= " AND i.new_used='U' ";
            } else {
                $model_where = " WHERE i.new_used='U' ";
            }
            $permalink_name = "Used";
            $car_type = "U";
            break;
        default:
            $permalink_name = "New";
            $car_type = "N";
            break;
    }

    switch( "{$order_by}" ) {
        case "inventory":
            $order_by = "inventory_date";
            break;
        case "price":
            $order_by = "price";
            break;
        case "random":
        default:
            $order_by = "RAND()";
            break;
    }

    if( $dealership['is_global'] ) {
        $query = "SELECT i.*, SUBSTRING_INDEX(i.photo_url_list, '|', 1) first_photo_url
            FROM inventory i
            JOIN dealership d ON s.dealership_id = d.dealership_id " . $model_where . "
            ORDER BY " . $order_by . " " . $sort_by . " LIMIT 3";
    } else {
        $query = "SELECT i.*, SUBSTRING_INDEX(i.photo_url_list, '|', 1) first_photo_url
            FROM inventory i
            JOIN dealership_make dm ON i.make=dm.make " . $model_where . "
            ORDER BY " . $order_by . " " . $sort_by . " LIMIT 3";
    }

    $models = get_results( $query );

    $permalink_params = "#page=&car-type=" . $car_type . "&make=" . $dealership['make'] . "&model={$model}";
    $html = "<span class='widgettitle'>{$title} <a href='" . permalink( $permalink_name ) . $permalink_params . "'>See all</a> </span><ul class='widget_summaries'>";

    if( count($models) != 0 ) {
        foreach( $models as $vehicle ) {
            $html .= "<li class='widget_summary'>";
            $html .= "<img src='" . vehicle_thumbnail( $vehicle["first_photo_url"] ) . "' width='88' height='66' />";
            $html .= trim(vehicle_label($vehicle)) . "<br />";
            $html .= "<span class='our_price'><strong>" . dh_get_price_label( $vehicle['new_used'] ) . "</strong> " . vehicle_price( $vehicle["price"], 'our' ) . "</span><br/>";

			if($vehicle['make'] == 'Acura' AND $vehicle['new_used'] == 'N'){
			} else{
            $html .= "<strong>" . dh_get_price_label( $vehicle['new_used'], 'compare_to_price' ) . "</strong>";
			$html .= " <span class='msrp'>" . vehicle_price( $vehicle["compare_to_price"], 'compare_to_price' ) . "</span>";
			}
			
            $html .= "<a class='more_link' href='" . vehicle_url_returned( $vehicle ) . "'>More details</a></li>";
        }
    } else {
        $html .= "<li class='widget_summary'>";
        $html .= "Currently out of stock but we have new inventory arriving daily, <a href='/about/contact' title=''>click here</a> to be notified when one arrives.";
        $html .= "</li>";
    }

    $html .= "</ul>";

    return $html;
}
add_shortcode( 'model_widget', 'model_widget_func' );

function show_services_umbrella() { 
	
		include ('services-umbrella.php');
	
 }
add_shortcode( 'services', 'show_services_umbrella' ); 

function number_trees_planted () {
	global $dealership;
	$trees = number_format($dealership['trees_planted']);
	return $trees;
}
add_shortcode('NumberOfTrees', 'number_trees_planted');
if ($dealership['dealership_id'] == 11) {
	function quick_search_shortcode($atts) { 
		extract( shortcode_atts( array(
			'type' => 'something',
		), $atts ) );
		include('ajax/quick_search.php');
		$html = "<div id='quick_search_place'></div>\n";
		$html .= "<script type='text/javascript' >\n";
		$html .= "var html = $('#quick_search1').wrap('<p/>').parent().html();\n";
		$html .= "$('#quick_search_place').html(html);\n";
		$html .= "$('#quick_search1').html('');\n";
		$html .= "$('#quick_search1').unwrap();";
		$html .= "</script>\n";
		
		return $html;
	}
	function reviews_shortcode() { 
		$html = "<div id='customer_reviews'>";
		$html .= "Customer Reviews Widget code";
		$html .= "</div>";
		return $html; 
	}
	add_shortcode('QuickSearch', 'quick_search_shortcode');
	add_shortcode('Reviews', 'reviews_shortcode');
}
if ($dealership['dealership_id'] == 11 OR $dealership['dealership_id'] == 8 OR $dealership['dealership_id'] == 5 OR $dealership['dealership_id'] == 2 ) {
	$role = get_role('store');
//	$role -> add_cap('read');
	$role -> add_cap('manage_reviews');
}


function convert_smart_quotes($string)
{
$search = array(chr(145),
chr(146),
chr(147),
chr(148),
chr(151));

$replace = array("'",
"'",
'"',
'"',
'-');

return str_replace($search, $replace, $string);
}

remove_action( 'wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_generator');

$globalCannon = array('/believe-in-nice', '/believe-in-nice/about', '/believe-in-nice/loyalty-rewards-program', '/believe-in-nice/peace-of-mind', '/trade-or-sell', '/trade-or-sell/trade-in-your-vehicle', '/trade-or-sell/sell-your-vehicle', '/trade-or-sell/frequently-asked-questions', '/trade-or-sell/the-appraisal-process', '/careers', '/openings' );

foreach ($globalCannon as $uri) {
	if ($_SERVER['REQUEST_URI'] == $uri) {
		remove_action('wp_head', 'rel_canonical');
		add_action('wp_head', function() { ?>
			<link rel='canonical' href='<?php echo $_SERVER['HTTPS'] ? 'https:' : 'http:'; ?>//www.dickhannah.com<?php echo $_SERVER['REQUEST_URI']?>'>
		<?php
		});
	}
}

if( isset($_SERVER['HTTPS'] ) ) {
	remove_action( 'wp_head', 'remote_login_js_loader' );
}


if (strpos($_SERVER['REQUEST_URI'], 'vehicle/') != false || strpos($_SERVER['REQUEST_URI'], 'mobile-vehicle') != false || strpos($_SERVER['REQUEST_URI'], 'for-sale/') != false) {
	remove_action('wp_head', 'rel_canonical');
}


if(dh_get_template_name() == "search-drs.php") {
echo "asdfsdf";
/*
// Remove Canonical Link Added By Yoast WordPress SEO Plugin
function at_remove_dup_canonical_link() {
  return false;
}
add_filter( 'wpseo_canonical', 'at_remove_dup_canonical_link' );
*/
}


//=============================================
//              WP-CRON Events
//=============================================
if ($dealership['is_global']) {

  if( false !== ( $time = wp_next_scheduled( 'wp-cron-test' ) ) ) {  
       wp_unschedule_event( $time, 'wp-cron-test' );  
    } 
 /* 
    if( !wp_next_scheduled( 'wp-cron-test' ) ) {  
       wp_schedule_event( current_time('timestamp') + 2000, 'hourly', 'wp-cron-test' );
    }  
 */
    add_action( 'wp-cron-test', 'confirm_wp_cron' );

    function confirm_wp_cron() {
        mail('nhill@dickhannah.com', 'WP Cron Test', 'This is a test of WP Cron. Message sent hourly.');
    }


  if( false !== ( $time = wp_next_scheduled( 'google_grab' ) ) ) {  
       wp_unschedule_event( $time, 'google_grab' );  
    } 
/*  
    if( !wp_next_scheduled( 'google_grab' ) ) {  
       wp_schedule_event( current_time('timestamp') + 2000, 'hourly', 'google_grab' );
    }  
*/ 
    add_action( 'google_grab', 'get_google_rating' );

    function get_google_rating() {
        global $wpdb;

        $dealershipGoogleQuery = "SELECT * FROM  `dealership` WHERE  `google_places_ref` <>  ''";             
        $dealerships = get_results($dealershipGoogleQuery);

        foreach ($dealerships as $dealershipUnique) {
            $api_url = "https://maps.googleapis.com/maps/api/place/details/xml?reference=".$dealershipUnique['google_places_ref']."&sensor=false&key=AIzaSyCY895U-gY4jEjno0Pf1atHN7y51G-I6RE";
            $api = curl_init("$api_url");
            curl_setopt($api, CURLOPT_RETURNTRANSFER, true);
            $api_response = curl_exec($api);
            curl_close($api);

            if($api_response !== false){
                //echo "<pre>\n";
                //var_dump($api_response);
                //echo "</pre>\n";
                if( stristr($api_response,'Error')){
                    $xml_response_received = false;
                } else {
                   $api_xml = new SimpleXMLExtended($api_response);
                    $rating = $api_xml->xpath("/PlaceDetailsResponse//rating");
                    $ratingTotal = $api_xml->xpath("/PlaceDetailsResponse//user_ratings_total");
                    $reviews = $api_xml->xpath("/PlaceDetailsResponse//review");

                    $xml_response_received = true;
                }
            } 

            if ($xml_response_received)
            {
                $googleRating = $rating[0];
                $googleReviewsTotal = $ratingTotal[0];


                $GoogleRatingQuery = "UPDATE `dealership` SET google_rating = '{$googleRating}', google_reviews_total = '{$googleReviewsTotal}' WHERE dealership_id = {$dealershipUnique['dealership_id']}";             
                mysql_query($GoogleRatingQuery) or die ('Error updating the dealership table');

                $nodeCount = 0;
                while(list( , $node) = each($reviews)) {
                    $nodeCount++;
                    $GoogleReviewQuery = "UPDATE `dealership_google_reviews` SET review_rating = '{$node->rating}', review_author = '{$node->author_name}', review_text = '{$node->text}' WHERE dealership_id = {$dealershipUnique['dealership_id']} AND review_number = {$nodeCount}";
                    mysql_query($GoogleReviewQuery) or die ('Error updating dealership_google_reviews table');
                }
            
            }

        }

    } 

	if( false !== ( $time = wp_next_scheduled( 'review_followup' ) ) ) {  
	   wp_unschedule_event( $time, 'review_followup' );  
	} 
/*	
	if( !wp_next_scheduled( 'review_followup' ) ) {  
	   wp_schedule_event( current_time('timestamp'), 'daily', 'review_followup' );
	}  
*/
	add_action( 'review_followup', 'send_review_followup' );

	function send_review_followup() {
		global $dealership;
		
		$review_date_check = date('Y-m-d', time() - 172800);
		for($store = 2; $store < 19; $store++) {
			$filters = "
				 customer_review cr 
			JOIN dealership_customer_review dcr ON dcr.customer_review_id=cr.customer_review_id 
			WHERE cr.review_date = '".$review_date_check."' AND dcr.dealership_id = ".$store;

			$query = "SELECT * FROM $filters ORDER BY create_dt DESC";
			$reviews_for_followup = get_results($query);
			$sales_reviews = '';
			$service_reviews = '';
			foreach($reviews_for_followup as $review) {
				if ((int)$review['rating'] >= 4) {
					if ($review['category'] == 'New Sales' OR $review['category'] == 'Used Sales' OR $review['category'] == 'Sales') {
						$sales_reviews .= $review['name']."\r\n";
						$sales_reviews .= $review['email']."\r\n";
						$sales_reviews .= $review['review_date']."\r\n";
						$sales_reviews .= stripslashes($review['testimonial'])."\r\n";
						$sales_reviews .= "================================================================\r\n";
					} elseif ($review['category'] == 'Service' OR $review['category'] == 'Parts') {
						$service_reviews .= $review['name']."\r\n";
						$service_reviews .= $review['email']."\r\n";
						$service_reviews .= $review['review_date']."\r\n";
						$service_reviews .= preg_replace("/&rsquo;/", "'",stripslashes($review['testimonial']))."\r\n";
						$service_reviews .= "================================================================\r\n";
					}
				}
			}
			$this_dealership = dh_get_dealership_from_id($store);
			$dealership_dept = get_row("SELECT * FROM department WHERE is_active=1 AND name='Dealership' AND dealership_id={$this_dealership["dealership_id"]}");
			$other_depts = get_results("SELECT * FROM department WHERE is_active=1 AND name!='Dealership' AND dealership_id={$this_dealership["dealership_id"]} ORDER BY name ASC");
			$headers = 'From: review.followup@dickhannah.com' . "\r\n";
			$to = $dealership_dept['manager_email'].", ".$dealership['cm_email'].', jgates@dickhannah.com';
			
			if (!empty($sales_reviews) AND $sales_reviews != '') {
				$to .= ", ".dh_get_make_from_dealership_id($this_dealership["dealership_id"])."-reviews@dickhannah.com";
				mail($to, 'Sales Reviews for Followup', $sales_reviews, $headers);
			}
			if (!empty($service_reviews) AND $service_reviews != '') {
				foreach($other_depts as $dept){
					if ($department == $dept['name']) {
						$to .= ", ".$dept['manager_email'];
					}
				}
				$to .= ", ".$dealership['corp_fod_email'].", servicecallcenter@dickhannah.com";
				mail($to, 'Service Reviews for Followup', $service_reviews, $headers);
			}
		} 
	} 
        
        
	if( false !== ( $time = wp_next_scheduled( 'review_adf' ) ) ) {  
	   wp_unschedule_event( $time, 'review_adf' );  
	} 
/*	
	if( !wp_next_scheduled( 'review_adf' ) ) {  
	   wp_schedule_event( current_time('timestamp'), 'daily', 'review_adf' );
	}  
*/
	add_action( 'review_adf', 'send_review_adf' );
	
function my_login_logo() { ?>
<style type="text/css">
	.login h1 a {
		background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/images/DHD_BIN_285x80.png);
		padding-bottom: 20px;
		-webkit-background-size: 200px;
		background-size: 200px;
		width: 100%;
	}
</style>
<?php }
add_action( 'login_enqueue_scripts', 'my_login_logo' );

	function send_review_adf() {

		$review_date_check = date('Y-m-d', time() - 172800);
		for($store = 2; $store < 19; $store++) {
			$filters = "
				 customer_review cr 
			JOIN dealership_customer_review dcr ON dcr.customer_review_id=cr.customer_review_id 
			WHERE cr.review_date = '".$review_date_check."' AND dcr.dealership_id = ".$store;

			$query = "SELECT * FROM $filters ORDER BY create_dt DESC";
			$reviews_for_adf = get_results($query);
                        
                        $this_dealership = dh_get_dealership_from_id($store);
                        
			foreach ($reviews_for_adf as $review) {
                          if ($review['category'] === 'Sales' AND !empty($review['last_name'])) {
                            $car = array();
                            $firstname = explode(" ", $review['name']);
                            $phone = htmlspecialchars($review['phone']);
                            $adf = new SimpleXMLExtended("<adf></adf>");
                            $prospect = $adf->addChild("prospect");
                            $prospect->addChild("requestdate", $review['create_dt']);
                            $vehicle = $prospect->addChild("vehicle");
                            $vehicle->addAttribute("status", strtolower(vehicle_type($car)));
                            $vehicle->addChild("year", $car["year"]);
                            $vehicle->addChild("make", htmlspecialchars($car["make"]));
                            $vehicle->addChild("model", htmlspecialchars($car["model"]));
                                $vehicle->addChild("body", $car["body"]);
                                $vehicle->addChild("odometer", $car["odometer"]);
                            $vehicle->addChild("stock", $car["stock_number"]);
                                $vehicle->addChild("vin", $car["vin"]);
                            $customer = $prospect->addChild("customer");
                            $contact = $customer->addChild("contact");
                            $contact->addChild("name", $firstname[0])->addAttribute("part", "first");
                            $contact->addChild("name", $review['last_name'])->addAttribute("part", "last");
                            $contact->addChild("phone", $phone);
                            $contact->addChild("email", $review['email']);
                                $address = $contact->addChild("address");
                                $address->addChild("street", htmlspecialchars($_POST["address_one"]));
                                $address->addChild("apartment", htmlspecialchars($_POST["address_two"]));
                                $address->addChild("city", htmlspecialchars($_POST["city"]));
                                $address->addChild("regioncode", htmlspecialchars($_POST["state"]));
                                $address->addChild("postalcode", htmlspecialchars($_POST["zipcode"]));
                            $customer->addChild("comments")->addCData($review['testimonial']);
                            $vendor = $prospect->addChild("vendor");
                            $vendor_contact = $vendor->addChild("contact");
                            $vendor_contact->addChild("name", $this_dealership["name"])->addAttribute("part", "full");
                            $provider = $prospect->addChild("provider");
                                
                            $provider->addChild("name", $_SERVER['HTTP_HOST'].'-'.'Review')->addAttribute("part", "full");
                          
                            $provider->addChild("service", $_SERVER['HTTP_HOST'].'-'.'Review');

                            $message = "<?ADF VERSION \"1.0\"?".">\r\n".$adf->prettyPrint();
                            mail($this_dealership['crm_email'], "Review: ".$review["email"], $message);
                            mail('leads@dickhannah.com', "Review: ".$review["email"], $message);
                          }
                        }

		} 
	}
}




/*
	Walker class for Nav menu
*/

// Walker class for second level of mobile menu
class Walker_Nav_Menu_Mobile_Sub extends Walker {
	/**
	 * @see Walker::$tree_type
	 * @since 3.0.0
	 * @var string
	 */
	var $tree_type = array( 'post_type', 'taxonomy', 'custom' );

	/**
	 * @see Walker::$db_fields
	 * @since 3.0.0
	 * @todo Decouple this.
	 * @var array
	 */
	var $db_fields = array( 'parent' => 'menu_item_parent', 'id' => 'db_id' );

	function display_element( $element, &$children_elements, $max_depth, $depth=0, $args, &$output )
    {
        $id_field = $this->db_fields['id'];
        if ( is_object( $args[0] ) ) {
			$args[0]->id = $element->$id_field;
        }
        return parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
    }
	
	
	/**
	 * @see Walker::start_lvl()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of page. Used for padding.
	 */
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		global $wp_query;
		$pgid = $args->id; 
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
		if ($depth == 0) {
			$output .= "\n$indent<div class=\"MobileSubItemWrapper SubMenuHide\" id=\"SubNav".$pgid."WrapperId\">\n";
			$output .= "\n$indent<div class=\"MobileSubItemInnerWrapper\" >\n";
		}
	}

	/**
	 * @see Walker::end_lvl()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of page. Used for padding.
	 */
	function end_lvl( &$output, $depth = 0, $args = array() ) {
		if ($depth == 0) {
			$output .= "\n$indent</div>\n";
			$output .= "\n$indent</div>\n";
		}
	}

	/**
	 * @see Walker::start_el()
	 * @since 3.0.0 
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Menu item data object.
	 * @param int $depth Depth of menu item. Used for padding.
	 * @param int $current_page Menu item ID.
	 * @param object $args
	 */
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		global $wp_query;
			if ($depth == 1 && strlen($item->title) > 4) {
				if($item->title != "Quick Search") {
				$output .= $indent . '<div class="s2d_MenuTitleDiv">';
				$output .= '<div class="s2d_MenuTitle">';
				$item_output .= apply_filters( 'the_title', $item->title, $item->ID );
				}
			} elseif ($depth == 2) {
				$output .= $indent . '<div class="s2d_MenuItemDiv">';
				$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
				$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
				$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
				$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

				$item_output = $args->before;
				
				$item_output .= '<a class="s2d_MenuItem" '. $attributes .'>';

				$item_output .= apply_filters( 'the_title', $item->title, $item->ID );
				$item_output .= '</a>';
				$item_output .= $args->after;		
			}
			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
			if ($depth == 1 && strlen($item->title) > 4) {
				$output .= $indent . '</div>';
				$output .= $indent . '</div>'."\n";			
			}
		
	}

	/**
	 * @see Walker::end_el()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Page data object. Not used.
	 * @param int $depth Depth of page. Not Used.
	 */
	function end_el( &$output, $item, $depth = 0, $args = array() ) {
		if ($depth == 2) {
			$output .= $indent . '</div>'."\n";
		
		}
	}
}


// Walker class for the first level of the mobile menu
class Walker_Nav_Menu_Mobile extends Walker {
	/**
	 * @see Walker::$tree_type
	 * @since 3.0.0
	 * @var string
	 */
	var $tree_type = array( 'post_type', 'taxonomy', 'custom' );

	/**
	 * @see Walker::$db_fields
	 * @since 3.0.0
	 * @todo Decouple this.
	 * @var array
	 */
	var $db_fields = array( 'parent' => 'menu_item_parent', 'id' => 'db_id' );

	/**
	 * @see Walker::start_lvl()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of page. Used for padding.
	 */
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		
	}

	/**
	 * @see Walker::end_lvl()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of page. Used for padding.
	 */
	function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		
	}

	/**
	 * @see Walker::start_el()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Menu item data object.
	 * @param int $depth Depth of menu item. Used for padding.
	 * @param int $current_page Menu item ID.
	 * @param object $args
	 */
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		global $wp_query;
		$pgid = $item->ID; 
		
		if($depth == 0) {
			$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
			$output .= $indent . '<div class="s2d_MenuItemDivWrapper">';
			$output .= '<div class="s2d_MenuItemDiv">';
			
			$class_names = $value = '';

			$classes = empty( $item->classes ) ? array() : (array) $item->classes;
			$classes[] = 'menu-item-' . $item->ID;

			$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
			$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

			$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
			$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';
			
			$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';

			$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
			$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
			if($item->title == "Used") {
			$attributes .= ! empty( $item->url )        ? ' href="/used"' : '';
			} else {
			$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
			}

			$item_output = $args->before;
			if($item->title == "Used") {
			$item_output .= '<a class="s2d_MenuItem" id="MobNavLink'.$pgid.'" '. $attributes .' >';
			} else {
			$item_output .= '<a class="s2d_MenuItem" id="MobNavLink'.$pgid.'" '. $attributes .' onclick="SlideSub(\'ClickNav'.$pgid.'\'); return false;" >';
			}
			$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
			$item_output .= '</a>';
			$item_output .= $args->after;

			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}
	}

	/**
	 * @see Walker::end_el()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Page data object. Not used.
	 * @param int $depth Depth of page. Not Used.
	 */
	function end_el( &$output, $item, $depth = 0, $args = array() ) {
		if($depth == 0) {
			$output .= "</div>";
			$output .= "</div>\n";
		}
	}
}

//  Walker class for the desktop/tablet menu
class Walker_Nav_Menu_Blank extends Walker {
	/**
	 * @see Walker::$tree_type
	 * @since 3.0.0
	 * @var string
	 */
	var $tree_type = array( 'post_type', 'taxonomy', 'custom' );

	/**
	 * @see Walker::$db_fields
	 * @since 3.0.0
	 * @todo Decouple this.
	 * @var array
	 */
	var $db_fields = array( 'parent' => 'menu_item_parent', 'id' => 'db_id' );

	/**
	 * @see Walker::start_lvl()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of page. Used for padding.
	 */
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
		
		if ($depth == 0) {
			$output .= "\n$indent</div><!-- start_lvl 0 1 END MenuItem-->\n";
			$output .= "\n$indent<div class=\"SubItemWrapper\" >\n";
			$output .= "\n$indent<div class=\"SubItemInnerWrapper\" >\n";
		} elseif ($depth == 1) {
			$output .= "\n$indent</div><!-- start_lvl 1 1 END MenuItemSub MenuItemSubTitle-->\n";
		}
	}

	/**
	 * @see Walker::end_lvl()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of page. Used for padding.
	 */
	function end_lvl( &$output, $depth = 0, $args = array() ) {
		if ($depth == 0) {
			$output .= $indent . '</div><!-- end_lvl 0 1 -->'."\n";
		}
	}

	/**
	 * @see Walker::start_el()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Menu item data object.
	 * @param int $depth Depth of menu item. Used for padding.
	 * @param int $current_page Menu item ID.
	 * @param object $args
	 */
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		global $wp_query;
		
		if ($depth == 0) {
			$output .= $indent . '<div class="MenuItemWrapper">'."\n";
			$output .= $indent . '<div class="MenuItem">'."\n";
			
			$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
			$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
			$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
			$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

			$item_output = $args->before;
			$item_output .= '<a'. $attributes .'>';
			$item_output .= apply_filters( 'the_title', $item->title, $item->ID );
			$item_output .= '</a>'."\n";
			$item_output .= $args->after;
		
		} elseif ($depth == 1) {
			if($item->title == "Quick Search") {
				$output .= "<div id=\"home_vehicle_search\"></div>";
			}	else {
			$output .= "\n$indent<div class=\"subwrap5\" >\n";
			$output .= "\n$indent<div class=\"MenuItemSub MenuItemSubTitle\" >\n";
			
			$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
			$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
			$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
			$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
			
			$item_output = $args->before;
			$item_output .= apply_filters( 'the_title', $item->title, $item->ID );
			$item_output .= "\n";
			$item_output .= $args->after;
			}
		} elseif ($depth == 2) {
			
			$output .= $indent . '<div class="MenuItemSub">'."\n";
			$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
			$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
			$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
			$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

			$item_output = $args->before;
			
			$item_output .= '<a'. $attributes .'>';

			$item_output .= apply_filters( 'the_title', $item->title, $item->ID );
			$item_output .= '</a>'."\n";
			$item_output .= $args->after;
			
			
		}
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	/**
	 * @see Walker::end_el()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Page data object. Not used.
	 * @param int $depth Depth of page. Not Used.
	 */
	function end_el( &$output, $item, $depth = 0, $args = array() ) {
		if ($depth == 0) {
			$output .= $indent . '</div><!-- end_el 0 1 -->';
			$output .= $indent . '</div><!-- end_el 0 2 -->';
		}
		else if ($depth == 1) {
			if($item->title != "Quick Search") {
			$output .= $indent . '</div><!-- end_el 1 1 -->';
			}
		}
		else if ($depth == 2) {
			$output .= $indent . '</div><!-- end_el 2 1 -->';
		}
	}
}


//  Walker class for the footer menu
class Walker_Nav_Menu_Footer extends Walker {
	static $count = 0;
	/**
	 * @see Walker::$tree_type
	 * @since 3.0.0
	 * @var string
	 */
	var $tree_type = array( 'post_type', 'taxonomy', 'custom' );

	/**
	 * @see Walker::$db_fields
	 * @since 3.0.0
	 * @todo Decouple this.
	 * @var array
	 */
	var $db_fields = array( 'parent' => 'menu_item_parent', 'id' => 'db_id' );

	/**
	 * @see Walker::start_lvl()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of page. Used for padding.
	 */
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
	}

	/**
	 * @see Walker::end_lvl()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of page. Used for padding.
	 */
	function end_lvl( &$output, $depth = 0, $args = array() ) {

	}

	/**
	 * @see Walker::start_el()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Menu item data object.
	 * @param int $depth Depth of menu item. Used for padding.
	 * @param int $current_page Menu item ID.
	 * @param object $args
	 */
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		global $wp_query;
		
			if($depth == 0) {
				$output .= "\n$indent<div class=\"FootNav_Column\" >\n";
				$output .= $indent . '<div class="FootNav_Header">';
			} else {
				$output .= $indent . '<div class="FootNav_Item">';
			}
			$output .= $item->index."\n";
			
			$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
			$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
			$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
			$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

			$item_output = $args->before;
			if($depth > 0) {
			$item_output .= '<a'. $attributes .'>';
			}
			$item_output .= apply_filters( 'the_title', $item->title, $item->ID );
			if($depth > 0) {
			$item_output .= '</a>'."\n";
			}
			$item_output .= $args->after;
		
		self::$count++;
		
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	/**
	 * @see Walker::end_el()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Page data object. Not used.
	 * @param int $depth Depth of page. Not Used.
	 */
	function end_el( &$output, $item, $depth = 0, $args = array() ) {
		
			$output .= $indent . '</div><!-- FootNav_Item -->'."\n";
			if ($depth == 0) {
				$output .= $indent . '</div><!-- end_lvl 0 1 FootNav_Column -->'."\n";
			}
	}
}


// add_theme_support( 'post-thumbnails', array( 'subieslingers', 'subieslingerclint' ) ); // Added Featured images for custom post types
add_theme_support( 'post-thumbnails' );
set_post_thumbnail_size( 150, 150 );
add_image_size( '300-square', 300, 300, true );
?>