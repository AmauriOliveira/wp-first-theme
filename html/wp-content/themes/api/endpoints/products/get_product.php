<?php

function api_get_product($request) {
  $slug = $request['slug'];
  $post_id = get_product_by_slug($slug);

  if ($post_id) {
    $post_meta = get_post_meta($post_id);

    $imgs = get_attached_media('image', $post_id);
    $imgs_arr = [];

    if ($imgs) {
      foreach ($imgs as $key => $value) {
        $imgs_arr[$key] = [
          'title' => $value->post_name,
          'urls' => $value->guid,
        ];
      }

    }

    $response = [
      'id' => $slug,
      'photo' => $imgs_arr,
      'name' => $post_meta['name'][0],
      'description' => $post_meta['description'][0],
      'price' => $post_meta['price'][0],
      'post_user_id' => $post_meta['post_user_id'][0],
      'sold' => $post_meta['sold'][0],

    ];

  }else {
    $response = new WP_Error('error', 'Not found: Product with slug ' . $slug . ' not found', ['status' => 404]);
  }

  return rest_ensure_response($response);
}

function register_api_get_product()
{
  register_rest_route('api/v1', 'products/(?P<slug>[-\w]+)', [
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'api_get_product',
  ]);
}

add_action('rest_api_init', 'register_api_get_product');
