<?php

/**
 * Retrieves the product scheme for a given slug.
 *
 * @param string $slug The slug of the product.
 * @return array|WP_Error The product scheme, or a WP_Error object if the product is not found.
 */
function product_scheme($slug) {
  $product_id = get_product_id_by_slug($slug);

  if (!$product_id) {
    return rest_ensure_response(
      new WP_Error('error', "Not found: Product with slug $slug not found", ['status' => 404]),
    );
  }

  $post_meta = get_post_meta($product_id);

  $imgs = get_attached_media('image', $product_id);
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

  return $response;
}

/**
 * Retrieves a single product based on the provided slug.
 *
 * @param array $request An associative array containing the slug of the product.
 * @return WP_REST_Response The response containing the product details.
 */
function api_get_product($request) {

  $response = product_scheme($request['slug']);

  return rest_ensure_response($response);
}

/**
 * Retrieves a paginated list of published products based on search term and pagination parameters.
 *
 * @param array $request An associative array containing the following keys:
 *                       - 'q' (string): The search term to filter products by. Defaults to an empty string.
 *                       - 'page' (int): The page number of the results. Defaults to 0.
 *                       - 'per_page' (int): The number of products per page. Defaults to 10.
 *                       - 'user_id' (int): The ID of the user making the request. Defaults to an empty string.
 * @return WP_REST_Response|WP_Error The response containing the paginated list of products and metadata, or a WP_Error object if an error occurs.
 */
function api_get_products($request) {

  $search_term = sanitize_text_field($request['q']) ?: '';
  $page = sanitize_text_field($request['page']) ?: 0;
  $per_page = sanitize_text_field($request['per_page']) ?: 10;
  $user_id = sanitize_text_field($request['user_id']);

  $user_id_meta_query = null;

  if ($user_id) {
    $user_id_meta_query = [
      'key' => 'post_user_id',
      'value' => $user_id,
      'compare' => '='
    ];
  }

  // Exclude sold products
  $not_sold_meta_query = [
    'key' => 'sold',
    'value' => 'false',
    'compare' => '='
  ];

  $products = new WP_Query([
    'post_type' => 'product',
    'post_status' => 'publish',
    'posts_per_page' => $per_page,
    'paged' => $page,
    's' => $search_term,
    'meta_query' => [
      $user_id_meta_query,
      $not_sold_meta_query,
    ],
  ]);

  $products_array = [];
  foreach ($products->posts as $key => $value) {
    $products_array[$key] = product_scheme($value->post_name);
  }

  $products_array["current_page"] = (int)$page;
  $products_array["found_posts"] = $products->found_posts;
	$products_array["max_num_pages"] = $products->max_num_pages;

  $response = rest_ensure_response($products_array);

  $response->header("X-Page", $page);
  $response->header("X-Per-Page", $per_page);
  $response->header("X-Total-Count", $products->found_posts);

  return $response;
}

function register_api_get_product() {
  register_rest_route('api/v1', 'products/(?P<slug>[-\w]+)', [
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'api_get_product',
  ]);
}

function register_api_get_products() {
  register_rest_route('api/v1', 'products', [
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'api_get_products',
  ]);
}

add_action('rest_api_init', 'register_api_get_product');
add_action('rest_api_init', 'register_api_get_products');
