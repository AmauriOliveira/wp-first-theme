<?php

function api_get_user() {

  $user = wp_get_current_user();
  $user_id = $user->ID;

  if ($user_id === 0) {
    return rest_ensure_response(
      new WP_Error('error', 'Unauthorized: You must send the JWT token.', ['status' => 401]),
    );
  }

  $meta = get_user_meta($user_id);

  $response = [
    'id' => $user->user_login,
    'display_name' => $user->display_name,
    'first_name' => $user->first_name,
    'last_name' => $user->last_name,
    'nick_name'=> $user->first_name,
    'email' => $user->user_email,
    'avatar' => get_avatar_url($user_id),
    'role' => $user->roles[0],
    'description' => $user->description,
    'zipCode' => $meta['zipCode'][0],
    'street' => $meta['street'][0],
    'number' => $meta['number'][0],
    'complement' => $meta['complement'][0],
    'neighborhood' => $meta['neighborhood'][0],
    'city' => $meta['city'][0],
    'uf' => $meta['uf'][0],
  ];

  return rest_ensure_response($response);
}

function register_api_get_user() {
  register_rest_route('api/v1', 'users', [
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'api_get_user',
  ]);
}

add_action('rest_api_init', 'register_api_get_user');
