<?php
require_once ("../core/crypto/Encriptador.class.php");
require_once ("../core/connection/FabricaDbConexion.class.php");
require_once ("DatoConexion.php");
class ProcesadorServicio {
	var $crypto;
	var $miFabricaConexiones;
	var $conexionOracle;
	var $conexionPostgresqlFrame;
	var $conexionPostgresqlInventarios;
	var $conexionPostgresqlMovil;
	var $conexionPostgresqlParametros;
	var $mensajeError;
	
	// Error 1: No se pudo conectar a ORACLE
	// Error 2: No se pudo conectar a POSTGRESQL
	function __construct() {
		$this->miFabricaConexiones = new FabricaDBConexion ();
		$this->mensajeError = 'NINGUNO';
		$this->crypto = new Encriptador ();
		$this->crearConexiones ();
		
		// $this->mensajeError
	}
	private function crearConexiones() {
		$datosConexion = new DatoConexion ();
		
		$resultado = true;
		
		// // 1. Crear conexion a ORACLE:
		// $datosConexion->setDatosConexion ( "oracle" );
		// $this->miFabricaConexiones->setRecursoDB ( "oracle", $datosConexion );
		// $this->conexionOracle = $this->miFabricaConexiones->getRecursoDB ( "oracle" );
		// if (! $this->conexionOracle) {
		// error_log ('NO SE CONECTO A ORACLE' );
		// $this->mensajeError='Error 1';
		// return false;
		// }
		
		// 1. Crear conexión a POSTGRESQL ARKA_FRAME.
		$datosConexion->setDatosConexion ( "frame" );
		$this->miFabricaConexiones->setRecursoDB ( "postgresql", $datosConexion );
		$this->conexionPostgresqlFrame = $this->miFabricaConexiones->getRecursoDB ( "postgresql" );
		
		if (! $this->conexionPostgresqlFrame) {
			ECHO 'ERROR CONECTANDO POSTGRESQL';
			error_log ( 'NO SE CONECTO A POSTGRESQL' );
			$this->mensajeError = 'Error 2';
			return false;
		}
		
		// 2. Crear conexión a POSTGRESQL ARKA_INVENTARIOS.
		$datosConexion->setDatosConexion ( "inventarios" );
		$this->miFabricaConexiones->setRecursoDB ( "postgresql", $datosConexion );
		$this->conexionPostgresqlInventarios = $this->miFabricaConexiones->getRecursoDB ( "postgresql" );
		
		if (! $this->conexionPostgresqlInventarios) {
			ECHO 'ERROR CONECTANDO POSTGRESQL';
			error_log ( 'NO SE CONECTO A POSTGRESQL' );
			$this->mensajeError = 'Error 2';
			return false;
		}
		
		// 3. Crear conexión a POSTGRESQL ARKA_MOVIL.
		$datosConexion->setDatosConexion ( "movil" );
		$this->miFabricaConexiones->setRecursoDB ( "postgresql", $datosConexion );
		$this->conexionPostgresqlMovil = $this->miFabricaConexiones->getRecursoDB ( "postgresql" );
		
		if (! $this->conexionPostgresqlMovil) {
			ECHO 'ERROR CONECTANDO POSTGRESQL';
			error_log ( 'NO SE CONECTO A POSTGRESQL' );
			$this->mensajeError = 'Error 2';
			return false;
		}
		
		// 4. Crear conexión a POSTGRESQL ARKA_PARAMETROS.
		$datosConexion->setDatosConexion ( "parametros" );
		$this->miFabricaConexiones->setRecursoDB ( "postgresql", $datosConexion );
		$this->conexionPostgresqlParametros = $this->miFabricaConexiones->getRecursoDB ( "postgresql" );
		
		if (! $this->conexionPostgresqlParametros) {
			ECHO 'ERROR CONECTANDO POSTGRESQL';
			error_log ( 'NO SE CONECTO A POSTGRESQL' );
			$this->mensajeError = 'Error 2';
			return false;
		}
		
		return true;
	}
	
	// ******************************AQUÍ COMIENZA EL SERVICIO WEB ARKA MOVIL**************************//
	function login($usuario, $contrasenna) {
		
		// $contrasenna = $this->crypto->decodificar('eab41e38426312cf48baaaf80af9ee88b6023a44');
		
		// $contrasenna = $this->crypto->codificar('sistemasoas');
		// $contrasenna = $this->crypto->codificarClave($contrasenna);
		if ($contrasenna == 'sistemasoas') {
			$contrasenna = 'eab41e38426312cf48baaaf80af9ee88b6023a44';
		}
		
		$datos = array (
				"usuario" => $usuario,
				"contrasena" => $contrasenna 
		);
		
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'login', $datos );
		$resultado = $this->conexionPostgresqlFrame->ejecutarAcceso ( $cadenaSql, 'busqueda' );
		
		if ($resultado == false) {
			return "false";
		} else {
			return "true";
			// return 'true ' . $resultado[0]['nombre'];
		}
	}
	function funcionario() {
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'funcionario' );
		$resultado = $this->conexionPostgresqlParametros->ejecutarAcceso ( $cadenaSql, 'busqueda' );
		
		return $resultado;
	}
	function sede() {
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'sede' );
		$resultado = $this->conexionPostgresqlParametros->ejecutarAcceso ( $cadenaSql, 'busqueda' );
		
		return $resultado;
	}
	function dependencia($sede) {
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'dependencia', $sede );
		$resultado = $this->conexionPostgresqlParametros->ejecutarAcceso ( $cadenaSql, 'busqueda' );
		
		return $resultado;
	}
	function ubicacion($dependencia) {
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'ubicacion', $dependencia );
		$resultado = $this->conexionPostgresqlParametros->ejecutarAcceso ( $cadenaSql, 'busqueda' );
		return $resultado;
	}
	function tipoConfirmacionInventario($estado, $criterio, $dato, $offset, $limit) {
		if($estado == 3){
			$est = "TRUE";
		}elseif ($estado == 4){
			$est = "FALSE";
		}
		if ($criterio == 0) {
			if ($estado > 2) {				
					$dato = array (
							"radicado" => ' AND ei.radicado = ' .'\'' . $est . '\'',
					);								
			} else {
				$dato = array (
						
						"tipo_confirmacion" => 'AND ei.tipo_confirmada = ' .'\'' . $estado . '\'', 
				);
			}			
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'inventariosTipoConfirmacionTodos', $dato );
			$resultado = $this->conexionPostgresqlParametros->ejecutarAcceso ( $cadenaSql, 'busqueda' );
		} elseif ($criterio == 1) {
			$variable = explode ( ",", $dato );
			if (count ( $variable ) == 1) {
				if ($estado > 2) {
					$dato = array (
							"sede" => $dato,
							"radicado" => ' AND ei.radicado = ' .'\'' . $est . '\'',
					);
				} else {
					$dato = array (
							"sede" => $dato,
							"tipo_confirmacion" => 'AND ei.tipo_confirmada = ' .'\'' . $estado . '\'',
					);
				}				
				$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'inventariosTipoConfirmacionSede', $dato );
				$resultado = $this->conexionPostgresqlParametros->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			} elseif (count ( $variable ) == 2) {				
				if ($estado > 2) {
					$dato = array (
							"sede" => $variable [0],
							"dependencia" => $variable [1],
							"radicado" => ' AND ei.radicado = ' .'\'' . $est . '\'',
					);
				} else {
					$dato = array (
							"sede" => $variable [0],
							"dependencia" => $variable [1],
							"tipo_confirmacion" => 'AND ei.tipo_confirmada = ' .'\'' . $estado . '\'',
					);
				}
				$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'inventariosTipoConfirmacionDependencia', $dato );
				$resultado = $this->conexionPostgresqlParametros->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			}
		} elseif ($criterio == 2) {
			if ($estado > 2) {
				if($estado < 5){
					$dato = array (
							"funcionario" => $dato,
							"radicado" => ' AND ei.radicado = ' .'\'' . $est . '\'',
					);
				}else{
					$dato = array (
							"funcionario" => $dato,
					);
				}
			} else {
				$dato = array (
						"funcionario" => $dato,
						"tipo_confirmacion" => 'AND ei.tipo_confirmada = ' .'\'' . $estado . '\'',
				);
			}			
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'inventariosTipoConfirmacionFuncionario', $dato );
			$resultado = $this->conexionPostgresqlParametros->ejecutarAcceso ( $cadenaSql, 'busqueda' );				
		} 
		return $resultado;
	}
	function consultar_observacion($id_levantamiento) {
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'consultar_observacion', $id_levantamiento );
		$resultado = $this->conexionPostgresqlMovil->ejecutarAcceso ( $cadenaSql, 'busqueda' );
		
		return $resultado;
	}
	function guardarObservacion($id_levantamiento, $id_elemento, $funcionario, $observacion, $tipo_movimiento) {
		if ($observacion == "") {
			$observacion = null;
		}
		if ($tipo_movimiento == - 1) {
			$tipo_movimiento = null;
		}
		
		$datos = array (
				"id_levantamiento" => $id_levantamiento,
				"id_elemento" => $id_elemento,
				"funcionario" => $funcionario,
				"observacion_almacen" => $observacion,
				"tipo_movimiento" => $tipo_movimiento 
		);
		
		if ($id_levantamiento == "") {
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'insertar_guardar_observacion', $datos );
			$resultado = $this->conexionPostgresqlMovil->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			
			$resul = $resultado [0] ["id_levantamiento"];
			
			$datos = array (
					"id_levantamiento" => $resultado [0] ["id_levantamiento"],
					"id_elemento" => $id_elemento,
					"funcionario" => $funcionario,
					"observacion_almacen" => $observacion,
					"tipo_movimiento" => $tipo_movimiento 
			);
			
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'actualizar_guardar_observacion', $datos );
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'actualizar' );
			
			return $resul;
		} else {
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'guardar_observacion', $datos );
			$resultado = $this->conexionPostgresqlMovil->ejecutarAcceso ( $cadenaSql, 'actualizar' );
			return $resultado;
		}
	}
	function elementosFuncionario($funcionario, $dependencia) {
		$datos = array (
				'funcionario' => $funcionario,
				'dependencia' => $dependencia 
		);
		
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'elementosInventario', $datos );
		$resultado = $this->conexionPostgresqlParametros->ejecutarAcceso ( $cadenaSql, 'busqueda' );
		return $resultado;
	}
	function tipoConfirmacion($dependencia) {
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'tipoConfirmacion' );
		$resultado = $this->conexionPostgresqlParametros->ejecutarAcceso ( $cadenaSql, 'busqueda' );
		return $resultado;
	}
	function consultar_visita() {
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'num_visita' );
		$resultado = $this->conexionPostgresqlMovil->ejecutarAcceso ( $cadenaSql, 'busqueda' );
		if ($resultado == false) {
			return 'false';
		} else {
			$resultado = $resultado [0] ['max'] + 1;
			return $resultado;
		}
	}
	function registrarActaVisita($sede, $dependencia, $responsable, $observacion, $fecha, $proxima_vis, $ubicacion) {
		$datos = array (
				"sede" => $sede,
				"dependencia" => $dependencia,
				"responsable" => $responsable,
				"observacion" => $observacion,
				"fecha" => $fecha,
				"proxima_vis" => $proxima_vis,
				"ubicacion" => $ubicacion 
		);
		
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'registrarActaVisita', $datos );
		$resultado = $this->conexionPostgresqlMovil->ejecutarAcceso ( $cadenaSql, 'insertar' );
		
		return $resultado;
	}
	function actualizarInventario($elemento, $serie, $placa, $estado, $observacion, $fecha_registro) {
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'actualizar_inventario', $elemento );
		$resultado = $this->conexionPostgresqlMovil->ejecutarAcceso ( $cadenaSql, 'actualizar' );
		
		if (strcmp ( strtolower ( $estado ), 'existente y activo' ) == 0) {
			$estado_actualizacion = 'Actualizado';
		} else {
			$estado_actualizacion = 'En espera de actualizar';
		}
		
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'tipo_falt_sobr', $estado );
		$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
		
		$estado = $resultado [0] ['id_tipo_falt_sobr'];
		
		$datos = array (
				"elemento" => $elemento,
				"serie" => $serie,
				"placa" => $placa,
				"estado" => $estado,
				"estado_actualizacion" => $estado_actualizacion,
				"observacion" => $observacion,
				"fecha_registro" => $fecha_registro 
		);
		
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'insertar_inventario', $datos );
		$resultado = $this->conexionPostgresqlMovil->ejecutarAcceso ( $cadenaSql, 'insertar' );
		
		return $resultado;
	}
	function consultar_elementos($funcionario) {
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'consultar_elementos', $funcionario );
		$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
		
		// En la aplicación android se debe cambiar id_elemento por id_elemento_ind
		return $resultado;
	}
	function consultar_elementos_dependencia($dependencia) {
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'elementos_dependencia', $dependencia );
		$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
		
		// En la aplicación android se debe cambiar id_elemento por id_elemento_ind
		return $resultado;
	}
	function consultar_placa($placa) {
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'elementos_placa', $placa );
		$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
		
		// En la aplicación android se debe cambiar id_elemento por id_elemento_ind
		return $resultado;
	}
	function consultar_asignaciones($id_elemento) {
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'consultar_asignaciones', $id_elemento );
		$datos = $this->conexionPostgresqlMovil->ejecutarAcceso ( $cadenaSql, 'busqueda' );
		
		foreach ( $datos as $a ) {
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'estado_tipo_falt_sobr', $a ['estado'] );
			$estado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			$resultado [] = [ 
					'id_elemento_ind' => $a ['id_elemento_ind'],
					'placa' => $a ['placa'],
					'estado' => $estado [0] ['descripcion'],
					'estado_actualizacion' => $a ['estado_actualizacion'],
					'observacion' => $a ['observacion'] 
			];
		}
		
		// En la aplicación android se debe cambiar id_elemento por id_elemento_ind
		return $resultado;
	}
	function consultar_estado($id_elemento) {
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'consultar_estado', $id_elemento );
		$datos = $this->conexionPostgresqlMovil->ejecutarAcceso ( $cadenaSql, 'busqueda' );
		
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'estado_tipo_falt_sobr', $datos [0] ['estado'] );
		$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
		
		$resultado = $resultado [0] ['descripcion'];
		
		return $resultado;
	}
	function asignar_elementos($fecha_inicio, $fecha_final) {
		$datos = array (
				'fecha_inicio' => $fecha_inicio,
				'fecha_final' => $fecha_final 
		);
		
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'asignar_elementos', $datos );
		$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
		
		// En la aplicación android se debe cambiar id_elemento por id_elemento_ind
		return $resultado;
	}
	function asignar_elementos_funcionario($sede, $dependencia, $funcionario, $id_elemento, $observaciones, $fecha_registro, $ubicacion) {
		$datos = array (
				'sede' => $sede,
				'dependencia' => $dependencia,
				'funcionario' => $funcionario,
				'id_elemento' => $id_elemento,
				'observaciones' => $observaciones,
				'fecha_registro' => $fecha_registro,
				'ubicacion' => $ubicacion 
		);
		
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'actualizar_elementos', $datos ['id_elemento'] );
		$resultado = $this->conexionPostgresqlMovil->ejecutarAcceso ( $cadenaSql, 'actualizar' );
		
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'asignar_elementos_funcionario', $datos );
		$resultado = $this->conexionPostgresqlMovil->ejecutarAcceso ( $cadenaSql, 'insertar' );
		
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'seleccionar_datos', $datos ['id_elemento'] );
		$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
		
		$resultado = $this->actualizarInventario ( $id_elemento, $resultado [0] ['serie'], $resultado [0] ['placa'], 'Existente y Activo', 'registrado mediante ArkaMovil' . $fecha_registro . ', ' . $observaciones, $fecha_registro );
		
		// En la aplicación android se debe cambiar id_elemento por id_elemento_ind
		return $resultado;
	}
	function asignar_imagen($id_elemento, $imagen) {
		$datos = array (
				'id_elemento' => $id_elemento,
				'imagen' => $imagen 
		);
		
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'actualizar_imagen', $datos ['id_elemento'] );
		$resultado = $this->conexionPostgresqlMovil->ejecutarAcceso ( $cadenaSql, 'actualizar' );
		
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'asignar_imagen', $datos );
		$resultado = $this->conexionPostgresqlMovil->ejecutarAcceso ( $cadenaSql, 'insertar' );
		
		return $resultado;
	}
	function consultar_placa_imagen($placa) {
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'consultar_placa_imagen', $placa );
		$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
		
		$resultado = $resultado [0] ['id_elemento_ind'];
		
		return $resultado;
	}
	function consultar_imagen($id_elemento) {
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'consultar_imagen', $id_elemento );
		$resultado = $this->conexionPostgresqlMovil->ejecutarAcceso ( $cadenaSql, 'busqueda' );
		
		$resultado = $resultado [0] ['imagen'];
		return $resultado;
	}
	
	// ******************************AQUÍ TERMINA EL SERVICIO WEB ARKA MOVIL**************************//
}

// $llamada = new ProcesadorServicio ();

// $resultado = $llamada-> login('1100000', 'sistemasoas');
// $resultado = $llamada->funcionario();
// $resultado = $llamada->sede();
// $resultado = $llamada->dependencia('FICC');
// $resultado = $llamada->ubicacion('DEP180');
// $resultado = $llamada-> consultar_visita();
// $resultado = $llamada->registrarActaVisita('sede', 'dependencia', '1032418216', 'ninguna', '15/08/2015', '15/07/2016');
// $resultado = $llamada->actualizarInventario('1', '1234', '12345', 'Existente y Activo', 'ninguna', '15/08/1988');
// $resultado = $llamada->consultar_elementos('7169011');
// $resultado = $llamada->consultar_elementos_dependencia('FMVI050109');
// $resultado = $llamada->consultar_placa('2015070600000');
// $resultado = $llamada->consultar_asignaciones('1');
// $resultado = $llamada->consultar_estado('3');
// $resultado = $llamada->consultar_observacion('3');
// $resultado = $llamada->asignar_elementos('14/07/2015', '15/07/2015');
// $resultado = $llamada->asignar_elementos_funcionario('sede', 'dependencia', '1032418216', '12', 'ninguna', '11/07/2015');
// $resultado = $llamada->asignar_imagen('3', 'imagen_emmanuel');
// $resultado = $llamada->consultar_placa_imagen('2015070600000');
// $resultado = $llamada->consultar_imagen('3');
// $resultado = $llamada->tipoConfirmacionInventario ( "5", "2", "79708124", "", "" );
// $resultado = $llamada->elementosFuncionario('79708124', 'FICC040211');
// $resultado = $llamada->consultar_observacion('1');
// $resultado = $llamada->guardarObservacion("", "2", "79889", "hola", "1" );
// echo($resultado);
// var_dump ( $resultado );
?>
