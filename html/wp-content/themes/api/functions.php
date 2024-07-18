<?php

$templete_dir = get_template_directory();

require_once($templete_dir.'/custom-post-type/product.php');
require_once($templete_dir.'/custom-post-type/transaction.php');

require_once($templete_dir.'/endpoints/users/get_user.php');
require_once($templete_dir.'/endpoints/users/post_user.php');
require_once($templete_dir.'/endpoints/users/put_user.php');

require_once($templete_dir.'/endpoints/products/get_product.php');
require_once($templete_dir.'/endpoints/products/post_product.php');

require_once($templete_dir.'/endpoints/products/delete_product.php');

require_once($templete_dir.'/endpoints/transaction/get_transaction.php');
require_once($templete_dir.'/endpoints/transaction/post_transaction.php');

function get_product_id_by_slug($slug) {
  $query =new WP_Query([
    'name' => $slug,
    'post_type' => 'product',
    'posts_per_page' => 1,
    'fields' => 'ids',
  ]);

  return $query->get_posts()[0];
}

function expire_token() {
  return time() + 60 * 60 * 3;
}

function customize_screen_lost_password() {
  ?>
    <style type="text/css">
      #login h1 a {
        background-image: none;
      }

      #backtoblog {
        display: none;
      }
    </style>
  <?php
}

add_action('jwt_auth_expire', 'expire_token');
add_action('login_enqueue_scripts', 'customize_screen_lost_password');