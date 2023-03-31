<?php

/* Event custom post type */
register_post_type(
    'event', [
        'labels' => [
            'name' => 'Events',
            'singular_name' => 'Event',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Event',
            'edit_item' => 'Edit Event',
            'new_item' => 'New Event',
            'view_item' => 'View Event',
            'view_items' => 'View Events',
            'search_items' => 'Search Events',
            'not_found' => 'No events found',
            'not_found_in_trash' => 'No events found in Trash',
            'all_items' => 'All Events',
            'archives' => 'Event Archives',
            'featured_image' => 'Featured Image',
            'set_featured_image' => 'Set featured image',
            'remove_featured_image' => 'Remove featured image',
            'use_featured_image' => 'Use as featured image'
        ],
        'public' => true,
        'has_archive' => false,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-calendar-alt',
        'rewrite' => ['slug' => 'events'],
        'supports' => [ 'title', 'editor', 'thumbnail', 'revisions' ],
        'taxonomies' => [ 'category', 'post_tag', 'event_type' ]
    ]
);


/* Partner custom post type */
register_post_type(
    'partner', [
        'labels' => [
            'name' => 'Partners',
            'singular_name' => 'Partner',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Partner',
            'edit_item' => 'Edit Partner',
            'new_item' => 'New Partner',
            'view_item' => 'View Partner',
            'view_items' => 'View Partners',
            'search_items' => 'Search Partners',
            'not_found' => 'No partners found',
            'not_found_in_trash' => 'No partners found in Trash',
            'all_items' => 'All Partners',
            'archives' => 'Partner Archives',
            'featured_image' => 'Featured Image',
            'set_featured_image' => 'Set featured image',
            'remove_featured_image' => 'Remove featured image',
            'use_featured_image' => 'Use as featured image'
        ],
        'public' => true,
        'has_archive' => false,
        'menu_position' => 6,
        'menu_icon' => 'dashicons-building',
        'rewrite' => ['slug' => 'partners'],
        'supports' => [ 'title', 'editor', 'thumbnail', 'revisions' ]
    ]
);

register_post_type(
    'organisation_details', [
        'labels' => [
            'name' => 'User organisation details',
            'singular_name' => 'Organisation details',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Organisation details',
            'edit_item' => 'Edit Organisation details',
            'new_item' => 'New Organisation details',
            'view_item' => 'View Organisation details',
            'view_items' => 'View Organisation details',
            'search_items' => 'Search Organisation details',
            'not_found' => 'No organisation details found',
            'not_found_in_trash' => 'No organisation details found in Trash',
            'all_items' => 'All Organisation details',
            'archives' => 'Organisation details Archives',
            'featured_image' => 'Featured Image',
            'set_featured_image' => 'Set featured image',
            'remove_featured_image' => 'Remove featured image',
            'use_featured_image' => 'Use as featured image'
        ],
        'public' => false,
        'has_archive' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 8,
        'menu_icon' => 'dashicons-id',
        'rewrite' => ['slug' => 'organisation-details'],
        'supports' => [ 'title', 'revisions' ]
    ]
);

register_taxonomy(
    'event_type', 'event', [
        'labels' => [
            'name' => 'Event Type',
            'singular_name' => 'Event Type',
            'menu_name' => 'Event Types',
            'all_items' => 'All Event Types',
            'edit_item' => 'Edit Event Type',
            'view_item' => 'View Event Type',
            'update_item' => 'Update Event Type',
            'add_new_item' => 'Add New Event Type',
            'search_items' => 'Search Event Types',
            'popular_items' => null,
            'separate_items_with_commas' => null,
            'add_or_remove_items' => null,
            'choose_from_most_used' => null,
            'not_found' => 'No event types found'
        ],
        'hierarchical' => true,
        'show_in_menu' => true,
        'show_admin_column' => true,
        'rewrite' => [ 'slug' => 'events/types', 'hierarchical' => true ]
    ]
);

register_post_type(
    'preferences', [
        'labels' => [
            'name' => 'User opt-in forms',
            'singular_name' => 'User opt-in form',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New User opt-in form',
            'edit_item' => 'Edit User opt-in form',
            'new_item' => 'New User opt-in form',
            'view_item' => 'View User opt-in form',
            'view_items' => 'View User opt-in forms',
            'search_items' => 'Search User opt-in forms',
            'not_found' => 'No User opt-in forms found',
            'not_found_in_trash' => 'No User opt-in forms found in Trash',
            'all_items' => 'All User opt-in forms',
            'archives' => 'User opt-in forms Archives',
            'featured_image' => 'Featured Image',
            'set_featured_image' => 'Set featured image',
            'remove_featured_image' => 'Remove featured image',
            'use_featured_image' => 'Use as featured image'
        ],
        'public' => false,
        'has_archive' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 9,
        'menu_icon' => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 256 256"><path d="M173.66,98.34a8,8,0,0,1,0,11.32l-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,148.69l50.34-50.35A8,8,0,0,1,173.66,98.34ZM224,48V208a16,16,0,0,1-16,16H48a16,16,0,0,1-16-16V48A16,16,0,0,1,48,32H208A16,16,0,0,1,224,48ZM208,208V48H48V208H208Z"></path></svg>'),
        'rewrite' => ['slug' => 'preferences'],
        'supports' => [ 'title', 'author' ]
    ]
);