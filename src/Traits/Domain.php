<?php

namespace Ejetar\LeitorHospedagem\Traits;

trait Domain {
	/**
	 * Traz lista de domínios da conta, para o atributo $domains
	 * @return void
	 */
	public function fetchDomainList(): void {
		$this->domains = explode("\n", shell_exec("/scripts/cwp_api account list_domains $this->name"));
		removeLastItemIfEmpty($this->domains);
	}
}
