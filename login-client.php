<?php
session_start();
date_default_timezone_set('America/New_York');
require_once __DIR__ . '/vendor/autoload.php';
include 'common.php';
$fb = new Facebook\Facebook($credentials);

// Sets the default fallback access token so we don't have to pass it to each request
$fb->setDefaultAccessToken('CAASlyODtSVsBAMtOFpREc0fU8wDZBGggL1Fn1cZB8eSzZCJFwHqOxf9LvHput0AX5UfKlje6UtykojvKnmVdmhFPit627SbUpMwUdg6zxDlDmGEaw6yuWiZCZATFlAbQi2rDJokTxugZBUz9ENdZAhEB8sIJ32GbqJny8Dfc0OVcd9yNgPA8q5EZACMMqQ0IbZC0ZD');

function extractInfo($pageId) {
	global $fb;
	$data = array();
	try {
		$data["pages"] = array();
		$data["pages"][$pageId] = array();
		$data["pages"][$pageId]["posts"] = array();
		$data["pages"][$pageId]["likes"] = array();
		$responsePost = $fb->get('/' . $pageId . '/feed');
		$postEdge = $responsePost->getGraphEdge();
		do {
			foreach ($postEdge as $post) {
			    $data["pages"][$pageId]["posts"][$post['id']] = array();
				$data["pages"][$pageId]["posts"][$post['id']]["comments"] = array();
				$data["pages"][$pageId]["posts"][$post['id']]["likes"] = array();
				$data["pages"][$pageId]["posts"][$post['id']]["data"] = $post->asArray();
				$responseComment = $fb->get('/' . $post['id'] . '/comments');
				$commentEdge = $responseComment->getGraphEdge();
				do {
					foreach ($commentEdge as $comment) {
						$data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']] = array();
						$data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']]["replies"] = array();
						$data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']]["likes"] = array();
						$data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']]["data"] = $comment->asArray();
						$responseReplies = $fb->get('/' . $comment['id'] . '/comments');
						$replyEdge = $responseReplies->getGraphEdge();
						do {
							foreach ($replyEdge as $reply) {
								$data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']]["replies"][$reply['id']] = array();
								$data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']]["replies"][$reply['id']]["likes"] = array();
								$data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']]["replies"][$reply['id']]["data"] = $reply->asArray();
								$responseReplyLikes = $fb->get('/' . $reply['id'] . '/likes');
								$replyLikeEdge = $responseReplyLikes->getGraphEdge();
								do {
									foreach ($replyLikeEdge as $replyLike) {
										array_push($data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']]["replies"][$reply['id']]["likes"], $replyLike->asArray());
									}
								} while ($replyLikeEdge = $fb->next($replyLikeEdge));
							}
						} while ($replyEdge = $fb->next($replyEdge));
						$responseCommentLikes = $fb->get('/' . $comment['id'] . '/likes');
						$commentLikeEdge = $responseCommentLikes->getGraphEdge();
						do {
							foreach ($commentLikeEdge as $commentLike) {
								array_push($data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']]["likes"], $commentLike->asArray());
							}
						} while ($commentLikeEdge = $fb->next($commentLikeEdge));
				  	}
				} while ($commentEdge = $fb->next($commentEdge));
				$responsePostLikes = $fb->get('/' . $post['id'] . '/likes');
				$postLikeEdge = $responsePostLikes->getGraphEdge();
				do {
					foreach ($postLikeEdge as $postLike) {
						array_push($data["pages"][$pageId]["posts"][$post['id']]["likes"], $postLike->asArray());
					}
				} while ($postLikeEdge = $fb->next($postLikeEdge));
		 	}
		} while ($postEdge = $fb->next($postEdge));
		echo json_encode($data);
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
	  // When Graph returns an error
	  output('Graph returned an error: ' . $e->getMessage());
	  exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
	  // When validation fails or other local issues
	  output('Facebook SDK returned an error: ' . $e->getMessage());
	  exit;
	}
}

function extractAsyncInfo($pageId) {
	global $fb;
	$data = array();
	try {
		$data["pages"] = array();
		$data["pages"][$pageId] = array();
		$data["pages"][$pageId]["posts"] = array();
		$data["pages"][$pageId]["likes"] = array();
		$responsePost = $fb->get('/' . $pageId . '/feed');
		// $userNode = $response->getGraphUser();
		// $graphObject = $response->getGraphObject();
		eachEdge($responsePost, function($post){
			global $fb;
			$data["pages"][$pageId]["posts"][$post['id']] = array();
			$data["pages"][$pageId]["posts"][$post['id']]["comments"] = array();
			$data["pages"][$pageId]["posts"][$post['id']]["likes"] = array();
			$data["pages"][$pageId]["posts"][$post['id']]["data"] = $post->asArray();
			$responseComment = $fb->get('/' . $post['id'] . '/comments');
			eachEdge($responseComment, function($comment){
				global $fb;
				$data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']] = array();
				$data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']]["replies"] = array();
				$data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']]["likes"] = array();
				$data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']]["data"] = $comment->asArray();
				$responseReplies = $fb->get('/' . $comment['id'] . '/comments');
				eachEdge($responseReplies, function($reply){
					global $fb;
					$data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']]["replies"][$reply['id']] = array();
					$data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']]["replies"][$reply['id']]["likes"] = array();
					$data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']]["replies"][$reply['id']]["data"] = $reply->asArray();
					$responseReplyLikes = $fb->get('/' . $reply['id'] . '/likes');
				  	eachEdge($responseReplyLikes, function($replyLike){
				  		array_push($data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']]["replies"][$reply['id']]["likes"], $replyLike->asArray());
				  	});
				});
				$responseCommentLikes = $fb->get('/' . $comment['id'] . '/likes');
			  	eachEdge($responseCommentLikes, function($commentLike){
			  		array_push($data["pages"][$pageId]["posts"][$post['id']]["comments"][$comment['id']]["likes"], $commentLike->asArray());
			  	});
			});
			$responsePostLikes = $fb->get('/' . $post['id'] . '/likes');
			eachEdge($responsePostLikes, function($postLike){
				array_push($data["pages"][$pageId]["posts"][$post['id']]["likes"], $postLike->asArray());
			});
		});
		echo json_encode($data);
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
	  // When Graph returns an error
	  output('Graph returned an error: ' . $e->getMessage());
	  exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
	  // When validation fails or other local issues
	  output('Facebook SDK returned an error: ' . $e->getMessage());
	  exit;
	}
}

if (isset($_REQUEST['fbid'])) {
	extractInfo($_REQUEST['fbid']);
} else {
	echo 'Please provide the fbid parameter.'
}