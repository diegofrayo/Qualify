<?php

namespace Dominio\ObjetosDeNegocio;

use Dominio\Clases\Usuario;
use Dominio\Excepciones\BusinessLogicException;
use Dominio\Daos\DaoGrupoDeNotas;
use Dominio\Daos\DaoPeriodo;
use Dominio\Daos\DaoNota;
use Dominio\Daos\DaoAsignatura;
use Dominio\Clases\GrupoDeNotas;
use Dominio\Clases\Periodo;
use Dominio\Clases\Asignatura;
use Dominio\Clases\Nota;

$_SERVER['DOCUMENT_ROOT'] = 'C:/xampp/htdocs/Qualify';

require_once $_SERVER['DOCUMENT_ROOT'].'/Dominio/Daos/DaoPeriodo.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Dominio/Daos/DaoAsignatura.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Dominio/Daos/DaoNota.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Dominio/Daos/DaoGrupoDeNotas.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Dominio/Clases/Periodo.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Dominio/Clases/Asignatura.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Dominio/Clases/Nota.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Dominio/Clases/GrupoDeNotas.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Dominio/Excepciones/BusinessLogicException.php';

/**
 * Esta clase es un objeto de negocio.
 * Va a manejar toda la logica de las siguientes entidades:
 * Nota, GrupoDeNotas, Asignatura, Periodo
 */

class BoLogicaNotas
{

	private $_notasDao;
	private $_asignaturaDao;
	private $_grupoDeNotasDao;
	private $_periodoDao;

	public function __construct()
	{
		$this->_asignaturaDao = new DaoAsignatura();
		$this->_notasDao = new DaoNota();
		$this->_periodoDao = new DaoPeriodo();
		$this->_grupoDeNotasDao = new DaoGrupoDeNotas();
	}


	/**
	 * Metodo para crear una asignatura
	 * @param Asignatura $asignatura
	 * @throws BusinessLogicException
	 * @return Asignatura
	 */
	public function crearAsignatura(Asignatura $asignatura)
	{
		if($this->obtenerAsignaturaPorId($asignatura->getId()) == null ){
			$asignatura = $this->_asignaturaDao->crear($asignatura);
			$grupoNotasDefecto = new GrupoDeNotas(0, "Calificaciones", false, true);
			$grupoNotasDefecto->setAsignatura($asignatura);
			$this->crearGrupoDeNotas($grupoNotasDefecto);
			return $asignatura;
		}
		throw new BusinessLogicException("La asignatura ya existe");
	}

	/**
	 * Metodo para borrar una asignatura
	 * @param Asignatura $asignatura
	 * @throws BusinessLogicException
	 * @return Asignatura
	 */
	public function borrarAsignatura(Asignatura $asignatura)
	{
		if($this->obtenerAsignaturaPorId($asignatura->getId()) != null ){
			return $this->_asignaturaDao->borrar($asignatura);
		}
		throw new BusinessLogicException("La asignatura que va a borrar no existe");
	}

	/**
	 * Metodo para editar una asignatura
	 * @param Asignatura $asignatura
	 * @throws BusinessLogicException
	 * @return Asignatura
	 */
	public function editarAsignatura(Asignatura $asignatura)
	{
		if($this->obtenerAsignaturaPorId($asignatura->getId()) != null ){
			return $this->_asignaturaDao->editar($asignatura);
		}
		throw new BusinessLogicException("La asignatura que va a editar no existe");
	}

	/**
	 * Metodo para obtener una asignatura a traves de un id
	 * @param unknown_type $id
	 * @return Ambigous <NULL, \Dominio\Clases\Asignatura>
	 */
	public function obtenerAsignaturaPorId($id)
	{
		return $this->_asignaturaDao->obtenerAsignaturaPorId($id);
	}

	/**
	 * Metodo para calcular la nota final de una asignatura
	 * @param Asignatura $asignatura
	 * @throws BusinessLogicException
	 * @return number
	 */
	public function calcularNotaFinalDeUnaAsignatura(Asignatura $asignatura)
	{
		$grupoDefectoAsignatura = $this->obtenerGrupoDefectoDeUnaAsignatura($asignatura);
		$notaFinal  = $this->calcularPromedioDeNotasDeUnGrupo($grupoDefectoAsignatura);
		return $notaFinal;
	}

	/**
	 * Metodo para obtener las asignaturas de un periodo
	 * @param Periodo $periodo
	 * @throws BusinessLogicException
	 */
	public function obtenerListaDeAsignaturasDeUnPeriodo(Periodo $periodo)
	{
		if($this->obtenerPeriodoPorId($periodo->getId())!=null ){
			return $this->_asignaturaDao->obtenerListaDeAsignaturasDeUnPeriodo($periodo);
		}
		throw new BusinessLogicException("El periodo no existe");
	}

	/**
	 * Metodo para crear una nota
	 * @param Nota $nota
	 * @throws BusinessLogicException
	 * @return Nota
	 */
	public function crearNota(Nota $nota)
	{
		if($this->obtenerNotaPorId($nota->getId()) ==null ){
			return $this->_notasDao->crear($nota);
		}
		throw new BusinessLogicException("La nota ya existe");
	}

	/**
	 * Metodo para editar una nota
	 * @param Nota $nota
	 * @throws BusinessLogicException
	 * @return Nota
	 */
	public function editarNota(Nota $nota)
	{
		if($this->obtenerNotaPorId($nota->getId()) !=null ){
			return $this->_notasDao->editar($nota);
		}
		throw new BusinessLogicException("La nota que quiere editar no existe");
	}

	/**
	 * Metodo para borrar una nota
	 * @param Nota $nota
	 * @throws BusinessLogicException
	 * @return Nota
	 */
	public function borrarNota(Nota $nota)
	{
		if($this->obtenerNotaPorId($nota->getId()) !=null ){
			return $this->_notasDao->borrar($nota);
		}
		throw new BusinessLogicException("La nota que quiere borrar no existe");
	}

	/**
	 * Metodo para obtener una nota por id
	 * @param unknown_type $id
	 * @return NULL
	 */
	private function obtenerNotaPorId($id)
	{
		return $this->_notasDao->obtenerNotaPorId($id);
	}

	/**
	 * Metodo para obtener la lista de notas de un grupo
	 * @param GrupoDeNotas $grupo
	 * @throws BusinessLogicException
	 * @return Ambigous <NULL, multitype:\Dominio\Clases\Nota >
	 */
	public function obtenerListaDeNotasDeUnGrupo(GrupoDeNotas $grupo)
	{
		//if($this->obtenerGrupoDeNotasPorId($grupo->getId())!=null){
		return $this->_notasDao->obtenerListaDeNotasDeUnGrupo($grupo);
		//	}
		//throw new BusinessLogicException("El grupo de notas no existe");
	}

	public function obtenerNotasFuturas($fecha,$emailUsuario)
	{
		return $this->_notasDao->obtenerNotasFuturas($fecha,$emailUsuario);
	}

	/**
	 * Metodo para crear un periodo
	 * @param Periodo $periodo
	 * @throws BusinessLogicException
	 * @return Periodo
	 */
	public function crearPeriodo(Periodo $periodo)
	{
		if($this->obtenerPeriodoPorId($periodo->getId()) == null){
			return $this->_periodoDao->crear($periodo);
		}
		throw new BusinessLogicException("El periodo ya existe");
	}

	/**
	 * Metodo para editar un periodo
	 * @param Periodo $periodo
	 * @throws BusinessLogicException
	 * @return Periodo
	 */
	public function editarPeriodo(Periodo $periodo)
	{
		if($this->obtenerPeriodoPorId($periodo->getId()) != null){
			return $this->_periodoDao->editar($periodo);
		}
		throw new BusinessLogicException("El periodo que quiere editar no existe");
	}

	/**
	 * Metodo para borrar un periodo
	 * @param Periodo $periodo
	 * @throws BusinessLogicException
	 * @return Periodo
	 */
	public function borrarPeriodo(Periodo $periodo)
	{
		if($this->obtenerPeriodoPorId($periodo->getId()) != null){

			return $this->_periodoDao->borrar($periodo);
		}
		throw new BusinessLogicException("El periodo que quiere borrar no existe");
	}

	/**
	 * Metodo para obtener un periodo por el id
	 * @param unknown_type $id
	 * @return Ambigous <NULL, \Dominio\Clases\Periodo>
	 */
	public function obtenerPeriodoPorId($id)
	{
		return $this->_periodoDao->obtenerPeriodoPorId($id);
	}

	/**
	 * Metodo para comprobar si un usuario es due�o de un periodo
	 * @param unknown_type $email
	 * @return boolean
	 */
	public function comprobarSiUnPeriodoPerteneceAUnUsuario($idPeriodo, $email){
		return $this->_periodoDao->comprobarSiUnPeriodoPerteneceAUnUsuario($idPeriodo,$email);
	}

	/**
	 * Metodo para calcular el promedio de un periodo
	 * @param Periodo $periodo
	 * @throws BusinessLogicException
	 * @return number
	 */
	public function calcularPromedioFinalDeUnPeriodo(Periodo $periodo)
	{
		if($this->obtenerPeriodoPorId($periodo->getId()) != null){
			$listaAsignaturas = $this->obtenerListaDeAsignaturasDeUnPeriodo($periodo);
			$promedioFinal = 0;
			$numeroAsignaturas = count($listaAsignaturas);
			if($numeroAsignaturas>0){
				foreach ($listaAsignaturas as $asignatura){
					$promedioFinal = $promedioFinal +  $this->calcularNotaFinalDeUnaAsignatura($asignatura);
				}
				$promedioFinal = $promedioFinal / $numeroAsignaturas;
			}
			return number_format($promedioFinal, 4);
		}
		throw new BusinessLogicException("El periodo no existe");
	}

	/**
	 * Metodo para obtener la lista de periodos de un usuario
	 * @param Usuario $usuario
	 * @throws BusinessLogicException
	 * @return Ambigous <NULL, multitype:unknown >
	 */
	public function obtenerListaDePeriodosDeUnUsuario(Usuario $usuario)
	{
		$boUsuario = new BoUsuarios();
		if($boUsuario->obtenerUsuarioPorEmail($usuario->getEmail()) !=null){
			return $this->_periodoDao->obtenerListaDePeriodosDeUnUsuario($usuario);
		}
		throw new BusinessLogicException("El usuario no existe");
	}

	/**
	 * Metodo para crear un grupo de notas
	 * @param GrupoDeNotas $grupoDeNotas
	 * @throws BusinessLogicException
	 * @return GrupoDeNotas
	 */
	public function crearGrupoDeNotas(GrupoDeNotas $grupoDeNotas)
	{
		if($this->obtenerGrupoDeNotasPorId($grupoDeNotas->getId()) == null ){
			return $this->_grupoDeNotasDao->crear($grupoDeNotas);
		}
		throw new BusinessLogicException("El grupo de notas ya existe");
	}

	/**
	 * Metodo para editar un grupo de notas
	 * @param GrupoDeNotas $grupoDeNotas
	 * @throws BusinessLogicException
	 * @return GrupoDeNotas
	 */
	public function editarGrupoDeNotas(GrupoDeNotas $grupoDeNotas)
	{
		if($this->obtenerGrupoDeNotasPorId($grupoDeNotas->getId()) != null ){
			return $this->_grupoDeNotasDao->editar($grupoDeNotas);
		}
		throw new BusinessLogicException("El grupo de notas que quiere editar no existe");
	}

	/**
	 * Metodo para borrar un grupo de notas
	 * @param GrupoDeNotas $grupoDeNotas
	 * @throws BusinessLogicException
	 * @return GrupoDeNotas
	 */
	public function borrarGrupoDeNotas(GrupoDeNotas $grupoDeNotas)
	{
		if($this->obtenerGrupoDeNotasPorId($grupoDeNotas->getId()) != null ){
			return $this->_grupoDeNotasDao->borrar($grupoDeNotas);
		}
		throw new BusinessLogicException("El grupo de notas que quiere borrar no existe");
	}


	/**
	 * Metodo para obtener un grupo de notas por el id
	 * @param unknown_type $id
	 * @return Ambigous <NULL, \Dominio\Clases\GrupoDeNotas>
	 */
	public function obtenerGrupoDeNotasPorId($id)
	{
		return $this->_grupoDeNotasDao->obtenerGrupoPorId($id);
	}

	/**
	 * Metodo para obtener el grupo por defecto de una asignatura
	 * @param Asignatura $asignatura
	 * @throws BusinessLogicException
	 * @return Ambigous <NULL, \Dominio\Clases\GrupoDeNotas>
	 */
	private function obtenerGrupoDefectoDeUnaAsignatura(Asignatura $asignatura)
	{
		if($this->obtenerAsignaturaPorId($asignatura->getId()) != null ){
			return $this->_grupoDeNotasDao->obtenerGrupoDefectoDeUnaAsignatura($asignatura);
		}
		throw new BusinessLogicException("La asignatura no existe");

	}

	/**
	 * Metodo para calcular el promedio de cualquier grupo de notas
	 * @param GrupoDeNotas $grupoDeNotas
	 * @throws BusinessLogicException
	 * @return number
	 */
	public function calcularPromedioDeNotasDeUnGrupo(GrupoDeNotas $grupoDeNotas)
	{
		if($this->obtenerGrupoDeNotasPorId($grupoDeNotas->getId()) != null ){
			$listaNotas = $this->obtenerListaDeNotasDeUnGrupo($grupoDeNotas);
			$notaFinal  = 0;
			if(count($listaNotas)>0){
				if($grupoDeNotas->getPorcentajesIguales() == true){
					foreach ($listaNotas as $nota){
						$notaFinal = $notaFinal +  $nota->getValor();
					}
				}else{
					foreach ($listaNotas as $nota){
						$notaFinal = $notaFinal +  (($nota->getValor() /100) * $nota->getPorcentaje() );
					}
				}
				$notaFinal = $notaFinal / count($listaNotas);
			}
			return $notaFinal;
		}
		throw new BusinessLogicException("El grupo no existe");
	}

	public function obtenerListaDeGruposDeUnaAsignatura(Asignatura $asignatura)
	{
		if($this->obtenerAsignaturaPorId($asignatura->getId()) != null ){
			return $this->_grupoDeNotasDao->obtenerListaDeGruposDeUnaAsignatura($asignatura);
		}
		throw new BusinessLogicException("La asignatura no existe");
	}
}
