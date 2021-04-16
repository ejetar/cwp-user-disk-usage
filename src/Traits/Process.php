<?php

namespace Ejetar\LeitorHospedagem\Traits;

trait Process {
	//Retorna número de processos abertos pelo usuário
	public function openedProcesses() {
		$pids = explode("\n", shell_exec("ps --no-headers -u ejetar -U ejetar | cut -d \" \" -f1"));
		removeLastItemIfEmpty($pids);

		return array_reduce($pids, fn($ac, $pid) => $ac += $pid, 0);
	}
}
