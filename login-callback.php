<?php
session_start();
require_once __DIR__ . '/vendor/autoload.php';
include 'common.php';
$fb = new Facebook\Facebook($credentials);

$helper = $fb->getRedirectLoginHelper();
try {
  $accessToken = $helper->getAccessToken();
  if (!isset($_SESSION['facebook_access_token'])) {
    $oAuth2Client = $fb->getOAuth2Client();
    // Exchanges a short-lived access token for a long-lived one
    $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
  }
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  output('Graph returned an error: ' . $e->getMessage());
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  output('Facebook SDK returned an error: ' . $e->getMessage());
  exit;
}

if (isset($accessToken)) {
  // Logged in!
  $_SESSION['facebook_access_token'] = (string) $accessToken;

  // Now you can redirect to another page and use the
  // access token from $_SESSION['facebook_access_token']
}
output($accessToken);