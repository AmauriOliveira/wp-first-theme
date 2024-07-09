<?php

function api_put_user($request) {
  $user = wp_get_current_user();
  $user_id = $user->ID;

  if ($user_id > 0) {

    $first_name = sanitize_text_field($request['first_name']);
    $last_name = sanitize_text_field($request['last_name']);
    $user_email = sanitize_email($request['user_email']);
    $user_pass = $request['user_pass'];
    $description = sanitize_text_field($request['description']);
    $street = sanitize_text_field($request['street']);
    $zipCode = sanitize_text_field($request['zipCode']);
    $number = sanitize_text_field($request['number']);
    $complement = sanitize_text_field($request['complement']);
    $neighborhood = sanitize_text_field($request['neighborhood']);
    $city = sanitize_text_field($request['city']);
    $uf = sanitize_text_field($request['uf']);

    $email_exists = email_exists($user_email);

    if (!$email_exists || $email_exists === $user_id) {

      $response = [
        'ID' => $user_id,
        'display_name' => $first_name . ' ' . $last_name,
        'user_email' => $user_email,
        'user_pass' => $user_pass,
        'first_name' => $first_name,
        'last_name'=> $last_name,
        'nick_name'=> $first_name,
        'description' => $description,
      ];

      wp_update_user($response);

      update_user_meta( $user_id, 'street', $street );
      update_user_meta( $user_id, 'zipCode', $zipCode );
      update_user_meta( $user_id, 'number', $number );
      update_user_meta( $user_id, 'complement', $complement );
      update_user_meta( $user_id, 'neighborhood', $neighborhood );
      update_user_meta( $user_id, 'city', $city );
      update_user_meta( $user_id, 'uf', $uf );
      // remove a senha do usuário do response
      unset($response['user_pass']);
    } else {
      $response = new WP_Error('error', 'Conflict: User already exists', ['status' => 409]);
    }
  } else {
    $response = new WP_Error('error', 'Unauthorized: You must send the JWT token.', ['status' => 401]);
  }

  return rest_ensure_response($response);
}

function register_api_put_user() {
  register_rest_route('api/v1', 'users', [
    'methods' => WP_REST_Server::EDITABLE,
    'callback' => 'api_put_user',
  ]);
}

add_action('rest_api_init', 'register_api_put_user');
