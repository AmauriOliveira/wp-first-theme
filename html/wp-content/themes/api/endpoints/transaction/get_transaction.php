<?php
function api_get_transactions($request) {
  $type = sanitize_text_field($request['type']) ?: 'buyer_id';
  $page = sanitize_text_field($request['page']) ?: 0;
  $per_page = sanitize_text_field($request['per_page']) ?: 10;
  $user = wp_get_current_user();
  $user_id = $user->ID;

  if ($user_id === 0) {
    return rest_ensure_response(
      new WP_Error('error', 'Unauthorized: You must send the JWT token.', ['status' => 401]),
    );
  }

  $type_meta_query = null;
  if ($type) {
    $type_meta_query = [
      'key' => $type,
      // user_login has been used because the user's email can change but user_Login does not.
      'value' => $user->user_login,
      'compare' => '='
    ];
  }

  $transactions = new WP_Query([
    'post_status' => 'publish',
    'post_type' => 'transaction',
    'orderby' => 'date',
    'posts_per_page' => $per_page,
    'paged' => $page,

    'meta_query' => [$type_meta_query],
  ]);

  $transactions_array = [];
  foreach ($transactions->posts as $key => $value) {
    $transaction_id = $value->ID;

    $transaction_meta = get_post_meta($transaction_id);

    $transactions_array[$key] = [
      'buyer_id' =>  $transaction_meta['buyer_id'][0],
      'seller_id' => $transaction_meta['seller_id'][0],
      'address' => json_decode($transaction_meta['address'][0]),
      'product' => json_decode($transaction_meta['product'][0]),
      'created_at' => $value->post_date,
    ];
  }

  $transactions_array["current_page"] = (int)$page;
  $transactions_array["found_posts"] = $transactions->found_posts;
	$transactions_array["max_num_pages"] = $transactions->max_num_pages;

  $response = rest_ensure_response($transactions_array);

  $response->header("X-Page", $page);
  $response->header("X-Per-Page", $per_page);
  $response->header("X-Total-Count", $transactions->found_posts);

  return $response;
}

function register_api_get_transactions() {
  register_rest_route('api/v1', 'transactions', [
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'api_get_transactions',
  ]);
}

add_action('rest_api_init', 'register_api_get_transactions');
