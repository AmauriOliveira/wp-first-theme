<?php

function api_post_product($request) {
  $user = wp_get_current_user();
  $user_id = $user->ID;

  if ($user_id === 0) {
    return rest_ensure_response(
      new WP_Error('error', 'Unauthorized: You must send the JWT token.', ['status' => 401]),
    );
  }

  $name = sanitize_text_field($request['name']);
  $description = sanitize_text_field($request['description']);
  $price = sanitize_text_field($request['price']);
  $post_user_id = $user->user_login;

  if (!$name || !$description || !$price) {
    return rest_ensure_response(
      new WP_Error('error', 'Conflict: `name`, `description` and `price` is required.', ['status' => 409]),
    );
  }

  $response = [
    'post_author' => $user_id,
    'post_title' => $name,
    'post_status' => 'publish',
    'post_type' => 'product',
    'meta_input' => [
      'name' => $name,
      'description' => $description,
      'price' => $price,
      'post_user_id' => $post_user_id,
      'sold' => 'false',
    ],
  ];

  $product_id = wp_insert_post($response);

  $response['id'] = get_post_field('post_name', $product_id);

  $files = $request->get_file_params();

  if ($files) {
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    foreach ($files as $file => $array) {
      media_handle_upload($file, $product_id);
    }
  }


  return rest_ensure_response($response);
}

function api_update_product($request) {
  $slug = $request['slug'];
  $product_id = get_product_id_by_slug($slug);
  $user = wp_get_current_user();
  $user_id = (int) $user->ID;
  $author_id = (int) get_post_field('post_author', $product_id);
  $is_admin_role = $user->roles[0] === 'administrator';

  if ($user_id === 0) {
    return rest_ensure_response(
      new WP_Error('error', 'Unauthorized: You must send the JWT token.', ['status' => 401]),
    );
  }

  // Check if the user is the author of the product or an admin or if the product doesn't exist
  if ($user_id !== $author_id && !$is_admin_role && !!$author_id) {
    return rest_ensure_response(
      new WP_Error('error', 'Forbidden: You must be the author of the product to delete it.', ['status' => 403]),
    );
  }

  $name = sanitize_text_field($request['name']);
  $description = sanitize_text_field($request['description']);
  $price = sanitize_text_field($request['price']);
  $sold = sanitize_text_field($request['sold'])?: 'false';
  $new_imgs_id = sanitize_text_field($request['new_imgs_id'])?: null;
  $post_user_id = $user->user_login;


  if (!$name || !$description || !$price) {
    return rest_ensure_response(
      new WP_Error('error', 'Conflict: `name`, `description` and `price` is required.', ['status' => 409]),
    );
  }

  $response = [
    'ID' => $product_id,
    'post_author' => $author_id,
    'post_title' => $name,
    'post_status' => 'publish',
    'post_type' => 'product',
    'meta_input' => [
      'name' => $name,
      'description' => $description,
      'price' => $price,
      'post_user_id' => $post_user_id,
      'sold' => $sold,
    ],
  ];

  wp_update_post($response);

  $imgs = get_attached_media('image', $product_id);

  $old_imgs_id_array = [];

  if ($imgs) {
    foreach ($imgs as $key => $value) {
      array_push($old_imgs_id_array, $value->ID);
    }
  }

  $new_imgs_id_array = explode(",", $new_imgs_id);

  $imgs_id_array_diff = array_diff($old_imgs_id_array, $new_imgs_id_array);

  foreach ($imgs_id_array_diff as $key => $value) {
    wp_delete_attachment($value, true);
  }

  $files = $request->get_file_params();
  $file_id = $new_imgs_id_array;

  if ($files) {
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    foreach ($files as $file => $array) {
      array_push($file_id, (string) media_handle_upload($file, $product_id));
    }
  }

  $response['file_id'] = $file_id;

  return rest_ensure_response($response);
}

function register_api_post_product()
{
  register_rest_route('api/v1', 'products', [
    'methods' => WP_REST_Server::CREATABLE,
    'callback' => 'api_post_product',
  ]);
}

function register_api_update_product()
{
  register_rest_route('api/v1', 'products/(?P<slug>[-\w]+)', [
    'methods' => WP_REST_Server::CREATABLE,
    'callback' => 'api_update_product',
  ]);
}

add_action('rest_api_init', 'register_api_post_product');
add_action('rest_api_init', 'register_api_update_product');
