<?php
use OneAuth\Auth\Client;
class Oneauth_Connect_Controller extends OneAuth\Auth\Controller {

	private $user_data;
	private $client;
	private $pass;


	/**
	 * Registration Page
	 */
	public function action_register() {
		$result = $this->get_client_info();
		if($result !== TRUE) {
			// if the client info could not be retrieved, return the redirect
			return $result;
		}

		// Not registered, do so
		$user					= new User;
		$user->username 		= $this->user_data['info']['nickname'];
		$user->password 		= Hash::make($this->pass);
		$user->{'oauth-token'} 	= $this->client->access_token;
		$user->{'oauth-secret'}	= $this->client->secret;

		$user->save();
		Event::fire('oneauth.sync', array($user->id));
		return OneAuth\Auth\Core::redirect('registered')->with('flash_notice', "You've been properly registered."); // redirect to /home
	}

	/**
	 * Login Page
	 */
	public function action_login() {
		$result = $this->get_client_info();
		if($result !== TRUE) {
			// if the client info could not be retrieved, return the redirect
			return $result;
		}

		$login = array(
				'username' => $this->user_data['info']['nickname'],
				'password' => $this->pass
		);

		$result = Auth::attempt($login);
		if ($result) {
			// get logged user id.
			$user_id = Auth::user()->id;

			// Synced it with oneauth, this will create a relationship between
			// `oneauth_clients` table with `users` table.
			Event::fire('oneauth.sync', array($user_id));
			return OneAuth\Auth\Core::redirect('logged_in'); // redirect to /home
		}
	}

	protected function action_logout() {
		// Log out
		Auth::logout();
		return Redirect::to('/')->with('flash_notice', "You've been logged out!");
	}


	/**
	 * View proper error message when authentication failed or cancelled by user
	 *
	 * @param   String      $provider       Provider name, e.g: twitter, facebook, google …
	 * @param   String      $e              Error Message
	 */
	protected function action_error($provider = null, $e = '') {
		return View::make('error.auth', compact('provider', 'e'));
	}

	private function get_client_info() {
		$this->user_data = Session::get('oneauth');
		if(empty($this->user_data)) {
			return Redirect::to('/')->with('flash_notice', "Could not logged you in.");
		}
		$this->client = Client::where('provider', '=', $this->user_data['provider'])
						->where('uid', '=', $this->user_data['info']['uid'])
						->first();
		if(empty($this->client)) {
			return Redirect::to('/')->with('flash_notice', "Could not logged you in.");
		}
		$this->pass = md5($this->client->access_token . $this->client->secret . Config::get('application::key'));
		return TRUE;

	}

}
