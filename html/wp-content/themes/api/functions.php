<?php
$templete_dir = get_template_directory();

require_once($templete_dir.'/custom-post-type/product.php');
require_once($templete_dir.'/custom-post-type/transaction.php');

require_once($templete_dir.'/endpoints/users/get_user.php');
require_once($templete_dir.'/endpoints/users/post_user.php');
require_once($templete_dir.'/endpoints/users/put_user.php');

//require_once($templete_dir.'/endpoints/products/get_products.php');
require_once($templete_dir.'/endpoints/products/post_product.php');
//require_once($templete_dir.'/endpoints/products/put_product.php');
//require_once($templete_dir.'/endpoints/products/delete_product.php');

function expire_token() {
  return time() + (60 * 60 * 3);
}

add_action('jwt_auth_expire', 'expire_token');