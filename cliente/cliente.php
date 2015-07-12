<?php
$_Request['ruta']= 'http://localhost/WebServicePSE-master/servicio/servicio.php';

$client = new SoapClient(null, array(  'location' => $_Request['ruta'], // Ruta del servidor
		'uri'    => 'urn:arka', // Nombre que se le ha dado al URI del servidor
		'trace'    => 1
)
);

try {
	$resultado = $client->login('1100000', 'eab41e38426312cf48baaaf80af9ee88b6023a44');
 	echo $resultado;
	
} catch(SoapDefault $e) {
	echo $e->faultstring;
}
?>