<?php
/**
 * Crozdesk strategy for Opauth
 * 
 * More information on Opauth: http://opauth.org
 * 
 * @copyright    Copyright © 2015 Alexandru Stoian (alex@crozdesk.com)
 * @link         https://crozdesk.com
 * @package      Opauth.CrozdeskStrategy
 * @license      MIT License
 */
class CrozdeskStrategy extends OpauthStrategy {
	/**
	 * Compulsory config keys, listed as unassociative arrays
	 */
	public $expects = array('client_id', 'client_secret');
	
	/**
	 * Optional config keys, without predefining any default values.
	 */
	public $optionals = array('redirect_uri', 'scope', 'state', 'access_type', 'approval_prompt');
	
	/**
	 * Optional config keys with respective default values, listed as associative arrays
	 * eg. array('scope' => 'email');
	 */
	public $defaults = array(
		'redirect_uri' => '{complete_url_to_strategy}oauth2callback',
		'scope' => 'public'
	);
	
	/**
	 * Auth request
	 */
	public function request(){
		$url = 'https://crozdesk.com/oauth/authorize';
		$params = array(
			'client_id' => $this->strategy['client_id'],
			'redirect_uri' => $this->strategy['redirect_uri'],
			'response_type' => 'code',
			'scope' => $this->strategy['scope']
		);

		foreach ($this->optionals as $key){
			if (!empty($this->strategy[$key])) $params[$key] = $this->strategy[$key];
		}
		
		$this->clientGet($url, $params);
	}
	
	/**
	 * Internal callback, after OAuth
	 */
	public function oauth2callback(){
		if (array_key_exists('code', $_GET) && !empty($_GET['code'])){
			$code = $_GET['code'];
			$url = 'https://crozdesk.com/oauth/token';
			$params = array(
				'code' => $code,
				'client_id' => $this->strategy['client_id'],
				'client_secret' => $this->strategy['client_secret'],
				'redirect_uri' => $this->strategy['redirect_uri'],
				'grant_type' => 'authorization_code'
			);
			$response = $this->serverPost($url, $params, null, $headers);
			
			$results = json_decode($response);
			
			if (!empty($results) && !empty($results->access_token)){
				$userinfo = $this->userinfo($results->access_token);
				$this->auth = array(
					'provider' => 'Crozdesk',
					'uid' => $userinfo->user->id,
					'info' => array(
						'first_name' => $userinfo->user->first_name,
						'last_name' => $userinfo->user->last_name,
						'name' => $userinfo->user->name,
						'email' => $userinfo->user->email
					),
					'credentials' => array(
						'token' => $results->access_token,
						'expires' => date('c', time() + $results->expires_in)
					),
					'raw' => $userinfo
				);
				
				$this->callback();
			}
			else{
				$error = array(
					'provider' => 'Crozdesk',
					'code' => 'access_token_error',
					'message' => 'Failed when attempting to obtain access token',
					'raw' => array(
						'response' => $response,
						'headers' => $headers
					)
				);

				$this->errorCallback($error);
			}
		}
		else{
			$error = array(
				'provider' => 'Crozdesk',
				'code' => 'oauth2callback_error',
				'raw' => $_GET
			);
			
			$this->errorCallback($error);
		}
	}
	
	/**
	 * Queries Crozdesk for user details
	 *
	 * @param string $access_token 
	 * @return array Parsed JSON results
	 */
	private function userinfo($access_token){
		$userinfo = $this->serverGet('https://crozdesk.com/api/v1/users/me', array('access_token' => $access_token), null, $headers);
		if (!empty($userinfo)){
			return json_decode($userinfo);
		}
		else{
			$error = array(
				'provider' => 'Crozdesk',
				'code' => 'userinfo_error',
				'message' => 'Failed when attempting to query for user information',
				'raw' => array(
					'response' => $userinfo,
					'headers' => $headers
				)
			);

			$this->errorCallback($error);
		}
	}
}
