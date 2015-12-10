<?php
$_Request['ruta']= 'http://localhost/WS_ARKA/servicio/servicio.php';

$client = new SoapClient(null, array(  'location' => $_Request['ruta'], // Ruta del servidor
		'uri'    => 'urn:arka', // Nombre que se le ha dado al URI del servidor
		'trace'    => 1
)
);

?>