<?php
include_once ("../core/manager/Configurador.class.php");

class DatoConexion{
	
	
	private $motorDB;
	private $direccionServidor;
	private $puerto;
    private $db;
    private $usuario;
    private $clave;
    private $conf;
    private $dbp;
    
    
    function __construct(){
    	$this->conf = new Configurador();    	 
    	$this->conf->variable();
    	$this->dbp = $this->conf->configuracion;
    }
    
    function setDatosConexion($nombre){
    	    	
    	switch($nombre){
			
    		case "principal":
    			$this->motorDB=$this->dbp['dbsys'];
    			$this->direccionServidor=$this->dbp['dbdns'];
    			$this->puerto=$this->dbp['dbpuerto'];
    			$this->db=$this->dbp['dbnombre'];
    			$this->usuario=$this->dbp['dbusuario'];
    			$this->clave=$this->dbp['dbclave'];    			
    			break;
    			
    		case "frame":
    			break;
    			
    		case "inventarios" :
				break;

			case "movil" :
				break;
    	}
    	
    	return true;
    	
    	
    }    
    

    function setDatosConexionConsulta($datos){
    	 
    	$this->motorDB=$datos[0]['dbms'];
    	$this->direccionServidor=$datos[0]['servidor'];
    	$this->db=$datos[0]['db'];
    	$this->puerto=$datos[0]['puerto'];
    	$this->usuario=$datos[0]['usuario'];
    	$this->clave=$datos[0]['password'];
    	
    	return true;
    	 
    }
    
    function getMotorDB(){
    	return $this->motorDB;
    }

    function getDireccionServidor(){
    	return $this->direccionServidor;
    }
    
    function getPuerto(){
    	return $this->puerto;
    }
    
    function getDb(){
    	return $this->db;
    }
    
    function getUsuario(){
    	return $this->usuario;
    }
    
    function getClave(){
    	return $this->clave;
    }
    
	
}



?>
