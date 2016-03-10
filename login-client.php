<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/New_York');
require_once __DIR__ . '/vendor/autoload.php';
include 'common.php';

function extractInfo($credentials, $options) {
	try {
		$fb = new Facebook\Facebook($credentials);
		$pageId = $options['i'];
		$data = array();
		$postCount = 0;
		if (isset($options['r'])) {
			unlink($pageId);
		}
		if (file_exists($pageId)) {
			$data = json_decode(file_get_contents($pageId), true);
		} else {
			$data["pages"] = array();
			$data["pages"][$pageId] = array();
			$data["pages"][$pageId]["posts"] = array();
			$data["pages"][$pageId]["likes"] = array();
		}
?>
<script>
	function pageScroll() {
	    window.scrollBy(0,100);
	    scrolldelay = setTimeout(pageScroll,10);
	}
	pageScroll();
</script>
<?php
		try {
			$responsePost = $fb->get('/' . $pageId . '/feed');
			$postEdge = $responsePost->getGraphEdge();
			do {
				foreach ($postEdge as $post) {
					$postCount++;
					output('N: ' . $postCount . ' P: ' . $post['id']);
					if ( (isset($options['n']) && $postCount <= $options['n']) ||  !isset($options['n']) ) {
						if (!isset($data["pages"][$pageId]["posts"][$post['id']])) {
							$data["pages"][$pageId]["posts"][$post['id']] = array();
							$data["pages"][$pageId]["posts"][$post['id']]["comments"] = array();
							$data["pages"][$pageId]["posts"][$post['id']]["likes"] = array();
							$data["pages"][$pageId]["posts"][$post['id']]["share"] = array();
							$data["pages"][$pageId]["posts"][$post['id']]["data"] = $post->asArray();
							try {
								$responseComment = $fb->get('/' . $post['id'] . '/comments');
								$commentEdge = $responseComment->getGraphEdge();
								do {
									foreach ($commentEdge as $comment) {
										output('N: ' . $postCount . ' P: ' . $post['id'] . ' C: ' . $comment['id']);
										$data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']] = array();
										$data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']]["replies"] = array();
										$data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']]["likes"] = array();
										$data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']]["data"] = $comment->asArray();
										try {
											$responseReplies = $fb->get('/' . $comment['id'] . '/comments');
											$replyEdge = $responseReplies->getGraphEdge();
											do {
												foreach ($replyEdge as $reply) {
													output('N: ' . $postCount . ' P: ' . $post['id'] . ' C: ' . $comment['id'] . ' R: ' . $reply['id']);
													$data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']]["replies"][$reply['id']] = array();
													$data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']]["replies"][$reply['id']]["likes"] = array();
													$data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']]["replies"][$reply['id']]["data"] = $reply->asArray();
													$responseReplyLikes = $fb->get('/' . $reply['id'] . '/likes');
													$replyLikeEdge = $responseReplyLikes->getGraphEdge();
													do {
														foreach ($replyLikeEdge as $replyLike) {
															output('N: ' . $postCount . ' P: ' . $post['id'] . ' C: ' . $comment['id'] . ' R: ' . $reply['id'] . ' L: ' . $replyLike['id']);
															array_push($data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']]["replies"][$reply['id']]["likes"], $replyLike->asArray());
														}
													} while ($replyLikeEdge = $fb->next($replyLikeEdge));
												}
											} while ($replyEdge = $fb->next($replyEdge));
										} catch(Exception $e) {
											output('E: ' . '/' . $comment['id'] . '/comments');
										}
										try {
											$responseCommentLikes = $fb->get('/' . $comment['id'] . '/likes');
											$commentLikeEdge = $responseCommentLikes->getGraphEdge();
											do {
												foreach ($commentLikeEdge as $commentLike) {
													output('N: ' . $postCount . ' P: ' . $post['id'] . ' C: ' . $comment['id'] . ' L: ' . $commentLike['id']);
													array_push($data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']]["likes"], $commentLike->asArray());
												}
											} while ($commentLikeEdge = $fb->next($commentLikeEdge));
										} catch(Exception $e) {
											output('E: ' . '/' . $comment['id'] . '/likes');
										}
								  	}
								} while ($commentEdge = $fb->next($commentEdge));
							} catch(Exception $e) {
								output('E: ' . '/' . $post['id'] . '/comments');
							}
							try {
								$responsePostLikes = $fb->get('/' . $post['id'] . '/likes');
								$postLikeEdge = $responsePostLikes->getGraphEdge();
								do {
									foreach ($postLikeEdge as $postLike) {
										output('N: ' . $postCount . ' P: ' . $post['id'] . ' L: ' . $postLike['id']);
										array_push($data["pages"][$pageId]["posts"][$post['id']]["likes"], $postLike->asArray());
									}
								} while ($postLikeEdge = $fb->next($postLikeEdge));
							} catch(Exception $e) {
								output('E: ' . '/' . $post['id'] . '/likes');
							}
							try {
								$responsePostShares = $fb->get('/' . $post['id'] . '/sharedposts');
								$postShareEdge = $responsePostShares->getGraphEdge();
								do {
									foreach ($postShareEdge as $postShare) {
										output('N: ' . $postCount . ' P: ' . $post['id'] . ' S: ' . $postShare['id']);
										array_push($data["pages"][$pageId]["posts"][$post['id']]["share"], $postShare->asArray());
									}
								} while ($postShareEdge = $fb->next($postShareEdge));
							} catch(Exception $e) {
								output('E: ' . '/' . $post['id'] . '/sharedposts');
							}
							file_put_contents($pageId, json_encode($data,TRUE));
						}
				 	} else {
				 		file_put_contents($pageId, json_encode($data,TRUE));
						return '{"status":"OK", "message":"<a href=\'' . $pageId . '\'>Parial file</a>", "data":"' . json_encode($data,TRUE) . '"}';
				 	}
				}
			} while ($postEdge = $fb->next($postEdge));
		} catch(Exception $e) {
			output('E: ' . '/' . $pageId . '/feed');
		}
		echo json_encode($data);
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
	  // When Graph returns an error
	  output('{"status":"error", "message":"Graph returned an error: ' . $e->getMessage() . '"}');
	  exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
	  // When validation fails or other local issues
	  output('{"status":"error", "message":"Facebook SDK returned an error: ' . $e->getMessage() . '"}');
	  exit;
	}
	file_put_contents($pageId, json_encode($data,TRUE));
	return '{"status":"OK", "message":"<a href=\'' . $pageId . '\'>Full file</a>", "data":"' . json_encode($data,TRUE) . '"}';
}

$options = array();
if (isset($argv)) {
	$options = getopt("i:c:n:r:");
} else {
	$options = $_REQUEST;
}
if (isset($options['i'])) {
	if (isset($options['c'])) {
		$credentials = parse_ini_file($options['c']);
	}
	echo extractInfo($credentials, $options);
} else {
	echo '{"status":"error", "message":"Please provide the fbid parameter."}';
}