<?php

require_once ("../aplicativo/ProcesadorServicio.php");

 $servidor = new SoapServer(null, array('uri' => 'urn:arka'));
 $servidor->setClass('ProcesadorServicio');
 $servidor->handle();

?>