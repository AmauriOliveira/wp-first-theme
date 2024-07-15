<?php

function api_delete_product($request) {
  $slug = $request['slug'];
  $product_id = get_product_by_slug($slug);
  $user = wp_get_current_user();
  $user_id = (int) $user->ID;
  $author_id = (int) get_post_field('post_author', $product_id);
  $is_admin_role = $user->roles[0] === 'administrator';

  // Check if the user is logged in
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

  // Check if the product exists
  if (!$product_id) {
    return rest_ensure_response(
      new WP_Error('error', 'Product not found.', ['status' => 404]),
    );
  }

  $imgs = get_attached_media('image', $product_id );

  if ($imgs) {
    foreach ($imgs as $key => $value) {
      // delete image, second param is force delete
      wp_delete_attachment($value->ID, true);
    }
  }

  $response = wp_delete_post($product_id, true);

  return rest_ensure_response($response);
}

function register_api_delete_product()
{
  register_rest_route('api/v1', 'products/(?P<slug>[-\w]+)', [
    'methods' => WP_REST_Server::DELETABLE,
    'callback' => 'api_delete_product',
  ]);
}

add_action('rest_api_init', 'register_api_delete_product');
