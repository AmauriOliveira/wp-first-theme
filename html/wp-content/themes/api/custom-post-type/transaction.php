<?php

function register_transaction_custom_post_type() {
  register_post_type('transacao', array(
    'label' => 'Transacao',
    'description'=> 'Transacao',
    'public'=> true,
    'show_ui'=> true,
    'capability_type' => 'post',
    'rewrite' => array('slug' => 'transacao', 'with_front' => true),
    'query_var' => true,
    'supports' => array('title', 'custom-fields', 'author'),
    'publicly_queryable' => true,
  ));
}

add_action('init', 'register_transaction_custom_post_type');