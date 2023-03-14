<?php

/* Main menu - remove sub-items from main nav */
add_filter( 'wp_nav_menu_args', 'ilm_nav_main_menu' );
function ilm_nav_main_menu( $args ) {
	if ($args['theme_location'] == 'primary-menu') {
		$args['depth'] = 1;
//		echo var_dump($args);
	}
	if ($args['theme_location'] == 'primary-menu' && strpos($args['menu_class'], 'fullwidth-menu') !== false) {
		$args['sub_menu'] = true;
	}
	return $args;
}
    
/* Menu modifications */
add_filter('wp_nav_menu_items','ilm_menu_items', 10, 2);
function ilm_menu_items($items, $args) {
    #echo(var_dump($args));
    if ( $args->theme_location == 'secondary-menu' ) {
        //$items .= '<li class="menu-item><a href="/members/"></a></li>'
        $items = '<li class="menu-search"><form action="/" method="get"><input type="text" name="s" placeholder="Search"></form></li>'.$items;
    } elseif ( $args->theme_location == 'primary-menu' && strpos($args->menu_class, 'fullwidth-menu') === false ) {
        $items .= '<li class="menu-social"><a href="https://www.linkedin.com/company/institute-of-legacy-management"><img src="/wp-content/themes/ilm/img/icons/linkedin.png" /></a> <a href="https://twitter.com/Legacy_Mngment"><img src="/wp-content/themes/ilm/img/icons/twitter.png" /></a> <a href="mailto:support@legacymanagement.org.uk"><img src="/wp-content/themes/ilm/img/icons/email-b.png" /></a></li>';
    }
    return $items;
}

// Submenu
// https://christianvarga.com/how-to-get-submenu-items-from-a-wordpress-menu-based-on-parent-or-sibling/
add_filter( 'wp_nav_menu_objects', 'my_wp_nav_menu_objects_sub_menu', 10, 2 );
function my_wp_nav_menu_objects_sub_menu( $sorted_menu_items, $args ) {
  if ( isset( $args->sub_menu ) ) {
    $root_id = 0;
    
    // find the current menu item
    foreach ( $sorted_menu_items as $menu_item ) {
      if ( $menu_item->current ) {
        // set the root id based on whether the current menu item has a parent or not
        $root_id = ( $menu_item->menu_item_parent ) ? $menu_item->menu_item_parent : $menu_item->ID;
        break;
      }
    }
    
    // find the top level parent
    if ( ! isset( $args->direct_parent ) ) {
      $prev_root_id = $root_id;
      while ( $prev_root_id != 0 ) {
        foreach ( $sorted_menu_items as $menu_item ) {
          if ( $menu_item->ID == $prev_root_id ) {
            $prev_root_id = $menu_item->menu_item_parent;
            // don't set the root_id to 0 if we've reached the top of the menu
            if ( $prev_root_id != 0 ) $root_id = $menu_item->menu_item_parent;
            break;
          } 
        }
      }
    }
    $menu_item_parents = array();
    foreach ( $sorted_menu_items as $key => $item ) {
      // init menu_item_parents
      if ( $item->ID == $root_id ) $menu_item_parents[] = $item->ID;
      if ( in_array( $item->menu_item_parent, $menu_item_parents ) ) {
        // part of sub-tree: keep!
        $menu_item_parents[] = $item->ID;
      } else if ( ! ( isset( $args->show_parent ) && in_array( $item->ID, $menu_item_parents ) ) ) {
        // not part of sub-tree: away with it!
        unset( $sorted_menu_items[$key] );
      }
    }
    
    return $sorted_menu_items;
  } else {
    return $sorted_menu_items;
  }
}
