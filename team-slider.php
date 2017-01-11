<?php
/*
Plugin Name: Wordpress Team Slider
*/

// Add team_members post type
function team_members_setup_post_types()
{
  register_post_type('team_members',
                      [
                        'labels' => [
                          'name' => __('Team Members'),
                          'singular_name' => __('Team Member'),
                        ],
                        'public' => true,
                        'has_archive' => false,
                        'rewrite' => ['slug' => 'team'],
                        'show_ui' => true,
                        'supports' => ['title', 'thumbnail'],
                      ]
  );
}
add_action('init', 'team_members_setup_post_types');

// Add Team Member Details and Social Networks to edit screen
function team_members_setup_post_metadata()
{
  add_meta_box('team_member_details_meta_box',
    'Team Member Details',
    'team_member_details_html',
    'team_members'
  );

  add_meta_box('team_member_social_networks_meta_box',
    'Team Member Social Networks',
    'team_member_social_networks_html',
    'team_members'
  );
}
add_action('add_meta_boxes', 'team_members_setup_post_metadata');

// Callback returns the HTML form for team member details
function team_member_details_html($post)
{
  $fields = [
    'name',
    'role',
    'email',
  ];

  foreach($fields as $field) {
    $$field = get_post_meta($post->ID, 'team_member_meta_' . $field, true);
  }
  ?>
  <table style="width: 100%;">
    <tr>
      <td style="width: 20%;">
        <label for="team_member_name">Name:</label>
      </td>
      <td>
        <input type="text" name="team_member_name" id="team_member_name" value="<?= $name ?>">
      </td>
    </tr>
    <tr>
      <td>
        <label for="team_member_role">Role:</label>
      </td>
      <td>
        <input type="text" name="team_member_role" id="team_member_role" value="<?= $role ?>">
      </td>
    </tr>
  </table>
  <?php
}

// Callback returns the HTML form for team member social networks
function team_member_social_networks_html($post)
{
  $fields = [
    'email',
  ];

  foreach($fields as $field) {
    $$field = get_post_meta($post->ID, 'team_member_meta_' . $field, true);
  }
  ?>
  <table style="width: 100%;">
    <tr>
      <td style="width: 20%;">
        <label for="team_member_email">Email Address:</label>
      </td>
      <td>
        <input type="text" name="team_member_email" id="team_member_email" value="<?= $email ?>">
      </td>
    </tr>
  </table>
  <?php
}

// Handle save of team member metadata on Publish/Update
function team_member_save_postdata($post_id)
{
  $fields = [
    'name',
    'role',
    'email',
  ];

  foreach($fields as $field) {
    if(array_key_exists('team_member_' . $field, $_POST)) {
      update_post_meta(
        $post_id,
        'team_member_meta_' . $field,
        $_POST['team_member_' . $field]
      );
    }
  }
}
add_action('save_post', 'team_member_save_postdata');

// Define column headings in team members list view
function team_members_custom_columns()
{
  $columns = [
    'cb' => '<input type="checkbox">',
    'title' => __('Title'),
    'name' => __('Name'),
    'role' => __('Role'),
    'featured_image' => __('Photo'),
    'date' => __('Date'),
  ];

  return $columns;
}
add_filter('manage_edit-team_members_columns', 'team_members_custom_columns');

// Populate list rows with team member post metadata
function team_members_populate_custom_columns($column, $post_id)
{
  if($column == 'featured_image') {
    $thumbnail_id = get_post_thumbnail_id($post_id);

    if(!empty($thumbnail_id)) {
      $thumbnail_img = wp_get_attachment_image_src($thumbnail_id);
      echo '<img src="' . $thumbnail_img[0] . '" style="height: 80px;">';
      return;
    }
  }

  $fields = [
    'name',
    'role',
    'email',
  ];

  if(in_array($column, $fields)) {
    $field_meta = get_post_meta($post_id, 'team_member_meta_' . $column, true);
    if(!empty($field_meta)) {
      echo $field_meta;
    }
  }
}
add_action('manage_team_members_posts_custom_column', 'team_members_populate_custom_columns', 10, 2);

// Functions to run on plugin activation
function team_members_install()
{
  team_members_setup_post_types();
  flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'team_members_install');

// Functions to run on plugin deactivation
function team_members_deactivation() {
  flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'team_members_deactivation');
