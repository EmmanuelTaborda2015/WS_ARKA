<?php
class Sql {
	
	var $cadenaSql;
	
	function __construct() {
	}
	
	function sql($opcion, $variable = '') {
		
		switch ($opcion) {
					
			case 'login' :
				
				$cadenaSql = 'SELECT';
				$cadenaSql .= ' nombre';
				$cadenaSql .= ' FROM';
				$cadenaSql .= ' arka_usuario';
				$cadenaSql .= ' WHERE';				
				$cadenaSql .= ' id_usuario='. '\'' . $variable["usuario"] . '\'';
				$cadenaSql .= ' AND';
				$cadenaSql .= ' clave='. '\'' . $variable["contrasena"] . '\'';	
				$this->cadenaSql = $cadenaSql; 	
				break;
				
			case 'funcionario' :
				
				$cadenaSql = 'SELECT';
				$cadenaSql .= " \"FUN_NOMBRE\",";
				$cadenaSql .= " \"FUN_IDENTIFICACION\"";
				$cadenaSql .= ' FROM';
				$cadenaSql .= ' arka_parametros.arka_funcionarios';
				$cadenaSql .= ' WHERE';
				$cadenaSql .= " \"FUN_ESTADO\"='A'";				
				$this->cadenaSql = $cadenaSql;				
				break;		

			case 'sede' :
				$cadenaSql = "SELECT DISTINCT  \"ESF_ID_SEDE\", \"ESF_SEDE\" ";
				$cadenaSql .= " FROM arka_parametros.arka_sedes ";
				$cadenaSql .= " WHERE   \"ESF_ESTADO\"='A' ";
				$cadenaSql .= " AND    \"ESF_COD_SEDE\" >  0 ";	
				$this->cadenaSql = $cadenaSql;
				break;
			
			case 'dependencia' :
				
				$cadenaSql = "SELECT DISTINCT  \"ESF_CODIGO_DEP\" , \"ESF_DEP_ENCARGADA\" ";
				$cadenaSql .= " FROM arka_parametros.arka_dependencia ad ";
				$cadenaSql .= " JOIN  arka_parametros.arka_espaciosfisicos ef ON  ef.\"ESF_ID_ESPACIO\"=ad.\"ESF_ID_ESPACIO\" ";
				$cadenaSql .= " JOIN  arka_parametros.arka_sedes sa ON sa.\"ESF_COD_SEDE\"=ef.\"ESF_COD_SEDE\" ";
				$cadenaSql .= " WHERE sa.\"ESF_SEDE\"='" . $variable . "' ";
				$cadenaSql .= " AND  ad.\"ESF_ESTADO\"='A'";
				$this->cadenaSql = $cadenaSql;
				break;
			
			case 'ubicacion' :
				
				$cadenaSql = "SELECT DISTINCT  ef.\"ESF_ID_ESPACIO\" , ef.\"ESF_NOMBRE_ESPACIO\" ";
				$cadenaSql .= " FROM arka_parametros.arka_espaciosfisicos ef  ";
				$cadenaSql .= " JOIN arka_parametros.arka_dependencia ad ON ad.\"ESF_ID_ESPACIO\"=ef.\"ESF_ID_ESPACIO\" ";
				$cadenaSql .= " WHERE ad.\"ESF_CODIGO_DEP\"='" . $variable . "' ";
				$cadenaSql .= " AND  ef.\"ESF_ESTADO\"='A'";
				$this->cadenaSql = $cadenaSql;
				break;
						
			case 'num_visita' :
				
				$cadenaSql = 'SELECT';
				$cadenaSql .= ' MAX';
				$cadenaSql .= ' (';
				$cadenaSql .= ' num_visita';
				$cadenaSql .= ' )';
				$cadenaSql .= ' FROM';
				$cadenaSql .= ' arka_movil.acta_visita';
				$this->cadenaSql = $cadenaSql;
				break;
				
			case 'registrarActaVisita' :
				
				$cadenaSql = 'INSERT';
				$cadenaSql .= ' INTO';
				$cadenaSql .= ' arka_movil.acta_visita';
				$cadenaSql .= ' (';
				$cadenaSql .= ' sede,';
				$cadenaSql .= ' dependencia,';
				$cadenaSql .= ' responsable,';
				$cadenaSql .= ' observacion,';
				$cadenaSql .= ' fecha,';
				$cadenaSql .= ' proxima_vis';
				$cadenaSql .= ' )';
				$cadenaSql .= ' VALUES';
				$cadenaSql .= ' (';
				$cadenaSql .= ' \'' . $variable["sede"] . '\', ';
				$cadenaSql .= '\'' . $variable["dependencia"] . '\', ';
				$cadenaSql .= '\'' . $variable["responsable"] . '\', ';
				$cadenaSql .= '\'' . $variable["observacion"] . '\', ';
				$cadenaSql .= '\'' . $variable["fecha"] . '\', ';
				$cadenaSql .= '\'' . $variable["proxima_vis"] . '\' ';
				$cadenaSql .= ' );';
				$this->cadenaSql = $cadenaSql;
				break;
				
				//En este caso se actualiza el estado de los inventarios a false.
			case 'actualizar_inventario' :
				
				$cadenaSql = 'UPDATE';
				$cadenaSql .= ' arka_movil.actualizar_inventario';
				$cadenaSql .= ' SET';
				$cadenaSql .= ' estado_registro=FALSE';
				$cadenaSql .= ' WHERE';
				$cadenaSql .= ' id_elemento_ind='. '\'' . $variable . '\'';	
				$this->cadenaSql = $cadenaSql;
				break;	

			case 'tipo_falt_sobr' :
				
				$cadenaSql = 'SELECT';
				$cadenaSql .= ' id_tipo_falt_sobr';
				$cadenaSql .= ' FROM';
				$cadenaSql .= ' tipo_falt_sobr';
				$cadenaSql .= ' WHERE';
				$cadenaSql .= ' descripcion=' . '\'' . $variable . '\'';
				$this->cadenaSql = $cadenaSql;
				break;
				
				//Se inserta un nuevo regitro que pasa a "reemplazar al que se le cambio en estado a false en el sql actualizar_inventario"
			case 'insertar_inventario' :
				
				$cadenaSql = 'INSERT';
				$cadenaSql .= ' INTO';
				$cadenaSql .= ' arka_movil.actualizar_inventario';
				$cadenaSql .= ' (';
				$cadenaSql .= ' id_elemento_ind,';
				$cadenaSql .= ' serie,';
				$cadenaSql .= ' placa,';
				$cadenaSql .= ' estado,';
				$cadenaSql .= ' estado_actualizacion,';
				$cadenaSql .= ' observacion,';
				$cadenaSql .= ' fecha_registro';
				$cadenaSql .= ' )';
				$cadenaSql .= ' VALUES';
				$cadenaSql .= ' (';
				$cadenaSql .= ' \'' . $variable["elemento"] . '\', ';
				$cadenaSql .= ' \'' . $variable["serie"] . '\', ';
				$cadenaSql .= ' \'' . $variable["placa"] . '\', ';
				$cadenaSql .= ' \'' . $variable["estado"] . '\', ';
				$cadenaSql .= ' \'' . $variable["estado_actualizacion"] . '\', ';
				$cadenaSql .= ' \'' . $variable["observacion"] . '\', ';
				$cadenaSql .= ' \'' . $variable["fecha_registro"] . '\'';
				$cadenaSql .= ' );';				
				$this->cadenaSql = $cadenaSql;
				break;
				
			case 'consultar_elementos' :
				
				$cadenaSql = 'SELECT';
				$cadenaSql .= ' *';
				$cadenaSql .= ' FROM';
				$cadenaSql .= ' (';
				$cadenaSql .= ' (';
				$cadenaSql .= ' (';
				$cadenaSql .= ' SELECT';
				$cadenaSql .= ' elemento_individual.id_elemento_ind,';
				$cadenaSql .= ' elemento.descripcion,';
				$cadenaSql .= ' elemento.nivel,';
				$cadenaSql .= ' elemento.marca,';
				$cadenaSql .= ' elemento_individual.placa,';
				$cadenaSql .= ' elemento_individual.serie,';
				$cadenaSql .= ' elemento.valor,';
				$cadenaSql .= ' elemento.subtotal_sin_iva,';
				$cadenaSql .= ' elemento.total_iva,';
				$cadenaSql .= ' elemento.total_iva_con,';
				$cadenaSql .= ' salida.sede,';
				$cadenaSql .= ' salida.dependencia';
				$cadenaSql .= ' FROM';
				$cadenaSql .= ' elemento,';
				$cadenaSql .= ' salida,';
				$cadenaSql .= ' elemento_individual';
				$cadenaSql .= ' WHERE';
				$cadenaSql .= ' elemento.id_elemento=elemento_individual.id_elemento_gen';
				$cadenaSql .= ' AND elemento_individual.estado_registro=TRUE';
				$cadenaSql .= ' AND salida.id_salida=elemento_individual.id_salida';
				$cadenaSql .= ' AND salida.id_entrada=elemento.id_entrada';
				$cadenaSql .= ' AND elemento_individual.estado_asignacion=True';
				$cadenaSql .= ' AND elemento_individual.funcionario=' . '\'' . $variable . '\'';
				$cadenaSql .= ' ORDER BY nivel ASC';
				$cadenaSql .= ' )';
				$cadenaSql .= ' EXCEPT';
				$cadenaSql .= ' (';
				$cadenaSql .= ' SELECT';
				$cadenaSql .= ' DISTINCT';
				$cadenaSql .= ' elemento_individual.id_elemento_ind,';
				$cadenaSql .= ' elemento.descripcion,';
				$cadenaSql .= ' elemento.nivel,';
				$cadenaSql .= ' elemento.marca,';
				$cadenaSql .= ' elemento_individual.placa,';
				$cadenaSql .= ' elemento_individual.serie,';
				$cadenaSql .= ' elemento.valor,';
				$cadenaSql .= ' elemento.subtotal_sin_iva,';
				$cadenaSql .= ' elemento.total_iva,';
				$cadenaSql .= ' elemento.total_iva_con,';
				$cadenaSql .= ' arka_movil.registro_elementos_funcionario. sede,';
				$cadenaSql .= ' arka_movil.registro_elementos_funcionario.dependencia';
				$cadenaSql .= ' FROM';
				$cadenaSql .= ' elemento,';
				$cadenaSql .= ' elemento_individual,';
				$cadenaSql .= ' arka_movil.registro_elementos_funcionario';
				$cadenaSql .= ' WHERE';
				$cadenaSql .= ' elemento.id_elemento=elemento_individual.id_elemento_gen';
				$cadenaSql .= ' AND arka_movil.registro_elementos_funcionario.id_elemento=elemento_individual.id_elemento_ind';
				$cadenaSql .= ' AND elemento_individual.estado_registro=TRUE';
				$cadenaSql .= ' )';
				$cadenaSql .= ' )';
				$cadenaSql .= ' UNION';
				$cadenaSql .= ' (';
				$cadenaSql .= ' SELECT';
				$cadenaSql .= ' DISTINCT';
				$cadenaSql .= ' elemento_individual.id_elemento_ind,';
				$cadenaSql .= ' elemento.descripcion,';
				$cadenaSql .= ' elemento.nivel,';
				$cadenaSql .= ' elemento.marca,';
				$cadenaSql .= ' elemento_individual.placa,';
				$cadenaSql .= ' elemento_individual.serie,';
				$cadenaSql .= ' elemento.valor,';
				$cadenaSql .= ' elemento.subtotal_sin_iva,';
				$cadenaSql .= ' elemento.total_iva,';
				$cadenaSql .= ' elemento.total_iva_con,';
				$cadenaSql .= ' arka_movil.registro_elementos_funcionario. sede,';
				$cadenaSql .= ' arka_movil.registro_elementos_funcionario.dependencia';
				$cadenaSql .= ' FROM';
				$cadenaSql .= ' elemento,';
				$cadenaSql .= ' elemento_individual,';
				$cadenaSql .= ' arka_movil.registro_elementos_funcionario';
				$cadenaSql .= ' WHERE';
				$cadenaSql .= ' elemento.id_elemento=elemento_individual.id_elemento_gen';
				$cadenaSql .= ' AND arka_movil.registro_elementos_funcionario.id_elemento=elemento_individual.id_elemento_ind';
				$cadenaSql .= ' AND elemento_individual.estado_registro=TRUE';
				$cadenaSql .= ' AND arka_movil.registro_elementos_funcionario.estado_registro=TRUE';
				$cadenaSql .= ' AND arka_movil.registro_elementos_funcionario.funcionario=' . '\'' . $variable . '\'';
				$cadenaSql .= ' ORDER BY nivel ASC';
				$cadenaSql .= ' )';
				$cadenaSql .= ' )';
				$cadenaSql .= ' AS a';
				$cadenaSql .= ' ORDER BY';
				$cadenaSql .= ' a.id_elemento_ind';			
				$this->cadenaSql = $cadenaSql;
				break;
					
			case 'elementos_dependencia' :
				
				$cadenaSql = 'SELECT';
				$cadenaSql .= ' *';
				$cadenaSql .= ' FROM';
				$cadenaSql .= ' (';
				$cadenaSql .= ' (';
				$cadenaSql .= ' (';
				$cadenaSql .= ' SELECT';
				$cadenaSql .= ' elemento_individual.id_elemento_ind,';
				$cadenaSql .= ' elemento.descripcion,';
				$cadenaSql .= ' elemento.nivel,';
				$cadenaSql .= ' elemento.marca,';
				$cadenaSql .= ' elemento_individual.placa,';
				$cadenaSql .= ' elemento_individual.serie,';
				$cadenaSql .= ' elemento.valor,';
				$cadenaSql .= ' elemento.subtotal_sin_iva,';
				$cadenaSql .= ' elemento.total_iva,';
				$cadenaSql .= ' elemento.total_iva_con,';
				$cadenaSql .= ' salida.funcionario';
				$cadenaSql .= ' FROM';
				$cadenaSql .= ' elemento,';
				$cadenaSql .= ' salida,';
				$cadenaSql .= ' elemento_individual';
				$cadenaSql .= ' WHERE';
				$cadenaSql .= ' elemento.id_elemento=elemento_individual.id_elemento_gen';
				$cadenaSql .= ' AND elemento_individual.estado_registro=TRUE';
				$cadenaSql .= ' AND salida.id_salida=elemento_individual.id_salida';
				$cadenaSql .= ' AND salida.id_entrada=elemento.id_entrada';
				$cadenaSql .= ' AND elemento_individual.estado_asignacion=True';
				$cadenaSql .= ' AND salida.dependencia=' . '\'' . $variable . '\'';
				$cadenaSql .= ' ORDER BY nivel ASC';
				$cadenaSql .= ' )';
				$cadenaSql .= ' EXCEPT';
				$cadenaSql .= ' (';
				$cadenaSql .= ' SELECT';
				$cadenaSql .= ' DISTINCT';
				$cadenaSql .= ' elemento_individual.id_elemento_ind,';
				$cadenaSql .= ' elemento.descripcion,';
				$cadenaSql .= ' elemento.nivel,';
				$cadenaSql .= ' elemento.marca,';
				$cadenaSql .= ' elemento_individual.placa,';
				$cadenaSql .= ' elemento_individual.serie,';
				$cadenaSql .= ' elemento.valor,';
				$cadenaSql .= ' elemento.subtotal_sin_iva,';
				$cadenaSql .= ' elemento.total_iva,';
				$cadenaSql .= ' elemento.total_iva_con,';
				$cadenaSql .= ' arka_movil.registro_elementos_funcionario. funcionario';
				$cadenaSql .= ' FROM';
				$cadenaSql .= ' elemento,';
				$cadenaSql .= ' elemento_individual,';
				$cadenaSql .= ' arka_movil.registro_elementos_funcionario';
				$cadenaSql .= ' WHERE';
				$cadenaSql .= ' elemento.id_elemento=elemento_individual.id_elemento_gen';
				$cadenaSql .= ' AND arka_movil.registro_elementos_funcionario.id_elemento=elemento_individual.id_elemento_ind';
				$cadenaSql .= ' AND elemento_individual.estado_registro=TRUE';
				$cadenaSql .= ' )';
				$cadenaSql .= ' )';
				$cadenaSql .= ' UNION';
				$cadenaSql .= ' (';
				$cadenaSql .= ' SELECT';
				$cadenaSql .= ' DISTINCT';
				$cadenaSql .= ' elemento_individual.id_elemento_ind,';
				$cadenaSql .= ' elemento.descripcion,';
				$cadenaSql .= ' elemento.nivel,';
				$cadenaSql .= ' elemento.marca,';
				$cadenaSql .= ' elemento_individual.placa,';
				$cadenaSql .= ' elemento_individual.serie,';
				$cadenaSql .= ' elemento.valor,';
				$cadenaSql .= ' elemento.subtotal_sin_iva,';
				$cadenaSql .= ' elemento.total_iva,';
				$cadenaSql .= ' elemento.total_iva_con,';
				$cadenaSql .= ' arka_movil.registro_elementos_funcionario. funcionario';
				$cadenaSql .= ' FROM';
				$cadenaSql .= ' elemento,';
				$cadenaSql .= ' elemento_individual,';
				$cadenaSql .= ' arka_movil.registro_elementos_funcionario';
				$cadenaSql .= ' WHERE';
				$cadenaSql .= ' elemento.id_elemento=elemento_individual.id_elemento_gen';
				$cadenaSql .= ' AND arka_movil.registro_elementos_funcionario.id_elemento=elemento_individual.id_elemento_ind';
				$cadenaSql .= ' AND elemento_individual.estado_registro=TRUE';
				$cadenaSql .= ' AND arka_movil.registro_elementos_funcionario.estado_registro=TRUE';
				$cadenaSql .= ' AND arka_movil.registro_elementos_funcionario.dependencia=' . '\'' . $variable . '\'';
				$cadenaSql .= ' ORDER BY nivel ASC';
				$cadenaSql .= ' )';
				$cadenaSql .= ' )';
				$cadenaSql .= ' AS a';
				$cadenaSql .= ' ORDER BY';
				$cadenaSql .= ' a.id_elemento_ind';		
				$this->cadenaSql = $cadenaSql;
				break;
				
			case 'elementos_placa' :
				
				$cadenaSql = '(';				
				$cadenaSql .= ' (';
				$cadenaSql .= ' SELECT';
				$cadenaSql .= ' elemento_individual.id_elemento_ind,';
				$cadenaSql .= ' elemento.descripcion,';
				$cadenaSql .= ' elemento.nivel,';
				$cadenaSql .= ' elemento.marca,';
				$cadenaSql .= ' elemento_individual.placa,';
				$cadenaSql .= ' elemento_individual.serie,';
				$cadenaSql .= ' elemento.valor,';
				$cadenaSql .= ' elemento.subtotal_sin_iva,';
				$cadenaSql .= ' elemento.total_iva,';
				$cadenaSql .= ' elemento.total_iva_con';
				$cadenaSql .= ' FROM';
				$cadenaSql .= ' elemento,';
				$cadenaSql .= ' salida,';
				$cadenaSql .= ' elemento_individual';
				$cadenaSql .= ' WHERE';
				$cadenaSql .= ' elemento.id_elemento=elemento_individual.id_elemento_gen';
				$cadenaSql .= ' AND elemento_individual.estado_registro=TRUE';
				$cadenaSql .= ' AND salida.id_salida=elemento_individual.id_salida';
				$cadenaSql .= ' AND salida.id_entrada=elemento.id_entrada';
				$cadenaSql .= ' AND elemento_individual.estado_asignacion=True';
				$cadenaSql .= ' AND elemento_individual.placa=' . '\'' . $variable . '\'';
				$cadenaSql .= ' )';
				$cadenaSql .= ' EXCEPT';
				$cadenaSql .= ' (';
				$cadenaSql .= ' SELECT';
				$cadenaSql .= ' DISTINCT';
				$cadenaSql .= ' elemento_individual.id_elemento_ind,';
				$cadenaSql .= ' elemento.descripcion,';
				$cadenaSql .= ' elemento.nivel,';
				$cadenaSql .= ' elemento.marca,';
				$cadenaSql .= ' elemento_individual.placa,';
				$cadenaSql .= ' elemento_individual.serie,';
				$cadenaSql .= ' elemento.valor,';
				$cadenaSql .= ' elemento.subtotal_sin_iva,';
				$cadenaSql .= ' elemento.total_iva,';
				$cadenaSql .= ' elemento.total_iva_con';
				$cadenaSql .= ' FROM';
				$cadenaSql .= ' elemento,';
				$cadenaSql .= ' elemento_individual,';
				$cadenaSql .= ' arka_movil.registro_elementos_funcionario';
				$cadenaSql .= ' WHERE';
				$cadenaSql .= ' elemento.id_elemento=elemento_individual.id_elemento_gen';
				$cadenaSql .= ' AND arka_movil.registro_elementos_funcionario.id_elemento=elemento_individual.id_elemento_ind';
				$cadenaSql .= ' AND elemento_individual.estado_registro=TRUE';
				$cadenaSql .= ' )';
				$cadenaSql .= ' )';
				$cadenaSql .= ' UNION';
				$cadenaSql .= ' (';
				$cadenaSql .= ' SELECT';
				$cadenaSql .= ' DISTINCT';
				$cadenaSql .= ' elemento_individual.id_elemento_ind,';
				$cadenaSql .= ' elemento.descripcion,';
				$cadenaSql .= ' elemento.nivel,';
				$cadenaSql .= ' elemento.marca,';
				$cadenaSql .= ' elemento_individual.placa,';
				$cadenaSql .= ' elemento_individual.serie,';
				$cadenaSql .= ' elemento.valor,';
				$cadenaSql .= ' elemento.subtotal_sin_iva,';
				$cadenaSql .= ' elemento.total_iva,';
				$cadenaSql .= ' elemento.total_iva_con';
				$cadenaSql .= ' FROM';
				$cadenaSql .= ' elemento,';
				$cadenaSql .= ' elemento_individual,';
				$cadenaSql .= ' arka_movil.registro_elementos_funcionario';
				$cadenaSql .= ' WHERE';
				$cadenaSql .= ' elemento.id_elemento=elemento_individual.id_elemento_gen';
				$cadenaSql .= ' AND arka_movil.registro_elementos_funcionario.id_elemento=elemento_individual.id_elemento_ind';
				$cadenaSql .= ' AND elemento_individual.estado_registro=TRUE';
				$cadenaSql .= ' AND arka_movil.registro_elementos_funcionario.estado_registro=TRUE';
				$cadenaSql .= ' AND elemento_individual.placa=' . '\'' . $variable . '\'';
				$cadenaSql .= ' ORDER BY nivel ASC';
				$cadenaSql .= ' )';			
				$this->cadenaSql = $cadenaSql;
				break;
				
			case 'consultar_asignaciones' :
				
				$cadenaSql = 'SELECT';
				$cadenaSql .= ' id_elemento_ind,';
				$cadenaSql .= ' placa,';
				$cadenaSql .= ' estado,';
				$cadenaSql .= ' estado_actualizacion,';
				$cadenaSql .= ' observacion';
				$cadenaSql .= ' FROM';
				$cadenaSql .= ' arka_movil.actualizar_inventario';
				$cadenaSql .= ' WHERE';
				$cadenaSql .= ' id_elemento_ind=' . '\'' . $variable . '\'';
				$cadenaSql .= ' AND estado_registro=True';	
				$this->cadenaSql = $cadenaSql;
				break;
				
			case 'estado_tipo_falt_sobr' :
				
				$cadenaSql = 'SELECT';
				$cadenaSql .= ' descripcion';
				$cadenaSql .= ' FROM';
				$cadenaSql .= ' tipo_falt_sobr';
				$cadenaSql .= ' WHERE';
				$cadenaSql .= ' id_tipo_falt_sobr=' . '\'' . $variable . '\'';
				$this->cadenaSql = $cadenaSql;
				break;
				
			case 'consultar_estado' :
				
				$cadenaSql = 'SELECT';
				$cadenaSql .= ' estado';
				$cadenaSql .= ' FROM';
				$cadenaSql .= ' arka_movil.actualizar_inventario';
				$cadenaSql .= ' WHERE';
				$cadenaSql .= ' estado_registro=TRUE';				
				$cadenaSql .= ' AND id_elemento_ind=' . '\'' . $variable . '\'';
				$this->cadenaSql = $cadenaSql;
				break;
				
			case 'consultar_observacion' :
				
				$cadenaSql = 'SELECT';
				$cadenaSql .= ' observacion';
				$cadenaSql .= ' FROM';
				$cadenaSql .= ' arka_movil.actualizar_inventario';
				$cadenaSql .= ' WHERE';
				$cadenaSql .= ' estado_registro=TRUE';
				$cadenaSql .= ' AND id_elemento_ind=' . '\'' . $variable . '\'';
				$this->cadenaSql = $cadenaSql;
				break;

			case 'asignar_elementos' :
				
				$cadenaSql = 'SELECT';
				$cadenaSql .= ' *';
				$cadenaSql .= ' FROM';
				$cadenaSql .= ' (';
				$cadenaSql .= ' SELECT';
				$cadenaSql .= ' elemento_individual.id_elemento_ind,';
				$cadenaSql .= ' elemento.descripcion,';
				$cadenaSql .= ' elemento.nivel,';
				$cadenaSql .= ' elemento.marca,';
				$cadenaSql .= ' elemento_individual.placa,';
				$cadenaSql .= ' elemento_individual.serie';
				$cadenaSql .= ' FROM';
				$cadenaSql .= ' arka_inventarios.elemento,';
				$cadenaSql .= ' arka_inventarios.elemento_individual';
				$cadenaSql .= ' WHERE';
				$cadenaSql .= ' elemento.id_elemento=elemento_individual.id_elemento_gen';
				$cadenaSql .= ' AND elemento_individual.estado_asignacion=FALSE';
				$cadenaSql .= ' AND elemento.fecha_registro';
				$cadenaSql .= ' BETWEEN';
				$cadenaSql .= ' CAST';
				$cadenaSql .= ' (';
				$cadenaSql .= ' \'' . $variable['fecha_inicio'] . '\'';
				$cadenaSql .= ' AS';
				$cadenaSql .= ' DATE';
				$cadenaSql .= ' )';
				$cadenaSql .= ' AND';
				$cadenaSql .= ' CAST';
				$cadenaSql .= ' (';
				$cadenaSql .= ' \'' . $variable['fecha_final'] . '\'';		
				$cadenaSql .= ' AS';
				$cadenaSql .= ' DATE';
				$cadenaSql .= ' )';
				$cadenaSql .= ' EXCEPT';
				$cadenaSql .= ' SELECT';
				$cadenaSql .= ' DISTINCT';
				$cadenaSql .= ' elemento_individual.id_elemento_ind,';
				$cadenaSql .= ' elemento.descripcion,';
				$cadenaSql .= ' elemento.nivel,';
				$cadenaSql .= ' elemento.marca, elemento_individual.placa,';
				$cadenaSql .= ' elemento_individual.serie';
				$cadenaSql .= ' FROM';
				$cadenaSql .= ' arka_inventarios.elemento,';
				$cadenaSql .= ' arka_inventarios.elemento_individual,';
				$cadenaSql .= ' arka_movil.registro_elementos_funcionario';
				$cadenaSql .= ' WHERE';
				$cadenaSql .= ' elemento.id_elemento=elemento_individual.id_elemento_gen';
				$cadenaSql .= ' AND elemento_individual.id_elemento_ind=registro_elementos_funcionario.id_elemento';
				$cadenaSql .= ' AND registro_elementos_funcionario.estado_registro=TRUE';
				$cadenaSql .= ' )';
				$cadenaSql .= ' AS';
				$cadenaSql .= ' a';
				$cadenaSql .= ' ORDER BY';
				$cadenaSql .= ' a.id_elemento_ind';			
				$this->cadenaSql = $cadenaSql;
				break;
				
			case 'actualizar_elementos' :
				
				$cadenaSql = 'UPDATE';
				$cadenaSql .= ' arka_movil.registro_elementos_funcionario';
				$cadenaSql .= ' SET';
				$cadenaSql .= ' estado_registro=FALSE';
				$cadenaSql .= ' WHERE';
				$cadenaSql .= ' id_elemento=' . '\'' . $variable . '\'';
				$this->cadenaSql = $cadenaSql;
				break;
				
			case 'asignar_elementos_funcionario' :
				
				$cadenaSql = 'INSERT';
				$cadenaSql .= ' INTO';
				$cadenaSql .= ' arka_movil.registro_elementos_funcionario';
				$cadenaSql .= ' (';
				$cadenaSql .= ' sede,';
				$cadenaSql .= ' dependencia,';
				$cadenaSql .= ' funcionario,';
				$cadenaSql .= ' id_elemento,';
				$cadenaSql .= ' fecha_registro';
				$cadenaSql .= ' )';
				$cadenaSql .= ' VALUES';
				$cadenaSql .= ' (';
				$cadenaSql .= ' \'' . $variable["sede"] . '\', ';
				$cadenaSql .= ' \'' . $variable["dependencia"] . '\', ';
				$cadenaSql .= ' \'' . $variable["funcionario"] . '\', ';
				$cadenaSql .= ' \'' . $variable["id_elemento"] . '\', ';
				$cadenaSql .= ' \'' . $variable["fecha_registro"] . '\' ';				
				$cadenaSql .= ' )';
				$this->cadenaSql = $cadenaSql;
				break;
				
			case 'seleccionar_datos' :
				
				$cadenaSql = 'SELECT';
				$cadenaSql .= ' placa,';
				$cadenaSql .= ' serie';
				$cadenaSql .= ' FROM';
				$cadenaSql .= ' elemento_individual';
				$cadenaSql .= ' WHERE';
				$cadenaSql .= ' id_elemento_ind=' . '\'' . $variable . '\'';
				$this->cadenaSql = $cadenaSql;
				break;
				
			case 'actualizar_imagen' :
				
				$cadenaSql = 'UPDATE';
				$cadenaSql .= ' arka_movil.asignar_imagen';
				$cadenaSql .= ' SET';
				$cadenaSql .= ' estado_registro=False';
				$cadenaSql .= ' WHERE';
				$cadenaSql .= ' id_elemento=' . '\'' . $variable . '\'';	
				$this->cadenaSql = $cadenaSql;
				break;
				
			case 'asignar_imagen' :
				
				$cadenaSql = 'INSERT';
				$cadenaSql .= ' INTO';
				$cadenaSql .= ' arka_movil.asignar_imagen';
				$cadenaSql .= ' (';
				$cadenaSql .= ' id_elemento,';
				$cadenaSql .= ' imagen';
				$cadenaSql .= ' )';
				$cadenaSql .= ' VALUES';
				$cadenaSql .= ' (';
				$cadenaSql .= ' \'' . $variable["id_elemento"] . '\', ';
				$cadenaSql .= ' \'' . $variable["imagen"] . '\'';	
				$cadenaSql .= ' )';
				$this->cadenaSql = $cadenaSql;
				break;
				
			case 'consultar_placa_imagen' :
				
				$cadenaSql = 'SELECT';
				$cadenaSql .= ' elemento_individual.id_elemento_ind';
				$cadenaSql .= ' FROM';
				$cadenaSql .= ' elemento_individual';
				$cadenaSql .= ' WHERE';
				$cadenaSql .= ' elemento_individual.placa=' . '\'' . $variable . '\'';
				$this->cadenaSql = $cadenaSql;
				break;
				
			case 'consultar_imagen' :
				
				$cadenaSql = 'SELECT';
				$cadenaSql .= ' imagen';
				$cadenaSql .= ' FROM';
				$cadenaSql .= ' arka_movil.asignar_imagen';
				$cadenaSql .= ' WHERE';
				$cadenaSql .= ' estado_registro = TRUE';				
				$cadenaSql .= ' AND id_elemento=' . '\'' . $variable . '\'';
				$this->cadenaSql = $cadenaSql;
				break;
											
			case '' :
				
				$cadenaSql = '';
				$cadenaSql .= ' ';
				$cadenaSql .= ' ';
				$cadenaSql .= ' ';
				$cadenaSql .= ' ';
				$cadenaSql .= ' ';
				$cadenaSql .= ' ';
				$cadenaSql .= ' ';
				$cadenaSql .= ' ';
				$cadenaSql .= ' ';
				$cadenaSql .= ' ';
				$cadenaSql .= ' ';
				$cadenaSql .= ' ';
				$cadenaSql .= ' ';
				$cadenaSql .= ' ';
				$cadenaSql .= ' ';
				break;
				
		}
			
		error_log($this->cadenaSql);
		return true;
	}
	
	function getCadenaSql(){
		return $this->cadenaSql;
	}
}
?>
