<?php
session_start();
require_once __DIR__ . '/vendor/autoload.php';
include 'common.php';
$fb = new Facebook\Facebook($credentials);

$helper = $fb->getRedirectLoginHelper();
$permissions = ['email', 'user_likes']; // optional
$loginUrl = $helper->getLoginUrl('http://localhost:8888/gd/web/fb/fb2/login-callback.php', $permissions);

output('<a href="' . $loginUrl . '">Log in with Facebook!</a>');
