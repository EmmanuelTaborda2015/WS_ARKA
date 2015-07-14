<?php
$_Request['ruta']= 'http://localhost/WS_ARKA/servicio/servicio.php';

$client = new SoapClient(null, array(  'location' => $_Request['ruta'], // Ruta del servidor
		'uri'    => 'urn:arka', // Nombre que se le ha dado al URI del servidor
		'trace'    => 1
)
);

//Este es un ejemplo de como crear un cliente para llamar un servicio web crado con el soap de PHP nativo.
// try {
// 	$resultado = $client->login('1100000', 'eab41e38426312cf48baaaf80af9ee88b6023a44');
//  	echo $resultado;
	
// } catch(SoapDefault $e) {
// 	echo $e->faultstring;
// }

try {
	$resultado = $client->dependencia('CALLE 40');
	var_dump( $resultado );

} catch(SoapDefault $e) {
	echo $e->faultstring;
}
?>