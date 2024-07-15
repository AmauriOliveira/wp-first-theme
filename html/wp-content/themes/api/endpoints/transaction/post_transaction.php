<?php
function api_post_transaction($request) {
  $user = wp_get_current_user();
  $user_id = $user->ID;
  $not_sold = $request['product']['not_sold'] === 'false';

  if ($user_id === 0) {
    return rest_ensure_return(
      new WP_Error('error', 'Unauthorized: You must send the JWT token.', ['status' => 401]),
    );
  }

  $product_name = sanitize_text_field($request['product']['name']);
  $product_slug = sanitize_text_field($request['product']['id']);
  $buyer_id = sanitize_text_field($request['buyer_id']);
  $seller_id = sanitize_text_field($request['seller_id']);
  $address = $request['address'] ?: null;
  $product = $request['product'] ?: null;

  $product_id = get_product_by_slug($product_slug);
  update_post_meta($product_id, 'sold','true');

  if (!$buyer_id || !$seller_id || empty($address) || empty($product) || !$product_name || !$product_slug) {
    return rest_ensure_return(
      new WP_Error('error', 'Conflict: buyer_id, seller_id, address, product, product_name, product_slug is required.', ['status' => 409]),
    );
  }

  $address = json_encode($request['address'], JSON_UNESCAPED_UNICODE)?: null;
  $product = json_encode($request['product'], JSON_UNESCAPED_UNICODE)?: null;

  $transaction = [
    'post_author' => $user_id,
    'post_title' => "{$buyer_id} - {$seller_id} - {$product_name}",
    'post_status' => 'publish',
    'post_type' => 'transaction',
    'meta_input' => [
      'buyer_id' => $buyer_id,
      'seller_id' => $seller_id,
      'address' => $address,
      'product' => $product,
    ],
  ];

  return rest_ensure_return(wp_insert_post($transaction));
}

function register_api_post_transaction()
{
  register_rest_route('api/v1', 'transactions', [
    'methods' => WP_REST_Server::CREATABLE,
    'callback' => 'api_post_transaction',
  ]);
}

add_action('rest_api_init', 'register_api_post_transaction');
