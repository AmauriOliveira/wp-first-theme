<?php

function api_post_product($request) {
  $user = wp_get_current_user();
  $user_id = $user->ID;

  if ($user_id > 0) {

    $name = sanitize_text_field($request['name']);
    $description = sanitize_text_field($request['description']);
    $price = sanitize_text_field($request['price']);
    $post_user_id = $user->user_login;

    if ($name && $description && $price) {

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
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        foreach ($files as $file => $array) {
          media_handle_upload($file, $product_id);
        }
      }

    } else {
      $response = new WP_Error('error', 'Conflict: `name`, `description` and `price` is required.', ['status' => 409]);
    }

  } else {
    $response = new WP_Error('error', 'Unauthorized: You must send the JWT token.', ['status' => 401]);
  }

  return rest_ensure_response($response);
}

function register_api_post_product()
{
  register_rest_route('api/v1', 'products', [
    'methods' => WP_REST_Server::CREATABLE,
    'callback' => 'api_post_product',
  ]);
}

add_action('rest_api_init', 'register_api_post_product');
