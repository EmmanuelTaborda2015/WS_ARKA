<?php
require_once ("../core/crypto/Encriptador.class.php");
require_once ("../core/connection/FabricaDbConexion.class.php");
include_once ("../core/manager/Configurador.class.php");
require_once ("DatoConexion.php");
class ProcesadorServicio {
    var $crypto;
    var $conf;
    var $miFabricaConexiones;
    var $conexionOracle;
    var $conexionPostgresqlFrame;
    var $conexionPostgresqlInventarios;
    var $conexionPostgresqlMovil;
    var $conexionPostgresqlParametros;
    var $mensajeError;
    var $sesionExpiracion;
    var $tiempoExpiracion;
   
   
    // Error 1: No se pudo conectar a ORACLE
    // Error 2: No se pudo conectar a POSTGRESQL
    function __construct() {
        $this->conf = new Configurador();
        $this->miFabricaConexiones = new FabricaDbConexion ();
        $this->mensajeError = 'NINGUNO';
        $this->crypto = new Encriptador ();
        $this->crearConexiones ();
       
        // $this->mensajeError
    }
    private function crearConexiones() {
        $datosConexion = new DatoConexion ();
       
        $resultado = true;
       
        $datosConexion->setDatosConexion ( "principal" );
        $this->miFabricaConexiones->setRecursoDB ( "postgresql", $datosConexion );
        $this->conexionPostgresqlFrame = $this->miFabricaConexiones->getRecursoDB ( "postgresql" );
       
        if (! $this->conexionPostgresqlFrame) {
            ECHO 'ERROR CONECTANDO POSTGRESQL';
            error_log ( 'NO SE CONECTO A POSTGRESQL' );
            $this->mensajeError = 'Error 2';
            return false;
        }
       
    	$cadenaSql = "select * from arka_dbms where nombre='inventarios'";
        $resultado = $this->conexionPostgresqlFrame->ejecutarAcceso ( $cadenaSql, 'busqueda' );
       
        $datosConexion->setDatosConexionConsulta($resultado);
        $datosConexion->setDatosConexion("inventarios");
        $this->miFabricaConexiones->setRecursoDB ( "postgresql", $datosConexion );
        $this->conexionPostgresqlInventarios = $this->miFabricaConexiones->getRecursoDB ( "postgresql" );
       
        if (! $this->conexionPostgresqlInventarios) {
            ECHO 'ERROR CONECTANDO POSTGRESQL';
            error_log ( 'NO SE CONECTO A POSTGRESQL' );
            $this->mensajeError = 'Error 2';
            return false;
        }
        
        return true;
    }
    
	
	// ******************************AQUÍ COMIENZA EL SERVICIO WEB ARKA MOVIL**************************//

    function validarConexion(){//Función que permite validar que la conexión a internet este funcionando correctemente.
    	return "true";	
    }
    
	function validarSesion($usuario, $id_dispositivo){
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'validarSesion', $id_dispositivo );
		$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
		if (count($resultado) > 0){
			if(time() <= $resultado[0]["expiracion"]){
				return 'sesion_activa';
			}else{				
				return 'sesion_expirada';
			}
		}else{
			$log['id_usuario'] = $usuario;
			$log['accion'] = 'ALERTA';
			$log['id_registro'] = $usuario;
			$log['tipo_registro'] = 'ALERTA';
			$log['nombre_registro'] = "Se ha tratado de acceder a la información de forma fraudulenta,". $usuario.",".$dispositivo;
			$log['fecha_log'] = date("F j, Y, g:i:s a");
			$log['descripcion'] = "Salida de sistema desde ArkaMovil, con el dispositivo: ". $dispositivo;
			$log['host'] = $dispositivo;
				
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'registroLogUsuario', $log);
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'registro' );
				
			return 'sesion_fraudulenta';
		}
	}
	
	function cerrarSesion($usuario, $dispositivo){
		$log['id_usuario'] = $usuario;
		$log['accion'] = 'SALIDA';
		$log['id_registro'] = $usuario;
		$log['tipo_registro'] = 'LOGOUT';
		$log['nombre_registro'] = "Cierre de sesión desde ArkaMovil, ".$usuario.",".$dispositivo;
		$log['fecha_log'] = date("F j, Y, g:i:s a");
		$log['descripcion'] = "Salida de sistema desde ArkaMovil, con el dispositivo: ". $dispositivo;
		$log['host'] = $dispositivo;
	
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'registroLogUsuario', $log);
		$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'registro' );
		
		$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'cerrarSesion', $dispositivo);
		$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'borrar' );

		return $resultado;
		
	}
	
	function login($usuario, $contrasenna, $dispositivo) {
		$this->tiempoExpiracion = 5;// Esta dado en minutos
		if($dispositivo!=''){
// 			$contrasenna = $this->crypto->codificarClave($contrasenna);
			
			$datos = array (
					"usuario" => $usuario,
					"contrasena" => $contrasenna 
			);
			
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'login', $datos );
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			
			if ($resultado == false) {
				return "false";
			} else {
				
				$this->sesionExpiracion = time () + $this->tiempoExpiracion * 60;
					
				$log['id_usuario'] = $usuario;
				$log['accion'] = 'INGRESO';
				$log['id_registro'] = $usuario;
				$log['tipo_registro'] = 'LOGIN';
				$log['nombre_registro'] = "Autenticacion Exitosa desde ArkaMovil, ".$usuario.",".$dispositivo;
				$log['fecha_log'] = date("F j, Y, g:i:s a");
				$log['descripcion'] = "Ingreso al sistema desde ArkaMovil, con el dispositivo: ". $dispositivo;
				$log['host'] = $dispositivo;
				
				$sesionExpiracion['id_dispositivo'] = $dispositivo;
				$sesionExpiracion['tipo_sesion'] = 'idUsuario';
				$sesionExpiracion['valor'] = $usuario;
				$sesionExpiracion['expiracion'] = $this->sesionExpiracion;
					
				$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'registroLogUsuario', $log);
				$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'registro' );
					
	
				$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'insertarValorSesion', $sesionExpiracion);
				$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'registro' );
					
				return "true";
				
				// return 'true ' . $resultado[0]['nombre'];
			}
		}
	}
	
	
	function funcionario($text, $usuario, $dispositivo) {
		$sesion = $this->validarSesion($usuario, $dispositivo);
		if (strcmp($sesion, "sesion_fraudulenta") == 0) {
    		return $sesion;
		}else{
			$text = strtoupper($text);
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'funcionario2', $text);
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			return $resultado;
		}
	}
	
	///__________________________________________
	
	function sede($usuario, $dispositivo) {
		$sesion = $this->validarSesion($usuario, $dispositivo);
		if (strcmp($sesion, "sesion_fraudulenta") == 0) {
			return $sesion;
		}else{
			$sesion = $this->validarSesion($usuario, $dispositivo);
			if (strcmp($sesion, "sesion_fraudulenta") == 0) {
				return $sesion;
			}else{
				$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'sede' );
				$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			
				return $resultado;
			}
		}
	}

	///__________________________________________
	
	function dependencia($sede, $usuario, $dispositivo) {
		$sesion = $this->validarSesion($usuario, $dispositivo);
		if (strcmp($sesion, "sesion_fraudulenta") == 0) {
			return $sesion;
		}else{
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'dependencia', $sede );
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			
			return $resultado;
		}
	}

	///__________________________________________
	
	function periodoLevantamiento($usuario, $dispositivo) {
		$sesion = $this->validarSesion($usuario, $dispositivo);
		if (strcmp($sesion, "sesion_fraudulenta") == 0) {
			return $sesion;
		}else{
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ('periodo');
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
				
			return $resultado;
		}
	}
	
	///__________________________________________
	
	function conexionInternet() {
		$resultado = "true";
		return $resultado;
	}
	
	///__________________________________________
	
	function ubicacion($dependencia, $usuario, $dispositivo) {
		$sesion = $this->validarSesion($usuario, $dispositivo);
		if (strcmp($sesion, "sesion_fraudulenta") == 0) {
			return $sesion;
		}else{
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'ubicacion', $dependencia );
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			return $resultado;
		}
	}

	///__________________________________________
	
	function tipoConfirmacionInventario($estado, $criterio, $dato, $offset, $limit, $usuario, $dispositivo) {
		$sesion = $this->validarSesion($usuario, $dispositivo);
		if (strcmp($sesion, "sesion_fraudulenta") == 0) {
			return $sesion;
		}else{
			if($estado == 3){
				$radicado = ' rl.funcionario is not null AND';
			}elseif ($estado == 4){
				$radicado = ' rl.funcionario is null AND';
			}
			if ($criterio == 0) {
				if ($estado > 2) {
					$dato = array (
							"radicado1" => ' LEFT JOIN arka_movil.radicado_levantamiento as rl on ei.funcionario = rl.funcionario',
							"radicado2" => $radicado,
							"offset" => $offset,
							"limit" => $limit,
					);
				} else {
					$dato = array (
		
							"tipo_confirmacion" => ' AND ei.tipo_confirmada = ' .'\'' . $estado . '\'',
							"offset" => $offset,
							"limit" => $limit,
					);
				}
				$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'inventariosTipoConfirmacionTodos', $dato );
				$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			} elseif ($criterio == 1) {
				$variable = explode ( ",", $dato );
				if (count ( $variable ) == 1) {
					if ($estado > 2) {
						$dato = array (
								"sede" => $dato,
								"radicado1" => ' LEFT JOIN arka_movil.radicado_levantamiento as rl on ei.funcionario = rl.funcionario',
								"radicado2" => $radicado,
								"offset" => $offset,
								"limit" => $limit,
						);
					} else {
						$dato = array (
								"sede" => $dato,
								"tipo_confirmacion" => 'AND ei.tipo_confirmada = ' .'\'' . $estado . '\'',
								"offset" => $offset,
								"limit" => $limit,
						);
					}
					$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'inventariosTipoConfirmacionSede', $dato );
					$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
				} elseif (count ( $variable ) == 2) {
					if ($estado > 2) {
						$dato = array (
								"sede" => $variable [0],
								"dependencia" => $variable [1],
								"radicado1" => ' LEFT JOIN arka_movil.radicado_levantamiento as rl on ei.funcionario = rl.funcionario',
								"radicado2" => $radicado,
								"offset" => $offset,
								"limit" => $limit,
						);
					} else {
						$dato = array (
								"sede" => $variable [0],
								"dependencia" => $variable [1],
								"tipo_confirmacion" => 'AND ei.tipo_confirmada = ' .'\'' . $estado . '\'',
								"offset" => $offset,
								"limit" => $limit,
						);
					}
					$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'inventariosTipoConfirmacionDependencia', $dato );
					$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
				}
			} elseif ($criterio == 2) {
				if ($estado > 2) {
					if($estado < 5){
						$dato = array (
								"funcionario" => $dato,
								"radicado1" => ' LEFT JOIN arka_movil.radicado_levantamiento as rl on ei.funcionario = rl.funcionario',
								"radicado2" => $radicado,
								"offset" => $offset,
								"limit" => $limit,
						);
					}else{
						$dato = array (
								"funcionario" => $dato,
								"offset" => $offset,
								"limit" => $limit,
						);
					}
				} else {
					$dato = array (
							"funcionario" => $dato,
							"tipo_confirmacion" => 'AND ei.tipo_confirmada = ' .'\'' . $estado . '\'',
							"offset" => $offset,
							"limit" => $limit,
					);
				}
				$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'inventariosTipoConfirmacionFuncionario', $dato );
				$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			}
			echo $cadenaSql;
			return $resultado;
		}
	}	

	///__________________________________________
	
	function buscarUbicacionesEspecificas($funcionario, $dependencia, $usuario, $dispositivo) {
		$sesion = $this->validarSesion($usuario, $dispositivo);
		if (strcmp($sesion, "sesion_fraudulenta") == 0) {
			return $sesion;
		}else{
			for($i = 0; $i < count ( $dependencia ); $i ++) {
				$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'buscarUbicacionesEspecificas', $dependencia [i] );
				$ubicaciones = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
				
				for($j = 0; j < count ( $ubicaciones ); $j ++) {
					$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'buscarUbicacionesEspecificas', $dependencia [i] );
					$ubicaciones = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
				}
			}
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'buscarUbicacionesEspecificas', $id_levantamiento );
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
		}
	}

	///__________________________________________
	
	function consultar_observacion($id_elemento, $usuario, $dispositivo) {
		$sesion = $this->validarSesion($usuario, $dispositivo);
		if (strcmp($sesion, "sesion_fraudulenta") == 0) {
			return $sesion;
		}else{
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'consultar_observacion', $id_elemento );
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			return $resultado;
		}
	}

	///__________________________________________
	
	function guardarObservacion($id_levantamiento, $id_elemento, $funcionario, $observacion, $tipo_movimiento, $usuario, $dispositivo) {
		$sesion = $this->validarSesion($usuario, $dispositivo);
		if (strcmp($sesion, "sesion_fraudulenta") == 0) {
			return $sesion;
		}else{
			if ($observacion == "") {
				$observacion = null;
			}
			if ($tipo_movimiento == - 1 || $tipo_movimiento=="") {
				$tipo_movimiento = -1;
			}
			
			$datos = array (
					"id_levantamiento" => $id_levantamiento,
					"id_elemento" => $id_elemento,
					"funcionario" => $funcionario,
					"observacion_almacen" => $observacion,
					"tipo_movimiento" => $tipo_movimiento 
			);
			
				$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'guardar_observacion', $datos );
				$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'insertar' );
	
				if($resultado==true){
					$log['id_usuario'] = $usuario;
					$log['accion'] = 'REGISTRO';
					$log['id_registro'] = $usuario;
					$log['tipo_registro'] = 'REGISTRAR';
					$log['nombre_registro'] = "Registro de Observación Exitoso desde ArkaMovil, ".$usuario.",".$dispositivo;
					$log['fecha_log'] = date("F j, Y, g:i:s a");
					$log['descripcion'] = "Registro de Observación: ". $observacion . ", Posible tipo de Movimiento: " . $tipo_movimiento . ". ArkaMovil, con el dispositivo: ". $dispositivo;
					$log['host'] = $dispositivo;
					
					$sesionExpiracion['id_dispositivo'] = $dispositivo;
					$sesionExpiracion['tipo_sesion'] = 'idUsuario';
					$sesionExpiracion['valor'] = $usuario;
					$sesionExpiracion['expiracion'] = $this->sesionExpiracion;
						
					$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'registroLogUsuario', $log);
					$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'registro' );
				}
				return $resultado;	
		}	
		
	}

	///__________________________________________
		
	function elementosFuncionario($funcionario, $dependencia, $usuario, $dispositivo) {
		$sesion = $this->validarSesion($usuario, $dispositivo);
		if (strcmp($sesion, "sesion_fraudulenta") == 0) {
			return $sesion;
		}else{
			$datos = array (
					'funcionario' => $funcionario,
					'dependencia' => $dependencia 
			);
			
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'elementosInventario', $datos );
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			return $resultado;
		}
	}
	
	///__________________________________________
	
	function elementoFuncionarioPlaca($id_elemento, $usuario, $dispositivo) {	
		$sesion = $this->validarSesion($usuario, $dispositivo);
		if (strcmp($sesion, "sesion_fraudulenta") == 0) {
			return $sesion;
		}else{	
	
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'elementoInventarioPlaca', $id_elemento );
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			return $resultado;
		}
	}

	///__________________________________________
	
	function tipoConfirmacion($dependencia, $usuario, $dispositivo) {
		$sesion = $this->validarSesion($usuario, $dispositivo);
		if (strcmp($sesion, "sesion_fraudulenta") == 0) {
			return $sesion;
		}else{
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'tipoConfirmacion' );
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			return $resultado;
		}
	}

	///__________________________________________
	
	function consultar_visita($usuario, $dispositivo) {
		$sesion = $this->validarSesion($usuario, $dispositivo);
		if (strcmp($sesion, "sesion_fraudulenta") == 0) {
			return $sesion;
		}else{
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'num_visita' );
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			if ($resultado == false) {
				return 'false';
			} else {
				$resultado = $resultado [0] ['max'] + 1;
				return $resultado;
			}
		}
	}

	///__________________________________________
	
	function registrarActaVisita($sede, $dependencia, $responsable, $observacion, $fecha, $proxima_vis, $ubicacion, $usuario, $dispositivo) {
		$sesion = $this->validarSesion($usuario, $dispositivo);
		if (strcmp($sesion, "sesion_fraudulenta") == 0) {
			return $sesion;
		}else{
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
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'insertar' );
			
			if($resultado==true){
				$log['id_usuario'] = $usuario;
				$log['accion'] = 'REGISTRO';
				$log['id_registro'] = $usuario;
				$log['tipo_registro'] = 'REGISTRAR';
				$log['nombre_registro'] = "Registro de Acta de Visita Exitoso desde ArkaMovil, ".$usuario.",".$dispositivo;
				$log['fecha_log'] = date("F j, Y, g:i:s a");
				$log['descripcion'] = "Registro de Acta de Visita: [". $sede.", ".$dependencia.", ".$ubicacion.", ".$fecha.", ".$proxima_vis.", ".$responsable . " ArkaMovil, con el dispositivo: ". $dispositivo;
				$log['host'] = $dispositivo;
					
				$sesionExpiracion['id_dispositivo'] = $dispositivo;
				$sesionExpiracion['tipo_sesion'] = 'idUsuario';
				$sesionExpiracion['valor'] = $usuario;
				$sesionExpiracion['expiracion'] = $this->sesionExpiracion;
			
				$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'registroLogUsuario', $log);
				$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'registro' );
			}
			
			return $resultado;
		}
	}

	///__________________________________________
	
	function actualizarInventario($elemento, $serie, $placa, $estado, $observacion, $fecha_registro, $usuario, $dispositivo) {
		$sesion = $this->validarSesion($usuario, $dispositivo);
		if (strcmp($sesion, "sesion_fraudulenta") == 0) {
			return $sesion;
		}else{
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'actualizar_inventario', $elemento );
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'actualizar' );
			
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
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'insertar' );
			
			return $resultado;
		}
	}

	///__________________________________________
	
	function consultar_elementos($funcionario, $usuario, $dispositivo) {
		$sesion = $this->validarSesion($usuario, $dispositivo);
		if (strcmp($sesion, "sesion_fraudulenta") == 0) {
			return $sesion;
		}else{
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'consultar_elementos', $funcionario );
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			
			// En la aplicación android se debe cambiar id_elemento por id_elemento_ind
			return $resultado;
		}
	}

	///__________________________________________
	
	function consultar_elementos_dependencia($dependencia, $usuario, $dispositivo) {
		$sesion = $this->validarSesion($usuario, $dispositivo);
		if (strcmp($sesion, "sesion_fraudulenta") == 0) {
			return $sesion;
		}else{
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'elementos_dependencia', $dependencia );
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			
			// En la aplicación android se debe cambiar id_elemento por id_elemento_ind
			return $resultado;
		}
	}

	///__________________________________________
	
	function consultar_placa($placa, $usuario, $dispositivo) {
		$sesion = $this->validarSesion($usuario, $dispositivo);
		if (strcmp($sesion, "sesion_fraudulenta") == 0) {
			return $sesion;
		}else{
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'elementos_placa', $placa );
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			
			// En la aplicación android se debe cambiar id_elemento por id_elemento_ind
			return $resultado;
		}
	}

	///__________________________________________
	
	function consultar_asignaciones($id_elemento, $usuario, $dispositivo) {
		$sesion = $this->validarSesion($usuario, $dispositivo);
		if (strcmp($sesion, "sesion_fraudulenta") == 0) {
			return $sesion;
		}else{
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'consultar_asignaciones', $id_elemento );
			$datos = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			
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
	}

	///__________________________________________
	
	function consultar_estado($id_elemento, $usuario, $dispositivo) {
		$sesion = $this->validarSesion($usuario, $dispositivo);
		if (strcmp($sesion, "sesion_fraudulenta") == 0) {
			return $sesion;
		}else{
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'consultar_estado', $id_elemento );
			$datos = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'estado_tipo_falt_sobr', $datos [0] ['estado'] );
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			
			$resultado = $resultado [0] ['descripcion'];
			
			return $resultado;
		}
	}

	///__________________________________________
	
	function asignar_elementos($fecha_inicio, $fecha_final, $usuario, $dispositivo) {
		$sesion = $this->validarSesion($usuario, $dispositivo);
		if (strcmp($sesion, "sesion_fraudulenta") == 0) {
			return $sesion;
		}else{
			$datos = array (
					'fecha_inicio' => $fecha_inicio,
					'fecha_final' => $fecha_final 
			);
			
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'asignar_elementos', $datos );
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			
			// En la aplicación android se debe cambiar id_elemento por id_elemento_ind
			return $resultado;
		}
	}

	///__________________________________________
	
	function asignar_elementos_funcionario($sede, $dependencia, $funcionario, $id_elemento, $observaciones, $fecha_registro, $ubicacion, $usuario, $dispositivo) {
		$sesion = $this->validarSesion($usuario, $dispositivo);
		if (strcmp($sesion, "sesion_fraudulenta") == 0) {
			return $sesion;
		}else{
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
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'actualizar' );
			
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'asignar_elementos_funcionario', $datos );
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'insertar' );
			
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'seleccionar_datos', $datos ['id_elemento'] );
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			
			$resultado = $this->actualizarInventario ( $id_elemento, $resultado [0] ['serie'], $resultado [0] ['placa'], 'Existente y Activo', 'registrado mediante ArkaMovil' . $fecha_registro . ', ' . $observaciones, $fecha_registro );
			
			// En la aplicación android se debe cambiar id_elemento por id_elemento_ind
			return $resultado;
		}
	}

	///__________________________________________
	
	function asignar_imagen($id_elemento, $imagen, $usuario, $dispositivo) {
		$sesion = $this->validarSesion($usuario, $dispositivo);
		if (strcmp($sesion, "sesion_fraudulenta") == 0) {
			return $sesion;
		}else{
			
			$datos = array (
					'id_elemento' => $id_elemento,
					'imagen' => $imagen 
			);		
			
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'asignar_imagen', $datos );
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'insertar' );			
			
			if($resultado==true){
				$log['id_usuario'] = $usuario;
				$log['accion'] = 'REGISTRO';
				$log['id_registro'] = $usuario;
				$log['tipo_registro'] = 'REGISTRAR';
				$log['nombre_registro'] = "Registro o Asociación de Imagen Exitoso desde ArkaMovil, ".$usuario.",".$dispositivo;
				$log['fecha_log'] = date("F j, Y, g:i:s a");
				$log['descripcion'] = "Registro de Imagen Al Elemento: [". $id_elemento . "] ArkaMovil, con el dispositivo: ". $dispositivo;
				$log['host'] = $dispositivo;
					
				$sesionExpiracion['id_dispositivo'] = $dispositivo;
				$sesionExpiracion['tipo_sesion'] = 'idUsuario';
				$sesionExpiracion['valor'] = $usuario;
				$sesionExpiracion['expiracion'] = $this->sesionExpiracion;
					
				$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'registroLogUsuario', $log);
				$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'registro' );
			}
				
			
			return $resultado;
		}
	}

	///__________________________________________
	
	function consultar_placa_imagen($placa, $usuario, $dispositivo) {
		$sesion = $this->validarSesion($usuario, $dispositivo);
		if (strcmp($sesion, "sesion_fraudulenta") == 0) {
			return $sesion;
		}else{
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'consultar_placa_imagen', $placa );
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			
			$resultado = $resultado [0] ['id_elemento_ind'];
			
			return $resultado;
		}
	}

	///__________________________________________
	
	function consultar_imagen($id_elemento, $usuario, $dispositivo) {
		$sesion = $this->validarSesion($usuario, $dispositivo);
		if (strcmp($sesion, "sesion_fraudulenta") == 0) {
			return $sesion;
		}else{
			$cadenaSql = $this->miFabricaConexiones->getCadenaSql ( 'consultar_imagen', $id_elemento );
			$resultado = $this->conexionPostgresqlInventarios->ejecutarAcceso ( $cadenaSql, 'busqueda' );
			
			$resultado = $resultado [0] ['imagen'];
			return $resultado;
		}
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
// $resultado = $llamada->tipoConfirmacionInventario ( "4", "0", "", "", "" );
// $resultado = $llamada->elementosFuncionario('79708124', 'FCMB');
// $resultado = $llamada->elementoFuncionarioPlaca('2015071400000');
// $resultado = $llamada->consultar_observacion ( '1' );
// $resultado = $llamada->guardarObservacion("", "2", "79889", "hola", "" );
// echo($resultado);
// var_dump ( $resultado );
?>
