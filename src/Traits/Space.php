<?php

namespace Ejetar\LeitorHospedagem\Traits;

use Httpful\Mime;
use Httpful\Request;

trait Space {
	public array $space;

	public function fetchSpaceByApi() {
		$response = Request::post(
			$_ENV['CWP_URL']."/{$this->session->token}/admin/loader_ajax.php?ajax=list_accounts",
			[
				"accion" => "calculate_space",
				"account" => "$this->name",
			],
			Mime::FORM
		)
			->expectsJson()
			->addHeaders(array(
				'Cookie' => "{$this->session->cookie} _firstImpression=true",
			))
			->send();

		$this->space['mysql'] = (float) $response->body->sizemysql /1000 /1000; //KB para GB
		$this->space['mail'] = (float) $response->body->sizemail /1000 /1000;
	}

	/**
	 * Retorna o espaço ocupado em disco (em GB) da pasta home do usuário
	 *
	 * @param string $method Método: Só 'manual' é suportado atualmente
	 * @return float|null Gigabytes
	 */
	public function getHomeSpace($method = 'manual'): float|null {
		return match($method) {
			'manual' => (float) shell_exec("du -shb /home/$this->name | cut -f1") /1000 /1000 /1000, //B para GB
			default => null,
		};
	}

	//todo documentar
	public function getMysqlSpace($method = 'api'): float|null {
		switch ($method) {
			case 'api':
				if (!isset($this->space['mysql']))
					$this->fetchSpaceByApi();
				return $this->space['mysql'];

			default:
				return null;
		}
	}

	/**
	 * Retorna o espaço ocupado em disco (em GB) das caixas de e-mail dos domínios do usuário
	 * @param string $method
	 * @return float|null Gigabytes
	 */
	public function getMailSpace($method = 'api'): float|null {
		switch ($method) {
			case 'api':
				if (!isset($this->space['mail']))
					$this->fetchSpaceByApi();
				return $this->space['mail'];

			case 'manual':
				$cmd = array_reduce(
					$this->domains,
					function($ac, $domain) {
						$path = "/var/vmail/$domain";
						return file_exists($path) ? "$ac $path" : $ac; //Leva em conta apenas diretórios que existem
					},
					"du -shb"
				);

				$spaces = explode("\n", shell_exec("$cmd | cut -f1"));
				removeLastItemIfEmpty($spaces);

				return array_reduce($spaces, fn($ac, $space) => $ac += $space, 0) /1000 /1000 /1000; //B para GB

			default:
				return null;
		}
	}

	//Retorna disco total ocupado
	//todo documentar
	public function getDiskUsage(): float {
		$this->fetchDomainList();
		return $this->getMailSpace() + $this->getHomeSpace() + $this->getMysqlSpace();
	}
}
