<?php
$templete_dir = get_template_directory();

require_once($templete_dir.'/custom-post-type/product.php');
require_once($templete_dir.'/custom-post-type/transaction.php');
require_once($templete_dir.'/endpoints/get_user.php');
require_once($templete_dir.'/endpoints/post_user.php');
require_once($templete_dir.'/endpoints/put_user.php');

function expire_token() {
  return time() + (60 * 60 * 3);
}

add_action('jwt_auth_expire', 'expire_token');