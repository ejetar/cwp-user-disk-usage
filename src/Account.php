<?php

namespace Ejetar\LeitorHospedagem;

use Ejetar\LeitorHospedagem\Traits\Domain;
use Ejetar\LeitorHospedagem\Traits\Process;
use Ejetar\LeitorHospedagem\Traits\Space;

use Httpful\Request;

class Account {
	use Space, Domain, Process;

	public string $name;
	public array $domains;
	public Session $session;

	public function __construct($name, $session) {
		$this->name = $name;
		$this->session = $session;
	}

	public static function getList($session) {
		$responseAccounts = Request::get($_ENV['CWP_URL']."/{$session->token}/admin/loader_ajax.php?ajax=list_accounts&tipo=all")
			->expectsJson()
			->addHeaders(array(
				'Cookie' => "{$session->cookie} _firstImpression=true",
			))
			->send();

		if ($responseAccounts->code !== 200)
			return false;

		return array_map(function ($item) use($session) {
			//Captura o nome do usuário em meio a maçaroca de dados
			preg_match("/^[^\s]*/", $item[0], $matches);

			return new Account($matches[0], $session);
		}, $responseAccounts->body->aaData);
	}
}
