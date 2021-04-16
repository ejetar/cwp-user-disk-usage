<?php

namespace Ejetar\LeitorHospedagem;

use Httpful\Mime;

class Session {
	public $cookie, $token;

	public function login($username, $password): bool {
		$response = \Httpful\Request::post(
			$_ENV['CWP_URL']."/login/index.php",
			[
				"username" => $username,
				"password" => $password,
				"commit"   => "Login",
			],
			Mime::FORM
		)
			->send();

		preg_match("/^\/(.+)\/admin/", $response->headers['Location'], $matches);

		$this->token = $matches[1];
		$this->cookie = str_replace(" path=/", "", $response->headers['Set-Cookie']);

		return $response->code === 302;
	}
}
