<?php

class Ajax_Controller extends Base_Controller {
	public $restful = true;

	public function __construct() {
		$this->filter('before', 'api');
	}

	public function get_index()
	{
		$twitter_id = Input::get('id');
		if(empty($twitter_id)) {
			$response = array('code' => 404, 'message' => 'You must specify a valid ID.');
			return Response::json($response);
		}

		$db_object = Timeline::find($twitter_id);
		if(empty($db_object)) {
			$response = array('code' => 404, 'message' => 'The user ' . $twitter_id . 'could not be found.');
			return Response::json($response);
		}

		$response = $this->build_timeline_response($db_object->timeline);
		if(empty($response)) {
			$response = array('code' => 500, 'message' => 'Server Error. Corrupt timeline?');
			///TODO: Log this response
			return Response::json($response);
		}
		$response['last_updated'] = $db_object->updated_at;
		return Response::json($response);
	}

	/**
	 * Build the array response from a given compressed blob.
	 *
	 * @param String $blob Compressed blob
	 *
	 * @return return
	 *
	 * @author Julio Foulquié <julio@tnwlabs.com>
	 */
	private function build_timeline_response($blob) {
		/* Used fields:
		 * 		$tweet['created_at'],
		 * 		$tweet['text']
		 * 		$tweet['retweet_count'],
		 * 		$tweet['favorite_count'],
		 * 		$tweet['in_reply_to_status_id'],
		 * 		$tweet['source']
		*/
		if(empty($blob)) {
			return FALSE;
		}

		$response = array('code' => 200, 'data' => array());
		$timeline = Timeline::decode_blob($blob);
		if(empty($timeline)) {
			return FALSE;
		}
		foreach($timeline AS $pos => $tweet) {
			if(!isset($tweet['created_at'], $tweet['text'])) {
				continue;
			}
			$tweet = $this->replace_urls($tweet);
			$response['data'][$pos]['created_at']		= $tweet['created_at'];
			$response['data'][$pos]['text']				= $tweet['text'];
			$response['data'][$pos]['retweet_count']	= $tweet['retweet_count'];
			$response['data'][$pos]['favorite_count']	= $tweet['favorite_count'];
			$response['data'][$pos]['source']			= $tweet['source'];
			$response['data'][$pos]['reply']			= empty($tweet['in_reply_to_status_id']) ? FALSE : TRUE;
		}
		return $response;
	}

	private function replace_urls($tweet_entity) {
		if(!isset($tweet_entity['entities']['urls']) OR count($tweet_entity['entities']['urls']) < 1 ) {
			return $tweet_entity;
		}
		foreach($tweet_entity['entities']['urls'] AS $url) {
			$tco_url = $url['url'];
			$expanded_url = $url['expanded_url'];
			$display_url = $url['display_url'];
			// Build the html link with the display_url and the proper href
			$html_link = "<a href=\"$expanded_url\" target=\"_blank\">$display_url</a>";
			// Replace the t.co url with the new html text
			$tweet_entity['text'] = str_replace($tco_url, $html_link, $tweet_entity['text']);
		}
		return $tweet_entity;
	}

}
