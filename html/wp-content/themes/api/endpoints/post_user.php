<?php

function api_post_user($request) {

  $first_name = sanitize_text_field($request['firstName']);
  $last_name = sanitize_text_field($request['lastName']);
  $email = sanitize_email($request['email']);
  $password = $request['password'];
  $street = sanitize_text_field($request['street']);
  $zipCode = sanitize_text_field($request['zipCode']);
  $number = sanitize_text_field($request['number']);
  $complement = sanitize_text_field($request['complement']);
  $neighborhood = sanitize_text_field($request['neighborhood']);
  $city = sanitize_text_field($request['city']);
  $uf = sanitize_text_field($request['uf']);

  $user_exists = username_exists($email);
  $email_exists = email_exists($email);

  if (!$user_exists && !$email_exists && $email && $password) {
    $user_id = wp_create_user($email, $password, $email);

    $response = [
      'ID' => $user_id,
      'display_name' => $first_name . ' ' . $last_name,
      'first_name' => $first_name,
      'last_name'=> $last_name,
      'role' => 'subscriber',
      'nickname'=> $first_name,
      'description' => 'football player', /* TODO: Add description */
    ];

    wp_update_user($response);

    update_user_meta( $user_id, 'street', $street );
    update_user_meta( $user_id, 'zipCode', $zipCode );
    update_user_meta( $user_id, 'number', $number );
    update_user_meta( $user_id, 'complement', $complement );
    update_user_meta( $user_id, 'neighborhood', $neighborhood );
    update_user_meta( $user_id, 'city', $city );
    update_user_meta( $user_id, 'uf', $uf );
  } else {
    $response = new WP_Error('error', 'User already exists', ['status' => 409]);
  }

  return rest_ensure_response($response);
}

function register_api_post_user() {
  register_rest_route('api/v1', 'users', [
    'methods' => WP_REST_Server::CREATABLE,
    'callback' => 'api_post_user',
  ]);
}

add_action('rest_api_init', 'register_api_post_user');