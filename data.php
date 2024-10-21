<?
/* ini_set('display_errors',1);
 ini_set('display_startup_errors',1);
error_reporting(-1); */

$dbConnection = new PDO('mysql:dbname=udlacademia;host=localhost;', 'ortseam', '139floz');
$dbConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

date_default_timezone_set('America/Mexico_City');
$mes = date("n");
$anio = date("Y");
$hoy = date("Y-m-d H:i:s");
include('../../privado/sync/sysKey.php');
require '../correosAPI/google-api-php-client/vendor/autoload.php';


$sysKey = sysToken();

$secureMode = '1';


function CallApi($method, $url, $data = false, $auth)
{
	// Method: POST, PUT, GET etc
	// Data: array("param" => "value") ==> index.php?param=value
	$curl = curl_init();

	switch ($method) {
		case "POST":
			curl_setopt($curl, CURLOPT_POST, 1);

			if ($data)
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			break;
		case "PUT":
			curl_setopt($curl, CURLOPT_PUT, 1);
			break;
		default:
			if ($data)
				$url = sprintf("%s?%s", $url, http_build_query($data));
	}

	// Optional Authentication:
	if ($auth == '1') {
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_USERPWD, "username:password");
	}

	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

	$result = curl_exec($curl);

	curl_close($curl);

	return $result;
}
/*INICIO DE FUNCIONES*/
function anios($clave)
{
	global $dbConnection;
	$q = "SELECT 
			YEAR ( CH_evaluacion_desempeno.fecha_alta ) AS anio
		FROM
			CH_evaluacion_desempeno
		WHERE
			CH_evaluacion_desempeno.clave_evaluada = '$clave'
			AND	CH_evaluacion_desempeno.estatus = 0 
			GROUP BY YEAR ( CH_evaluacion_desempeno.fecha_alta )
			ORDER BY fecha_alta DESC";

	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {
			$anio = $d['anio'];
		}
	}

	return $anio;
}

function guardar_evaluacion($hash_usuario, $tipo_evaluacion, $id_evaluado, $compromiso, $orientacion_cliente, $adaptabilidad_cambio, $comunicacion, $trabajo_equipo, $liderazgo, $efectividad_eficiencia, $integridad, $planeacion_organizacion, $innov_creatividad, $proactividad, $pulcritud, $dinamismo_energia, $puntualidad_asis, $ev_general, $abiertas, $clave_evaluada)
{ // $principales_logros, $sugerencias,
	global $dbConnection;
	$data = array();
	global $hoy, $anio;
	$q0 = "SELECT clave_registro FROM CH_base_ev_desempeno WHERE hash = '$hash_usuario'";
	$stmt = $dbConnection->prepare($q0);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		foreach ($stmt as $d) {
			$clave = $d['clave_registro'];


			$q0 = "SELECT clave FROM CH_evaluacion_desempeno WHERE id_evaluado = $id_evaluado AND hash = '$hash_usuario' AND estatus = 0 AND YEAR ( fecha_alta ) = $anio";
			$stmt = $dbConnection->prepare($q0);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$data['msj'] = '';
				$data['error'] = 'Ya has evaluado a este usuario anteriormente';
			} else {
				$q = "INSERT INTO CH_evaluacion_desempeno(clave, tipo_evaluacion, id_evaluado, compromiso, orientacion_cliente, adaptabilidad_cambio, comunicacion, trabajo_equipo, liderazgo, efectividad_eficiencia, integridad, planeacion_organizacion, usuario_alta, fecha_alta, innov_creatividad, proactividad, pulcritud, dinamismo_energia, puntualidad_asist, hash, ev_general, abiertas, clave_evaluada) 
			VALUES (:clave, :tipo_evaluacion, :id_evaluado, :compromiso, :orientacion_cliente, :adaptabilidad_cambio, :comunicacion, :trabajo_equipo, :liderazgo, :efectividad_eficiencia, :integridad, :planeacion_organizacion, :usuario_alta, :fecha_alta, :innov_creatividad, :proactividad, :pulcritud, :dinamismo_energia, :puntualidad_asis, :hash_usuario, :ev_general, :abiertas, :clave_evaluada)";
				$stmt = $dbConnection->prepare($q);
				$ejecucion = $stmt->execute(array(
					':clave' => $clave,
					':tipo_evaluacion' => $tipo_evaluacion,
					':id_evaluado' => $id_evaluado,
					':compromiso' => $compromiso,
					':orientacion_cliente' => $orientacion_cliente,
					':adaptabilidad_cambio' => $adaptabilidad_cambio,
					':comunicacion' => $comunicacion,
					':trabajo_equipo' => $trabajo_equipo,
					':liderazgo' => $liderazgo,
					':efectividad_eficiencia' => $efectividad_eficiencia,
					':integridad' => $integridad,
					':planeacion_organizacion' => $planeacion_organizacion,
					':usuario_alta' => $clave,
					':fecha_alta' => $hoy,
					':innov_creatividad' => $innov_creatividad,
					':proactividad' => $proactividad,
					':pulcritud' => $pulcritud,
					':dinamismo_energia' => $dinamismo_energia,
					':puntualidad_asis' => $puntualidad_asis,
					':hash_usuario' => $hash_usuario,
					':ev_general' => $ev_general,
					':abiertas' => $abiertas,
					':clave_evaluada' => $clave_evaluada
				));
				if ($ejecucion) {
					$data['msj'] = 'Guardado correctamente';
					$data['error'] = '';
				} else {
					$data['msj'] = '';
					$data['error'] = 'Error, intenta mas tarde. CodError:Act90';
				}
			}
		}
	} else {
	}
	return $data;
}

function esta_evaluado($id_evaluado, $hash_usuario)
{
	global $dbConnection, $anio;
	$data = array();
	$q = "SELECT clave FROM CH_evaluacion_desempeno WHERE clave_evaluada = $id_evaluado AND hash = '$hash_usuario' AND estatus = 0 AND YEAR ( fecha_alta ) = $anio";

	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$data = 'Evaluado';
	} else {
		$data = 'No evaluado';
	}

	return $data;
}

/* ================================================================================================= */
function foto_empleado($clave)
{
	global $dbConnection;
	$data = array();
	$q = "SELECT clave_registro FROM CH_base_ev_desempeno WHERE clave_registro = $clave";

	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		foreach ($stmt as $d) {
			$foto = $d['clave_registro'];


			if ($clave == '' || $clave == '0') {
				$res = 'user.png';
			} else {
				$path = $foto;
				$filename = "../../httpdocs/ch/images/fotos-empleados/$foto.jpg";
				$filename2 = "../../httpdocs/ch/images/fotos-empleados/$foto.jpeg";
				$filename3 = "../../httpdocs/ch/images/fotos-empleados/$foto.png";
				if (file_exists($filename)) {
					$res = "$path.jpg";
				} else {
					if (file_exists($filename2)) {
						$res = "$path.jpeg";
					} elseif (file_exists($filename3)) {
						$res = "$path.png";
					} else {
						$res = $res = 'user.png';
					}
				}
			}
		}
	}
	return $res;
}

function evaluacion_empleado($hash_usuario)
{
	global $dbConnection;
	$data = array();
	$supervisor = array();
	$empleado = array();

	$q = "SELECT
			CH_base_ev_desempeno.id,
			CH_base_ev_desempeno.clave_registro,
			CH_base_ev_desempeno.nombre_completo,
			CH_base_ev_desempeno.departamento,
			CH_base_ev_desempeno.puesto_actual,
			CH_base_ev_desempeno.supervisor,
			CH_base_ev_desempeno.nombre_evaluacion,
			CH_base_ev_desempeno.asignacion_evaluacion
		FROM
			CH_base_ev_desempeno
			WHERE
			CH_base_ev_desempeno.hash = '$hash_usuario' ";
	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {
			$id = $d['id'];
			$nombre_emp = utf8_encode($d['nombre_completo']);
			$nombre_puesto = utf8_encode($d['puesto_actual']);
			$nombre_depto = utf8_encode($d['departamento']);
			$nombre_pila = $d['nombre_evaluacion'];
			$asignacion_evaluacion = $d['asignacion_evaluacion'];
			$nombre_supervisor = rtrim($d['supervisor'], ' ');
			$nombre_empleado = rtrim($nombre_emp, ' ');
			$clave_registro = $d['clave_registro'];
			$foto = foto_empleado($clave_registro);
			$tipo_evaluacion = utf8_encode("Autoevaluación");

			if ($nombre_puesto == "JEFE") {
				$puesto = "$nombre_puesto DE $nombre_depto";
			} else {
				$puesto = $nombre_puesto;
			}
			$subordinados = subordinados($nombre_empleado, $hash_usuario);
			$esta_evaluado = esta_evaluado($clave_registro, $hash_usuario);

			if ($asignacion_evaluacion == "VIGILANCIA" || $asignacion_evaluacion == "INT Y MTTO") {
				$companeros = companeros($hash_usuario, $asignacion_evaluacion);
			} else {
				$companeros = '';
			}

			$empleado[$n]['id_emp'] = $id;
			$empleado[$n]['nombre_empleado'] = $nombre_empleado;
			$empleado[$n]['nombre_pila'] = $nombre_pila;
			$empleado[$n]['nombre_puesto'] = $puesto;
			$empleado[$n]['nombre_depto'] = $nombre_depto;
			$empleado[$n]['foto'] = $foto;
			$empleado[$n]['tipo_evaluacion'] = $tipo_evaluacion;
			$empleado[$n]['evaluado'] = $esta_evaluado;

			if ($nombre_supervisor <> '') {
				$supervisor[$n] = supervisor($nombre_supervisor, $hash_usuario);
				$data['supervisor'] = $supervisor;
			} else {
			}

			$n++;
		}
	}

	$data['empleado'] = $empleado;
	$data['subordinados'] = $subordinados;
	$data['companeros'] = $companeros;
	return $data;
}

function supervisor($nombre_supervisor, $hash_usuario)
{
	global $dbConnection;
	$supervisor = array();
	$q0 = "SELECT
			CH_base_ev_desempeno.id,
			CH_base_ev_desempeno.clave_registro,
			CH_base_ev_desempeno.nombre_completo,
			CH_base_ev_desempeno.departamento,
			CH_base_ev_desempeno.puesto_actual,
			CH_base_ev_desempeno.supervisor,
			CH_base_ev_desempeno.nombre_evaluacion
		FROM
			CH_base_ev_desempeno
			
		WHERE
			CH_base_ev_desempeno.nombre_completo LIKE '%$nombre_supervisor%' GROUP BY CH_base_ev_desempeno.clave_registro";
	$stmt = $dbConnection->prepare($q0);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {
			$id = $d['id'];
			$nombre_supervisor = $d['nombre_completo'];
			$puesto_supervisor = utf8_encode($d['puesto_actual']);
			$depto_supervisor = utf8_encode($d['departamento']);
			$clave_registro = $d['clave_registro'];
			$nombre_pila = $d['nombre_evaluacion'];
			$foto = foto_empleado($clave_registro);
			$tipo_evaluacion = "Jefe";
			if ($puesto_supervisor == "JEFE") {
				$puesto = "$puesto_supervisor DE $depto_supervisor";
			} else {
				$puesto = $puesto_supervisor;
			}

			$esta_evaluado = esta_evaluado($clave_registro, $hash_usuario);

			$supervisor['id_sup'] = $id;
			$supervisor['nombre_supervisor'] = $nombre_supervisor;
			$supervisor['nombre_pila'] = $nombre_pila;

			$supervisor['puesto_supervisor'] = $puesto;
			$supervisor['depto_supervisor'] = $depto_supervisor;
			$supervisor['foto'] = $foto;
			$supervisor['tipo_evaluacion'] = $tipo_evaluacion;
			$supervisor['evaluado'] = $esta_evaluado;
			$n++;
		}
	}
	return $supervisor;
}

function subordinados($nombre_empleado, $hash_usuario)
{
	global $dbConnection;
	$subordinados = array();
	$q = "SELECT
			CH_base_ev_desempeno.id,
			CH_base_ev_desempeno.clave_registro,
			CH_base_ev_desempeno.nombre_completo,
			CH_base_ev_desempeno.departamento,
			CH_base_ev_desempeno.puesto_actual,
			CH_base_ev_desempeno.supervisor,
			CH_base_ev_desempeno.nombre_evaluacion
		FROM
			CH_base_ev_desempeno
			
		WHERE
			CH_base_ev_desempeno.supervisor LIKE '%$nombre_empleado%'";
	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {
			$id = $d['id'];
			$nombre_subordinado = utf8_encode($d['nombre_completo']);
			$puesto_subordinado = utf8_encode($d['puesto_actual']);
			$depto_subordinado = utf8_encode($d['departamento']);
			$nombre_pila = $d['nombre_evaluacion'];
			$clave_registro = $d['clave_registro'];
			$foto = foto_empleado($clave_registro);
			$tipo_evaluacion = "Subordinado";

			$esta_evaluado = esta_evaluado($clave_registro, $hash_usuario);

			$subordinados[$n]['id_sub'] = $id;
			$subordinados[$n]['nombre_subordinado'] = $nombre_subordinado;
			$subordinados[$n]['nombre_pila'] = $nombre_pila;
			$subordinados[$n]['puesto_subordinado'] = $puesto_subordinado;
			$subordinados[$n]['depto_subordinado'] = $depto_subordinado;
			$subordinados[$n]['foto'] = $foto;
			$subordinados[$n]['tipo_evaluacion'] = $tipo_evaluacion;
			$subordinados[$n]['evaluado'] = $esta_evaluado;
			$n++;
		}
	}
	return $subordinados;
}

/*  */
function companeros($hash_usuario, $asignacion)
{
	global $dbConnection;
	$companeros = array();
	$q = "SELECT
			CH_base_ev_desempeno.id,
			CH_base_ev_desempeno.clave_registro,
			CH_base_ev_desempeno.nombre_completo,
			CH_base_ev_desempeno.departamento,
			CH_base_ev_desempeno.puesto_actual,
			CH_base_ev_desempeno.supervisor,
			CH_base_ev_desempeno.nombre_evaluacion
		FROM
			CH_base_ev_desempeno			
		WHERE
			CH_base_ev_desempeno.asignacion_evaluacion = '$asignacion' AND CH_base_ev_desempeno.hash != '$hash_usuario'";
	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {
			$id = $d['id'];
			$nombre_companero = utf8_encode($d['nombre_completo']);
			$puesto_companero = utf8_encode($d['puesto_actual']);
			$depto_companero = utf8_encode($d['departamento']);
			$nombre_pila = $d['nombre_evaluacion'];
			$clave_registro = $d['clave_registro'];
			$foto = foto_empleado($clave_registro);
			$tipo_evaluacion = "Companero";

			$esta_evaluado = esta_evaluado($clave_registro, $hash_usuario);

			$companeros[$n]['id_companero'] = $id;
			$companeros[$n]['nombre_companero'] = $nombre_companero;
			$companeros[$n]['nombre_pila'] = $nombre_pila;
			$companeros[$n]['puesto_companero'] = $puesto_companero;
			$companeros[$n]['depto_companero'] = $depto_companero;
			$companeros[$n]['foto'] = $foto;
			$companeros[$n]['tipo_evaluacion'] = $tipo_evaluacion;
			$companeros[$n]['evaluado'] = $esta_evaluado;
			$n++;
		}
	}
	return $companeros;
}

/* NUEVOS CAMBIOS */
function secciones_preguntas($id)
{
	global $dbConnection;
	$data = array();
	$secciones = array();
	$q = "SELECT nombre_completo, departamento, puesto_actual, asignacion_evaluacion FROM CH_base_ev_desempeno WHERE id = $id";
	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {
			$nombre_completo = utf8_encode($d['nombre_completo']);
			$puesto_actual = $d['puesto_actual'];
			$departamento = $d['departamento'];
			$asignacion = $d['asignacion_evaluacion'];
			$data['nombre'] = $nombre_completo;

			if ($asignacion == "DIR Y JEF") {
				$q = "SELECT id, seccion, descripcion, imagen FROM `CH_secciones_preguntas` WHERE jefe_director = 1";
			} elseif ($asignacion == "VIGILANCIA") {
				$q = "SELECT id, seccion, descripcion, imagen FROM `CH_secciones_preguntas` WHERE vigilancia = 1";
			} elseif ($asignacion == "INT Y MTTO") {
				$q = "SELECT id, seccion, descripcion, imagen FROM `CH_secciones_preguntas` WHERE int_mtto = 1";
			} else {
				$q = "SELECT id, seccion, descripcion, imagen FROM `CH_secciones_preguntas` WHERE admvos = 1";
			}

			$stmt = $dbConnection->prepare($q);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$n = 0;
				foreach ($stmt as $d) {
					$id = $d['id'];
					$seccion = utf8_encode($d['seccion']);
					$descripcion = utf8_encode($d['descripcion']);
					$imagen = $d['imagen'];
					$preguntas = lista_preguntas($id, $asignacion);
					$data[$n]['id_seccion'] = $id;
					$data[$n]['seccion'] = $seccion;
					$data[$n]['descripcion'] = $descripcion;
					$data[$n]['imagen'] = $imagen;
					$data[$n]['preguntas'] = $preguntas;
					$n++;
				}
			}
		}
	}
	return $data;
}

function lista_preguntas($id, $asignacion)
{
	global $dbConnection;
	$preguntas = array();

	if ($asignacion == "DIR Y JEF") {
		$qE = "AND CH_preguntas_ev_desempeno.jefes_director = 1";
	} elseif ($asignacion == "VIGILANCIA") {
		$qE = "AND CH_preguntas_ev_desempeno.vigilancia = 1";
	} elseif ($asignacion == "INT Y MTTO") {
		$qE = "AND CH_preguntas_ev_desempeno.int_mtto = 1";
	} else {
		$qE = "AND CH_preguntas_ev_desempeno.admvos = 1";
	}

	$q = "SELECT
			CH_preguntas_ev_desempeno.id,
			CH_preguntas_ev_desempeno.pregunta,
			escalas_ini.escala AS escala_ini,
			escalas_fin.escala AS escala_fin 
		FROM
			CH_preguntas_ev_desempeno
			LEFT JOIN CH_ev_escalas AS escalas_ini ON CH_preguntas_ev_desempeno.escala_ini = escalas_ini.id
			LEFT JOIN CH_ev_escalas AS escalas_fin ON CH_preguntas_ev_desempeno.escala_fin = escalas_fin.id 
		WHERE
			CH_preguntas_ev_desempeno.seccion = $id $qE";

	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {
			$id = $d['id'];
			$pregunta = utf8_encode($d['pregunta']);
			$escala_ini = utf8_encode($d['escala_ini']);
			$escala_fin = utf8_encode($d['escala_fin']);

			$preguntas[$n]['id_pregunta'] = $id;
			$preguntas[$n]['pregunta'] = $pregunta;
			$preguntas[$n]['escala_ini'] = $escala_ini;
			$preguntas[$n]['escala_fin'] = $escala_fin;
			$n++;
		}
	}
	return $preguntas;
}

function lista_preguntas_nueva($id, $asignacion)
{
	global $dbConnection;
	$preguntas = array();

	if ($asignacion == "DIR Y JEF") {
		$qE = "AND CH_preguntas_ev_desempeno_nueva.jefes_director = 1";
	} elseif ($asignacion == "VIGILANCIA") {
		$qE = "AND CH_preguntas_ev_desempeno_nueva.vigilancia = 1";
	} elseif ($asignacion == "INT Y MTTO") {
		$qE = "AND CH_preguntas_ev_desempeno_nueva.int_mtto = 1";
	} else {
		$qE = "AND CH_preguntas_ev_desempeno_nueva.admvos = 1";
	}

	$q = "SELECT
			CH_preguntas_ev_desempeno_nueva.id,
			CH_preguntas_ev_desempeno_nueva.pregunta,
			escalas_ini.escala AS escala_ini,
			escalas_fin.escala AS escala_fin,
			CH_preguntas_ev_desempeno_nueva.tipo
		FROM
			CH_preguntas_ev_desempeno_nueva
			LEFT JOIN CH_ev_escalas AS escalas_ini ON CH_preguntas_ev_desempeno_nueva.escala_ini = escalas_ini.id
			LEFT JOIN CH_ev_escalas AS escalas_fin ON CH_preguntas_ev_desempeno_nueva.escala_fin = escalas_fin.id 
		WHERE
			CH_preguntas_ev_desempeno_nueva.seccion = $id $qE ORDER BY CH_preguntas_ev_desempeno_nueva.tipo ASC";

	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {
			$id = $d['id'];
			$pregunta = utf8_encode($d['pregunta']);
			$escala_ini = utf8_encode($d['escala_ini']);
			$escala_fin = utf8_encode($d['escala_fin']);
			$tipo = utf8_encode($d['tipo']);
			$preguntas[$n]['id_pregunta'] = $id;
			$preguntas[$n]['pregunta'] = $pregunta;
			$preguntas[$n]['escala_ini'] = $escala_ini;
			$preguntas[$n]['escala_fin'] = $escala_fin;
			$preguntas[$n]['tipo'] = $tipo;
			$n++;
		}
	}

	return $preguntas;
}

function secciones_preguntas_nueva($id)
{
	global $dbConnection;
	$data = array();
	$secciones = array();
	$q = "SELECT nombre_completo, departamento, puesto_actual, asignacion_evaluacion FROM CH_base_ev_desempeno WHERE id = $id";
	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {
			$nombre_completo = utf8_encode($d['nombre_completo']);
			$puesto_actual = $d['puesto_actual'];
			$departamento = $d['departamento'];
			$asignacion = $d['asignacion_evaluacion'];
			$data['nombre'] = $nombre_completo;

			if ($asignacion == "DIR Y JEF") {
				$q = "SELECT id, seccion, descripcion, imagen, color FROM `CH_secciones_preguntas` WHERE jefe_director = 1";
			} elseif ($asignacion == "VIGILANCIA") {
				$q = "SELECT id, seccion, descripcion, imagen, color FROM `CH_secciones_preguntas` WHERE vigilancia = 1";
			} elseif ($asignacion == "INT Y MTTO") {
				$q = "SELECT id, seccion, descripcion, imagen, color FROM `CH_secciones_preguntas` WHERE int_mtto = 1";
			} else {
				$q = "SELECT id, seccion, descripcion, imagen, color FROM `CH_secciones_preguntas` WHERE admvos = 1";
			}

			$stmt = $dbConnection->prepare($q);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$n = 0;
				foreach ($stmt as $d) {
					$id = $d['id'];
					$seccion = utf8_encode($d['seccion']);
					$descripcion = utf8_encode($d['descripcion']);
					$imagen = $d['imagen'];
					$color = $d['color'];
					$preguntas = lista_preguntas_nueva($id, $asignacion);
					$data[$n]['id_seccion'] = $id;
					$data[$n]['seccion'] = $seccion;
					$data[$n]['descripcion'] = $descripcion;
					$data[$n]['imagen'] = $imagen;
					$data[$n]['preguntas'] = $preguntas;
					$data[$n]['color'] = $color;
					$n++;
				}
			}
		}
	}
	return $data;
}

function imagen_cabecera($id)
{
	global $dbConnection;
	$data = array();
	$secciones = array();
	$q = "SELECT
			CH_base_ev_desempeno.asignacion_evaluacion,
			CH_cabecera_ev_desempeno.texto,
			CH_cabecera_ev_desempeno.imagen_cabecera 
		FROM
			CH_base_ev_desempeno
			INNER JOIN CH_cabecera_ev_desempeno ON CH_base_ev_desempeno.asignacion_evaluacion = CH_cabecera_ev_desempeno.asignacion_evaluacion 
		WHERE
			CH_base_ev_desempeno.id = $id";
	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {

			$texto = utf8_encode($d['texto']);
			$imagen_cabecera = $d['imagen_cabecera'];

			$data[$n]['texto'] = $texto;
			$data[$n]['imagen_cabecera'] = $imagen_cabecera;
			$n++;
		}
	}
	return $data;
}

/* ESTADÍSTICAS */

function list_empleados()
{
	global $dbConnection;
	$data = array();
	$data = array();
	$q = "SELECT
			MAX( CH_base_ev_desempeno.id ) AS id,
			CH_base_ev_desempeno.clave_registro,
			CH_base_ev_desempeno.nombre_completo,
			CH_base_ev_desempeno.departamento,
			CH_base_ev_desempeno.puesto_actual, 
			CH_base_ev_desempeno.asignacion_evaluacion 
		FROM
			CH_base_ev_desempeno 
		GROUP BY
			CH_base_ev_desempeno.clave_registro 
		ORDER BY
			CH_base_ev_desempeno.nombre_completo ASC ";
	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {
			$id = $d['id'];
			$clave = $d['clave_registro'];
			$nombre_completo = utf8_encode($d['nombre_completo']);
			$departamento = utf8_encode($d['departamento']);
			$puesto_actual = utf8_encode($d['puesto_actual']);
			$asignacion = $d['asignacion_evaluacion'];
			$asignacion_evaluacion = obtenerPrimeraPalabra($asignacion);
			$foto = foto_empleado($clave);

			$data[$n]['id'] = $id;
			$data[$n]['clave'] = $clave;
			$data[$n]['nombre_completo'] = $nombre_completo;
			$data[$n]['departamento'] = $departamento;
			$data[$n]['puesto'] = $puesto_actual;
			$data[$n]['foto'] = $foto;
			$data[$n]['asignacion_evaluacion'] = $asignacion_evaluacion;
			$n++;
		}
	}
	return $data;
}
function calcularPromedio($array)
{
	// Si no es un array y es un número, devuélvelo directamente
	if (!is_array($array)) {
		if (is_numeric($array)) {
			return floatval($array);
		}
		return null; // Si no es un número, devuelve null
	}

	// Si es un array, procede con el cálculo del promedio
	$sum = 0;
	$count = 0;

	foreach ($array as $element) {
		$valor = floatval($element);
		if ($valor != 0) {
			$sum += $valor;
			$count++;
		}
	}

	if ($count > 0) {
		return $sum / $count; // Devuelve el promedio
	} else {
		return 0; // Si no hay elementos válidos, devuelve 0
	}
}

function suma_secciones($id, $tipo_evaluacion, $anio)
{
	global $dbConnection;
	$data = array();

	$q = "SELECT
			CH_evaluacion_desempeno.id,
			CH_evaluacion_desempeno.clave,
			CH_evaluacion_desempeno.tipo_evaluacion,
			CH_evaluacion_desempeno.compromiso,
			CH_evaluacion_desempeno.orientacion_cliente,
			CH_evaluacion_desempeno.adaptabilidad_cambio,
			CH_evaluacion_desempeno.comunicacion,
			CH_evaluacion_desempeno.trabajo_equipo,
			CH_evaluacion_desempeno.liderazgo,
			CH_evaluacion_desempeno.efectividad_eficiencia,
			CH_evaluacion_desempeno.integridad,
			CH_evaluacion_desempeno.planeacion_organizacion,
			CH_evaluacion_desempeno.pulcritud,
			CH_evaluacion_desempeno.dinamismo_energia,
			CH_evaluacion_desempeno.puntualidad_asist,
			CH_evaluacion_desempeno.ev_general,
			YEAR ( CH_evaluacion_desempeno.fecha_alta ) AS anio,
			CH_base_ev_desempeno.asignacion_evaluacion
		FROM
			CH_evaluacion_desempeno
			INNER JOIN CH_base_ev_desempeno ON CH_evaluacion_desempeno.clave_evaluada = CH_base_ev_desempeno.clave_registro
		WHERE
			CH_evaluacion_desempeno.clave_evaluada =:id
			AND CH_evaluacion_desempeno.estatus = 0 
			AND YEAR ( CH_evaluacion_desempeno.fecha_alta ) =:anio AND CH_evaluacion_desempeno.tipo_evaluacion =:tipo_evaluacion GROUP BY CH_evaluacion_desempeno.clave";

	$stmt = $dbConnection->prepare($q);
	$stmt->execute(array(':id' => $id, ':anio' => $anio, ':tipo_evaluacion' => $tipo_evaluacion));
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {
			$id = $d['id'];
			$tipo_evaluacion = utf8_decode($d['tipo_evaluacion']);
			$matricula = $d['clave'];

			$ev_general = $d['ev_general'];
			$anio = $d['anio'];

			if ($tipo_evaluacion == "Subordinado") {
				$tipo = "Jefe";
				$asignacion = ' (Jefe)';
			} elseif ($tipo_evaluacion == "Jefe") {
				$tipo = "Subordinado";
				$asignacion = '';
			} else {
				$tipo = $tipo_evaluacion;
				$asignacion = '';
			}
			$evaluador = nombre_evaluador($matricula);
			if ($tipo_evaluacion == "Subordinado") {
				$tipo = "Jefe";
				$asignacion = ' (Jefe)';
			} elseif ($tipo_evaluacion == "Jefe") {
				$tipo = "Subordinado";
				$asignacion = '';
			} else {
				$tipo = $tipo_evaluacion;
				$asignacion = '';
			}
			$campos = [
				'compromiso',
				'orientacion_cliente',
				'adaptabilidad_cambio',
				'comunicacion',
				'trabajo_equipo',
				'liderazgo',
				'efectividad_eficiencia',
				'integridad',
				'planeacion_organizacion',
				'pulcritud',
				'dinamismo_energia',
				'puntualidad_asist',
				'ev_general'
			];

			$sumas = [];
			$conteos = [];
			foreach ($campos as $campo) {
				$valor = json_decode($d[$campo]);
				$promedio = calcularPromedio($valor);
				$index = array_search($campo, $campos) + 1;

				if ($promedio !== null) {
					$sumas[$index] = isset($sumas[$index]) ? $sumas[$index] + $promedio : $promedio;
					$conteos[$index] = isset($conteos[$index]) ? $conteos[$index] + ($promedio != 0 ? 1 : 0) : ($promedio != 0 ? 1 : 0);
				}
			}

			$total_sum = array_sum($sumas);
			$total_count = array_sum($conteos);
			$promedio_individual = $total_count > 0 ? $total_sum / $total_count : 0;

			$data[$n] = [
				'id' => $id,
				'nombre_evaluador' => $evaluador . $asignacion,
				'quien_me_evaluo' => $tipo,
				'anio' => $anio,
				'promedio_individual' => number_format($promedio_individual, 2) // Formateo aquí
			];

			// Asegurarse de que la clave [15] sea tratada correctamente
			foreach ($sumas as $index => $suma) {
				// Ajuste para claves específicas
				if ($index >= "10") {
					$index += 2;
				}

				$data[$n][(string)$index] = number_format($suma, 2); // Formateo aquí

			}

			// Si la clave [15] ya tiene un valor asignado, se mantiene, de lo contrario, se mantiene '0'
			$data[$n]['15'] = ($ev_general == '' || $ev_general == null) ? '0' : $ev_general;
			$n++;
		}
		// Calcular promedio general por tipo de evaluador
		$promedio_total = [];
		foreach ($data as $evaluacion) {
			foreach ($campos as $index => $campo) {
				$i = (string)($index + 1);
				if (isset($evaluacion[$i]) && $evaluacion[$i] != 0) {
					if (!isset($promedio_total[$i])) {
						$promedio_total[$i] = ['sum' => 0, 'count' => 0];
					}
					$promedio_total[$i]['sum'] += $evaluacion[$i];
					$promedio_total[$i]['count']++;
				}
			}
		}

		$total = 0;
		$count = 0;

		foreach ($data as $item) {
			if ($item['promedio_individual'] != 0) {
				$total += floatval($item['promedio_individual']); // Asegurar suma de valores numéricos
				$count++;
			}
		}

		$promedio_total_subordinados = $count ? $total / $count : 0;

		if ($tipo_evaluacion == 'Jefe') {
			$t = 'promedio_total_subordinados';
		} elseif ($tipo_evaluacion == 'Subordinado') {
			$t = 'promedio_total_jefe';
		} else {
			$t = 'promedio_autoev';
		}

		$data[$t] = number_format($promedio_total_subordinados, 2); // Formateo aquí

	}
	return $data;
}

function detalle_evaluacion($id)
{
	global $dbConnection;
	$data = array();

	// Consulta única para obtener todos los datos necesarios
	$q = "SELECT 
			YEAR ( CH_evaluacion_desempeno.fecha_alta ) AS anio
		FROM
			CH_evaluacion_desempeno
		WHERE
			CH_evaluacion_desempeno.clave_evaluada =:id
			AND	CH_evaluacion_desempeno.estatus = 0 
			GROUP BY YEAR ( CH_evaluacion_desempeno.fecha_alta )
			ORDER BY fecha_alta DESC";

	$stmt = $dbConnection->prepare($q);
	$stmt->bindParam(':id', $id, PDO::PARAM_STR);
	$stmt->execute();

	if ($stmt->rowCount() > 0) {
		$defaultFactors = [
			'empty_j' => ['sobre_5_Auto' => 0.4, 'sobre_5_subor' => 0.6, 'sobre_5_jefe' => ''],
			'empty_s' => ['sobre_5_Auto' => 0.4, 'sobre_5_jefe' => 0.6,	'sobre_5_subor' => ''],

			'empty_solo_auto' => ['sobre_5_Auto' => 1.0, 'sobre_5_subor' => '', 'sobre_5_jefe' => ''],
			'empty_solo_jefe' => ['sobre_5_Auto' => '', 'sobre_5_subor' => '', 'sobre_5_jefe' => 1.0],
			'empty_solo_subor' => ['sobre_5_Auto' => '', 'sobre_5_subor' => 1.0, 'sobre_5_jefe' => ''],
			'default' => ['sobre_5_Auto' => 0.3, 'sobre_5_subor' => 0.3, 'sobre_5_jefe' => 0.4]
		];

		foreach ($stmt as $n => $d) {
			$anio = $d['anio'];
			$data[$n]['anio'] = $anio;

			// Ejecutar sumas y consultas necesarias
			$s = suma_secciones($id, 'Jefe', $anio);
			$j = suma_secciones($id, 'Subordinado', $anio);
			$a = suma_secciones($id, utf8_encode('Autoevaluación'), $anio);
			$pa = preguntas_abiertas_empleado($id);

			$j1	= reemplazarCeros(suma_generales($id, $anio, 'Subordinado'));
			$a1 = reemplazarCeros(suma_generales($id, $anio, utf8_encode('Autoevaluación')));
			$s1 = reemplazarCeros(suma_generales($id, $anio, 'Jefe'));

			// Determinar los factores con base en la disponibilidad de datos
			//$factors = empty($j) ? $defaultFactors['empty_j'] : (empty($s) ? $defaultFactors['empty_s'] : $defaultFactors['default']);

			if (empty($s) && empty($j)) {
				$factors = $defaultFactors['empty_solo_auto'];
			} elseif (empty($a) && empty($s)) {
				$factors = $defaultFactors['empty_solo_jefe'];
			} elseif (empty($a) && empty($j)) {
				$factors = $defaultFactors['empty_solo_subor'];
			} elseif (empty($j)) {
				$factors = $defaultFactors['empty_j'];
			} elseif (empty($s)) {
				$factors = $defaultFactors['empty_s'];
			} else {
				$factors = $defaultFactors['default'];
			}

			// Multiplicar arreglos según factores calculados
			$j1 = multiplicarArreglo($j1, $factors['sobre_5_jefe']);
			$s1 = multiplicarArreglo($s1, $factors['sobre_5_subor']);
			$a1 = multiplicarArreglo($a1, $factors['sobre_5_Auto']);

			// Sumar arreglos y almacenar los resultados
			$g = sumarArreglos([$j1, $a1, $s1]);

			$data[$n]['estadistica_jefe'] = $j;
			$data[$n]['estadistica_subordinado'] = $s;
			$data[$n]['estadistica_autoev'] = $a;
			$data[$n]['estadistica_general'] = $g;
			$data[$n]['preguntas_abiertas'] = $pa;
			$n++;
		}
	}

	return $data;
}
function reemplazarCeros($arreglo)
{
	// Verificar que $arreglo sea un array válido
	if (!is_array($arreglo)) {
		return []; // Retornar un array vacío si no es un array válido
	}

	foreach ($arreglo as $key => $value) {
		// Si el valor es 0, lo reemplazamos con 5
		if ($value === null) {
			$arreglo[$key] = 5;
		}
	}

	return $arreglo;
}
function sumarArreglos($arreglos)
{
	$resultado = [];
	$totalCount = 0; // Contador de arreglos válidos

	foreach ($arreglos as $arreglo) {
		// Verificar que la variable esté definida y sea un array válido
		if (isset($arreglo) && is_array($arreglo)) {
			$totalCount++; // Incrementar el contador de arreglos válidos

			foreach ($arreglo as $key => $value) {
				// Si el valor no es numérico, lo ignoramos
				if (!is_numeric($value)) {
					continue;
				}
				// Si la clave no existe, la inicializamos a 0
				if (!isset($resultado[$key])) {
					$resultado[$key] = 0;
				}
				// Sumamos el valor actual
				$resultado[$key] += number_format($value, 2);
			}
		}
	}

	// Calculamos el promedio si existe la clave 'promedio_individual'
	if (isset($resultado['promedio_individual']) && $totalCount > 0) {
		$resultado['promedio_individual'] /= $totalCount;
	}

	return $resultado;
}
function multiplicarArreglo($arreglo, $factor)
{
	$resultado = [];

	// Verificar que $arreglo sea un array válido
	if (!is_array($arreglo)) {
		return $resultado; // Retornar un arreglo vacío si no es un arreglo válido
	}

	foreach ($arreglo as $key => $value) {
		// Si el valor es numérico y distinto de 0 y null, lo multiplicamos por el factor
		if (is_numeric($value) && $value != 0) {
			$resultado[$key] = $value * $factor;
		} else {
			// Si es 0, null o no es numérico, lo dejamos igual
			$resultado[$key] = $value;
		}
	}

	return $resultado;
}

function suma_generales($id, $anio, $tipo_evaluacion)
{
	global $dbConnection;
	$data = array();
	$promedio = array();
	if ($tipo_evaluacion == '') {
		$te = '';
	} else {
		$te = "AND CH_evaluacion_desempeno.tipo_evaluacion = '$tipo_evaluacion'";
	}
	$q = "SELECT
			CH_evaluacion_desempeno.id,
			CH_evaluacion_desempeno.clave,
			CH_evaluacion_desempeno.tipo_evaluacion,
			CH_evaluacion_desempeno.compromiso,
			CH_evaluacion_desempeno.orientacion_cliente,
			CH_evaluacion_desempeno.adaptabilidad_cambio,
			CH_evaluacion_desempeno.comunicacion,
			CH_evaluacion_desempeno.trabajo_equipo,
			CH_evaluacion_desempeno.liderazgo,
			CH_evaluacion_desempeno.efectividad_eficiencia,
			CH_evaluacion_desempeno.integridad,
			CH_evaluacion_desempeno.planeacion_organizacion,
			CH_evaluacion_desempeno.pulcritud,
			CH_evaluacion_desempeno.dinamismo_energia,
			CH_evaluacion_desempeno.puntualidad_asist,
			CH_evaluacion_desempeno.ev_general,
			YEAR ( CH_evaluacion_desempeno.fecha_alta ) AS anio,
			CH_base_ev_desempeno.asignacion_evaluacion
		FROM
			CH_evaluacion_desempeno
			INNER JOIN CH_base_ev_desempeno ON CH_evaluacion_desempeno.clave_evaluada = CH_base_ev_desempeno.clave_registro
		WHERE
			CH_evaluacion_desempeno.clave_evaluada =:id
			AND CH_evaluacion_desempeno.estatus = 0 AND YEAR ( CH_evaluacion_desempeno.fecha_alta ) =:anio $te GROUP BY CH_evaluacion_desempeno.clave";

	$stmt = $dbConnection->prepare($q);
	$stmt->execute(array(':id' => $id, ':anio' => $anio));
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {
			$id = $d['id'];
			$asignacion_evaluacion = $d['asignacion_evaluacion'];
			$matricula = $d['clave'];
			$compromiso = $d['compromiso'];
			$orientacion_cliente = $d['orientacion_cliente'];
			$adaptabilidad_cambio = $d['adaptabilidad_cambio'];
			$comunicacion = $d['comunicacion'];
			$trabajo_equipo = $d['trabajo_equipo'];
			$liderazgo = $d['liderazgo'];
			$efectividad_eficiencia = $d['efectividad_eficiencia'];
			$integridad = $d['integridad'];
			$planeacion_organizacion = $d['planeacion_organizacion'];
			$pulcritud = $d['pulcritud'];
			$dinamismo_energia = $d['dinamismo_energia'];
			$puntualidad_asist = $d['puntualidad_asist'];
			$ev_general = $d['ev_general'];
			$anio = $d['anio'];

			if ($ev_general == '') {
				$ev_general = '0';
			}

			if ($asignacion_evaluacion == 'DIR Y JEF') {
				$pulcritud = 0;
				$dinamismo_energia = 0;
				$puntualidad_asist = 0;
			} else {
				if ($asignacion_evaluacion == 'ADMVOS') {
					$liderazgo = 0;
					$pulcritud = 0;
					$dinamismo_energia = 0;
					$puntualidad_asist = 0;
				} elseif ($asignacion_evaluacion == 'INT Y MTTO' || $asignacion_evaluacion == 'VIGILANCIA') {
					$liderazgo = 0;
					$planeacion_organizacion = 0;
				}
			}

			$sum_c = calcularPromedio(json_decode($compromiso));
			$sum_oc = calcularPromedio(json_decode($orientacion_cliente));
			$sum_ac = calcularPromedio(json_decode($adaptabilidad_cambio));
			$sum_com = calcularPromedio(json_decode($comunicacion));
			$sum_te = calcularPromedio(json_decode($trabajo_equipo));
			$sum_li = calcularPromedio(json_decode($liderazgo));
			$sum_ee = calcularPromedio(json_decode($efectividad_eficiencia));
			$sum_i = calcularPromedio(json_decode($integridad));
			$sum_po = calcularPromedio(json_decode($planeacion_organizacion));
			$sum_pul = calcularPromedio(json_decode($pulcritud));
			$sum_din = calcularPromedio(json_decode($dinamismo_energia));
			$sum_pa = calcularPromedio(json_decode($puntualidad_asist));

			$evaluador = nombre_evaluador($matricula);
			if ($sum_pul == 0 && $sum_din == 0 && $sum_pa == 0) {
				$sum_pul = $sum_din = $sum_pa = null;
			} elseif ($sum_din == 0) {
				$sum_din = null;
			} elseif ($sum_li == 0) {
				$sum_li = null;
			} elseif ($sum_po == 0) {
				$sum_po = null;
			}

			$data[$n]['nombre_evaluador'] = $evaluador;
			$data[$n]['1'] = $sum_c;
			$data[$n]['2'] = $sum_oc;
			$data[$n]['3'] = $sum_ac;
			$data[$n]['4'] = $sum_com;
			$data[$n]['5'] = $sum_te;
			$data[$n]['6'] = $sum_li;
			$data[$n]['7'] = $sum_ee;
			$data[$n]['8'] = $sum_i;
			$data[$n]['9'] = $sum_po;
			$data[$n]['12'] = $sum_pul;
			$data[$n]['13'] = $sum_din;
			$data[$n]['14'] = $sum_pa;
			$data[$n]['15'] = $ev_general;

			$n++;
		}

		foreach (range(1, 15) as $key) {
			if ($key == 10 || $key == 11) continue;
			$promedio[$key] = promedio_total_deptos($data, (string)$key);
		}

		if ($asignacion_evaluacion == 'DIR Y JEF') {
			unset($promedio['12']);
			unset($promedio['13']);
			unset($promedio['14']);
		} elseif ($asignacion_evaluacion == 'ADMVOS') {
			unset($promedio['6']);
			unset($promedio['12']);
			unset($promedio['13']);
			unset($promedio['14']);
		} elseif ($asignacion_evaluacion == 'VIGILANCIA') {
			unset($promedio['6']);
			unset($promedio['9']);
			unset($promedio['13']);
		} elseif ($asignacion_evaluacion == 'INT Y MTTO') {
			unset($promedio['6']);
			unset($promedio['9']);
		}
		// Reemplazar 0 con null y eliminar nulls
		foreach ($promedio as $key => $value) {
			$filtered_array[$key] = ($value != 0) ? $value : null;
		}

		$promedios_sin_cero = array_filter($filtered_array, function ($val) {
			return $val !== null;
		});

		// Calcular el promedio sin ceros
		$num_elementos_sin_cero = count($promedios_sin_cero);
		$suma_sin_cero = array_sum($promedios_sin_cero);

		if ($num_elementos_sin_cero > 0) {
			$promedio_sin_cero = $suma_sin_cero / $num_elementos_sin_cero;
			$promedio['promedio_individual'] = number_format($promedio_sin_cero, 2);
		} else {
			$promedio['promedio_individual'] = null;
		}

		return $promedio;
	}

	return null;
}

function promedio_evaluacion($id)
{
	global $dbConnection;
	$data = array();
	$q = "SELECT YEAR
			( CH_evaluacion_desempeno.fecha_alta ) AS anio,
			CH_base_ev_desempeno.asignacion_evaluacion 
		FROM
			CH_evaluacion_desempeno
			INNER JOIN CH_base_ev_desempeno ON CH_evaluacion_desempeno.clave_evaluada = CH_base_ev_desempeno.clave_registro
		WHERE
			CH_evaluacion_desempeno.clave_evaluada = $id 
			AND CH_evaluacion_desempeno.estatus = 0 
		GROUP BY
			YEAR ( CH_evaluacion_desempeno.fecha_alta ) 
		ORDER BY
			fecha_alta DESC";

	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {

			$anio = $d['anio'];
			$asignacion_evaluacion = $d['asignacion_evaluacion'];
			$data[$n]['anio'] = $anio;

			$s = suma_secciones($id, 'Jefe', $anio);
			$j = suma_secciones($id, 'Subordinado', $anio);
			$a = suma_secciones($id, utf8_encode('Autoevaluación'), $anio);

			//Ponderación 
			if ($asignacion_evaluacion == 'DIR Y JEF') {
				$ponderacion_jefe = 40;
				$ponderacion_subor = 30;
				$ponderacion_autoev = 30;
			} else {
				$ponderacion_jefe = 60;
				$ponderacion_autoev = 40;
			}

			//if($asignacion_evaluacion == 'DIR Y JEF'){			
			if (isset($j['promedio_total_jefe'])) {
				$sobre_5 = $j['promedio_total_jefe'];
				$porcentaje_jefe = ($j['promedio_total_jefe'] * $ponderacion_jefe) / 5;
				$data[$n]['promedio_jefe'] = $j['promedio_total_jefe']; //"$porcentaje_jefe / $ponderacion_jefe%";	
			} else {
			}

			//} else{}
			if (isset($s['promedio_total_subordinados'])) {
				$sobre_5_subor = $s['promedio_total_subordinados'];
				$porcentaje_subor = ($s['promedio_total_subordinados'] * $ponderacion_subor) / 5;
				//$sobre_5_subor = number_format($sobre_5_subor, 2);
				$data[$n]['promedio_subordinado'] = $s['promedio_total_subordinados']; //"$porcentaje_subor / $ponderacion_subor%";
			}

			if (isset($a['promedio_autoev'])) {
				$sobre_5_Auto = $a['promedio_autoev'];
				$porcentaje_autoev = ($a['promedio_autoev'] * $ponderacion_autoev) / 5;
				//$sobre_5_Auto = number_format($sobre_5_Auto, 2);

				$data[$n]['promedio_autoevaluacion'] = $a['promedio_autoev']; //"$porcentaje_autoev / $ponderacion_autoev%";	
			}

			if ($asignacion_evaluacion == 'DIR Y JEF') {
				if (empty($j['promedio_total_jefe'])) {
					$sobre_5_Auto = $a['promedio_autoev'] * 0.4;
					$sobre_5_subor = $s['promedio_total_subordinados'] * 0.6;
					$sobre_5_total = ($sobre_5_Auto + $sobre_5_subor);
				} elseif (empty($s['promedio_total_subordinados'])) {
					$sobre_5_Auto = $a['promedio_autoev'] * 0.4;
					$sobre_5 = $j['promedio_total_jefe'] * 0.6;
					$sobre_5_total = ($sobre_5_Auto + $sobre_5);
				} elseif (empty($s['promedio_autoev'])) {
					$sobre_5_subor = $s['promedio_total_subordinados'] * 0.4;
					$sobre_5 = $j['promedio_total_jefe'] * 0.6;
					$sobre_5_total = ($sobre_5_subor + $sobre_5);
				} else {
					$sobre_5_Auto = $a['promedio_autoev'] * 0.3;
					$sobre_5_subor = $s['promedio_total_subordinados'] * 0.3;
					$sobre_5 = $j['promedio_total_jefe'] * 0.4;
					$sobre_5_total = ($sobre_5_Auto + $sobre_5_subor + $sobre_5);
				}
			} else {
				if (empty($j['promedio_total_jefe'])) {
					$sobre_5_Auto = $a['promedio_autoev'];
					$sobre_5_total = $sobre_5_Auto;
				} elseif (empty($s['promedio_autoev'])) {
					$sobre_5 = $j['promedio_total_jefe'];
					$sobre_5_total = ($sobre_5);
				} else {
					$sobre_5 = $j['promedio_total_jefe'] * 0.6;
					$sobre_5_Auto = $a['promedio_autoev'] * 0.4;
					$sobre_5_total = ($sobre_5_Auto + $sobre_5);
				}
			}

			$sobre_5_total = number_format($sobre_5_total, 2);

			$data[$n]['porcentaje_total'] = $sobre_5_total;
			$n++;
		}
	}
	return $data;
}

function nombre_evaluador($matricula)
{
	global $dbConnection;
	$data = array();

	$q = "SELECT
			CH_base_ev_desempeno.nombre_evaluacion, CH_base_ev_desempeno.nombre_completo, CH_base_ev_desempeno.nombre_grafica
		FROM
			CH_evaluacion_desempeno
			INNER JOIN CH_base_ev_desempeno ON CH_evaluacion_desempeno.clave = CH_base_ev_desempeno.clave_registro 
		WHERE
			CH_base_ev_desempeno.clave_registro = $matricula
		GROUP BY
			CH_base_ev_desempeno.nombre_evaluacion";

	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {
			$nombre_evaluacion = utf8_encode($d['nombre_evaluacion']);
			$nombre_completo = utf8_encode($d['nombre_completo']);
			$nombre_grafica = utf8_encode($d['nombre_grafica']); //Se pidió cambiar los nombres

			$nombre = obtenerPrimeraPalabra($nombre_evaluacion);
			$apellido = obtenerPrimeraPalabra($nombre_completo);

			$evaluado = "$nombre $apellido";
		}
	}
	return $nombre_grafica; //Se pidió cambiar los nombres
}


function list_departamentos()
{
	global $dbConnection;
	$data = array();

	$q = "SELECT
				CH_base_ev_desempeno.departamento 
			FROM
				CH_base_ev_desempeno 
			WHERE
				CH_base_ev_desempeno.departamento != 'SERVICIOS GENERALES' 
			GROUP BY
				CH_base_ev_desempeno.departamento UNION
			SELECT
				CH_base_ev_deptos_especiales.departamento_especial AS departamento 
			FROM
				CH_base_ev_deptos_especiales 
			GROUP BY
				CH_base_ev_deptos_especiales.departamento_especial 
			ORDER BY
				departamento ASC";

	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {
			$departamento = utf8_encode($d['departamento']);
			$data[$n]['departamento'] = $departamento;
			$data[42]['departamento'] = 'SERVICIOS GENERALES ITL';
			$data[43]['departamento'] = 'SERVICIOS GENERALES CEL';
			$n++;
		}
	}
	return $data;
}

function promedio_total_deptos($data, $indice)
{
	$acumulado = 0;
	$conteo = 0;

	foreach ($data as $valor) {
		if (isset($valor[$indice]) && $valor[$indice] !== '' && $valor[$indice] !== null && floatval($valor[$indice]) != 0) {
			$acumulado += floatval($valor[$indice]);
			$conteo++;
		}
	}

	if ($conteo > 0) {
		$prom = $acumulado / $conteo;
		return number_format($prom, 2);
	} else {
		return null;
	}
}

function promedio_total_deptosX($data, $indice)
{
	$acumulado = 0;
	$conteo = 0;

	foreach ($data as $valor) {
		if (isset($valor[$indice]) && $valor[$indice] !== '0' && $valor[$indice] !== 0) {
			$acumulado += floatval($valor[$indice]);
			$conteo++;
		}
	}

	if ($conteo > 0) {
		$prom = $acumulado / $conteo;
		return number_format($prom, 2);
	} else {
		return null;
	}
}

function promedios_por_clave($data)
{
	$result = [];

	// Obtener todas las claves presentes en los arrays internos
	$claves = array_keys(call_user_func_array('array_merge', $data));

	foreach ($claves as $clave) {
		$result[$clave] = promedio_total_deptosX($data, $clave);
	}

	return $result;
}

function suma_deptos($departamento, $anio, $tipo_evaluacion, $razon_social)
{
	global $dbConnection;
	$data = array();
	$promedio = array();

	//SI ES SERVICIOS GENERALES, SEPARAR A LOS EMPLEADOS POR ITL Y CEL
	if ($departamento == 'SERVICIOS GENERALES' && $razon_social == 'ITL') {
		$rs = "AND CH_base_ev_desempeno.razon_social = 'ITL'";
	} elseif ($departamento == 'SERVICIOS GENERALES' && $razon_social == 'CEL') {
		$rs = "AND CH_base_ev_desempeno.razon_social = 'CEL'";
	} else {
		$rs = '';
	}

	if ($tipo_evaluacion == '') {
		$t = '';
	} else {
		$t = "AND CH_evaluacion_desempeno.tipo_evaluacion='$tipo_evaluacion'";
	}
	$q = "SELECT
			CH_evaluacion_desempeno.id,
			CH_evaluacion_desempeno.clave,
			CH_evaluacion_desempeno.tipo_evaluacion,
			CH_evaluacion_desempeno.compromiso,
			CH_evaluacion_desempeno.orientacion_cliente,
			CH_evaluacion_desempeno.adaptabilidad_cambio,
			CH_evaluacion_desempeno.comunicacion,
			CH_evaluacion_desempeno.trabajo_equipo,
			CH_evaluacion_desempeno.liderazgo,
			CH_evaluacion_desempeno.efectividad_eficiencia,
			CH_evaluacion_desempeno.integridad,
			CH_evaluacion_desempeno.planeacion_organizacion,
			CH_evaluacion_desempeno.pulcritud,
			CH_evaluacion_desempeno.dinamismo_energia,
			CH_evaluacion_desempeno.puntualidad_asist,
			CH_evaluacion_desempeno.ev_general,
			YEAR ( CH_evaluacion_desempeno.fecha_alta ) AS anio,
			CH_base_ev_desempeno.asignacion_evaluacion 
		FROM
			CH_evaluacion_desempeno
			INNER JOIN CH_base_ev_desempeno ON CH_evaluacion_desempeno.clave_evaluada = CH_base_ev_desempeno.clave_registro
		WHERE
			CH_evaluacion_desempeno.estatus = 0 
			AND YEAR ( CH_evaluacion_desempeno.fecha_alta ) =:anio
			AND CH_base_ev_desempeno.departamento =:departamento $t $rs GROUP BY CH_evaluacion_desempeno.id";

	$stmt = $dbConnection->prepare($q);
	$stmt->execute(array(':anio' => $anio, ':departamento' => $departamento));
	if ($stmt->rowCount() == 0) {
		$q = "SELECT
				CH_evaluacion_desempeno.id, 
				CH_evaluacion_desempeno.clave, 
				CH_evaluacion_desempeno.tipo_evaluacion, 
				CH_evaluacion_desempeno.compromiso, 
				CH_evaluacion_desempeno.orientacion_cliente, 
				CH_evaluacion_desempeno.adaptabilidad_cambio, 
				CH_evaluacion_desempeno.comunicacion, 
				CH_evaluacion_desempeno.trabajo_equipo, 
				CH_evaluacion_desempeno.liderazgo, 
				CH_evaluacion_desempeno.efectividad_eficiencia, 
				CH_evaluacion_desempeno.integridad, 
				CH_evaluacion_desempeno.planeacion_organizacion, 
				CH_evaluacion_desempeno.pulcritud, 
				CH_evaluacion_desempeno.dinamismo_energia, 
				CH_evaluacion_desempeno.puntualidad_asist, 
				CH_evaluacion_desempeno.ev_general, 
				YEAR ( CH_evaluacion_desempeno.fecha_alta ) AS anio, 
				CH_base_ev_desempeno.asignacion_evaluacion
			FROM
				CH_evaluacion_desempeno
				INNER JOIN
				CH_base_ev_desempeno
				ON 
					CH_evaluacion_desempeno.clave_evaluada = CH_base_ev_desempeno.clave_registro
				INNER JOIN
				CH_base_ev_deptos_especiales
				ON 
					CH_evaluacion_desempeno.clave_evaluada = CH_base_ev_deptos_especiales.clave_registro
			WHERE
				CH_evaluacion_desempeno.estatus = 0 AND
				YEAR ( CH_evaluacion_desempeno.fecha_alta ) =:anio AND
				CH_base_ev_deptos_especiales.departamento_especial =:departamento $t
			GROUP BY
				CH_evaluacion_desempeno.id";

		$stmt = $dbConnection->prepare($q);
		$stmt->execute(array(':anio' => $anio, ':departamento' => $departamento));
	}
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {
			$id = $d['id'];
			$asignacion_evaluacion = $d['asignacion_evaluacion'];
			$matricula = $d['clave'];
			$compromiso = $d['compromiso'];
			$orientacion_cliente = $d['orientacion_cliente'];
			$adaptabilidad_cambio = $d['adaptabilidad_cambio'];
			$comunicacion = $d['comunicacion'];
			$trabajo_equipo = $d['trabajo_equipo'];
			$liderazgo = $d['liderazgo'];
			$efectividad_eficiencia = $d['efectividad_eficiencia'];
			$integridad = $d['integridad'];
			$planeacion_organizacion = $d['planeacion_organizacion'];
			$pulcritud = $d['pulcritud'];
			$dinamismo_energia = $d['dinamismo_energia'];
			$puntualidad_asist = $d['puntualidad_asist'];
			$ev_general = $d['ev_general'];
			$anio = $d['anio'];

			if ($ev_general == '') {
				$ev_general = 0;
			}

			if ($asignacion_evaluacion == 'DIR Y JEF') {
				$pulcritud = 0;
				$dinamismo_energia = 0;
				$puntualidad_asist = 0;
			} else {
				if ($asignacion_evaluacion == 'ADMVOS') {
					$liderazgo = 0;
					$pulcritud = 0;
					$dinamismo_energia = 0;
					$puntualidad_asist = 0;
				} elseif ($asignacion_evaluacion == 'INT Y MTTO' || $asignacion_evaluacion == 'VIGILANCIA') {
					$liderazgo = 0;
					$planeacion_organizacion = 0;
				}
			}

			$sum_c = calcularPromedio(json_decode($compromiso));
			$sum_oc = calcularPromedio(json_decode($orientacion_cliente));
			$sum_ac = calcularPromedio(json_decode($adaptabilidad_cambio));
			$sum_com = calcularPromedio(json_decode($comunicacion));
			$sum_te = calcularPromedio(json_decode($trabajo_equipo));
			$sum_li = calcularPromedio(json_decode($liderazgo));
			$sum_ee = calcularPromedio(json_decode($efectividad_eficiencia));
			$sum_i = calcularPromedio(json_decode($integridad));
			$sum_po = calcularPromedio(json_decode($planeacion_organizacion));
			$sum_pul = calcularPromedio(json_decode($pulcritud));
			$sum_din = calcularPromedio(json_decode($dinamismo_energia));
			$sum_pa = calcularPromedio(json_decode($puntualidad_asist));

			$evaluador = nombre_evaluador($matricula);

			$data[$n]['nombre_evaluador'] = $evaluador;
			$data[$n]['1'] = $sum_c;
			$data[$n]['2'] = $sum_oc;
			$data[$n]['3'] = $sum_ac;
			$data[$n]['4'] = $sum_com;
			$data[$n]['5'] = $sum_te;
			$data[$n]['6'] = $sum_li;
			$data[$n]['7'] = $sum_ee;
			$data[$n]['8'] = $sum_i;
			$data[$n]['9'] = $sum_po;
			$data[$n]['12'] = $sum_pul;
			$data[$n]['13'] = $sum_din;
			$data[$n]['14'] = $sum_pa;
			$data[$n]['15'] = $ev_general;
			$n++;
		}

		foreach (range(1, 15) as $key) {
			if ($key == 10 || $key == 11) continue; // Skip keys 10 and 11 as they are not in use
			$promedio[$key] = promedio_total_deptos($data, (string)$key);
		}
		if ($promedio['12'] == null && $promedio['13'] == null && $promedio['14'] == null) {
			//$sum_pul = $sum_din = $sum_pa = null;
			unset($promedio['12']);
			unset($promedio['13']);
			unset($promedio['14']);
		} elseif ($promedio['13']  == null) {
			//$sum_din = null;
			unset($promedio['13']);
		} elseif ($promedio['6']  == null) {
			//$sum_li = null;
			unset($promedio['6']);
		} elseif ($promedio['9']  == null) {
			//$sum_po = null;
			unset($promedio['9']);
		}
		foreach ($promedio as $key => $value) {
			$filtered_array[$key] = ($value != 0) ? $value : null;
		}

		$promedios_sin_cero = array_filter($filtered_array, function ($val) {
			return $val !== null;
		});

		$num_elementos_sin_cero = count($promedios_sin_cero);
		$suma_sin_cero = array_sum($promedios_sin_cero);

		if ($num_elementos_sin_cero > 0) {
			$promedio_sin_cero = $suma_sin_cero / $num_elementos_sin_cero;
			$truncate_promedio_indiv = number_format($promedio_sin_cero, 2);
			$promedio['promedio_individual'] = $truncate_promedio_indiv;
		} else {
			$promedio['promedio_individual'] = null;
		}

		return $promedio;
	}

	return null;
}

function suma_deptos_general($departamento, $razon_social)
{
	$suma = [];
	$contador = [];
	$arrays = detalle_ev_deptos_empleados($departamento, $razon_social);
	// Inicializar arrays de suma y contador
	foreach ($arrays as $array) {
		foreach ($array as $key => $value) {
			if ($key !== 'count') {
				if (!isset($suma[$key])) {
					$suma[$key] = 0;
					$contador[$key] = 0;
				}
				if ($value != 0 && $value !== null) {
					$suma[$key] += $value;
					$contador[$key]++;
				}
			}
		}
	}
	// Calcular promedio
	$promedios = [];
	foreach ($suma as $key => $total) {
		if ($contador[$key] > 0) {
			$promedios[$key] = number_format($total / $contador[$key], 2);
		} else {
			$promedios[$key] = null;
		}
	}
	return $promedios;
}

function detalle_ev_deptos($departamento, $razon_social)
{
	global $dbConnection;
	$data = array();
	$q = "SELECT YEAR
				( CH_evaluacion_desempeno.fecha_alta ) AS anio 
			FROM
				CH_evaluacion_desempeno,
				CH_base_ev_desempeno 
			WHERE
				CH_evaluacion_desempeno.estatus = 0 
			GROUP BY
				YEAR ( CH_evaluacion_desempeno.fecha_alta ) 
			ORDER BY
				fecha_alta DESC";

	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {

			$anio = $d['anio'];
			$data[$n]['anio'] = $anio;

			$j = suma_deptos($departamento, $anio, 'Jefe', $razon_social);
			$s = suma_deptos($departamento, $anio, 'Subordinado', $razon_social);
			$a = suma_deptos($departamento, $anio, utf8_encode('Autoevaluación'), $razon_social);
			$g = suma_deptos_general($departamento, $razon_social);

			$data[$n]['promedio_ev_jefe'] = $j;
			$data[$n]['promedio_ev_subordinados'] = $s;
			$data[$n]['promedio_autoev'] = $a;
			$data[$n]['promedio_general'] = $g;
			$n++;
		}
	}

	return $data;
}

function promedio_evaluacion_deptos($departamento, $razon_social)
{
	global $dbConnection;
	$data = array();
	$q = "SELECT YEAR
			( CH_evaluacion_desempeno.fecha_alta ) AS anio,
			CH_base_ev_desempeno.asignacion_evaluacion 
		FROM
			CH_evaluacion_desempeno
			INNER JOIN CH_base_ev_desempeno ON CH_evaluacion_desempeno.clave_evaluada = CH_base_ev_desempeno.clave_registro
		WHERE
			CH_base_ev_desempeno.departamento =:departamento
			AND CH_evaluacion_desempeno.estatus = 0 
		GROUP BY
			YEAR ( CH_evaluacion_desempeno.fecha_alta ) 
		ORDER BY
			fecha_alta DESC";

	$stmt = $dbConnection->prepare($q);
	$stmt->execute(array(':departamento' => $departamento));
	if ($stmt->rowCount() == 0) {
		$q = "SELECT YEAR
				( CH_evaluacion_desempeno.fecha_alta ) AS anio,
				CH_base_ev_desempeno.asignacion_evaluacion 
			FROM
				CH_evaluacion_desempeno
				INNER JOIN CH_base_ev_desempeno ON CH_evaluacion_desempeno.clave_evaluada = CH_base_ev_desempeno.clave_registro
				INNER JOIN CH_base_ev_deptos_especiales ON CH_evaluacion_desempeno.clave_evaluada = CH_base_ev_deptos_especiales.clave_registro 
			WHERE
				CH_base_ev_deptos_especiales.departamento_especial =:departamento
				AND CH_evaluacion_desempeno.estatus = 0 
			GROUP BY
				YEAR ( CH_evaluacion_desempeno.fecha_alta ) 
			ORDER BY
				fecha_alta DESC";

		$stmt = $dbConnection->prepare($q);
		$stmt->execute(array(':departamento' => $departamento));
	}
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {

			$anio = $d['anio'];
			$data[$n]['anio'] = $anio;

			$j = suma_deptos($departamento, $anio, 'Jefe', $razon_social);
			$s = suma_deptos($departamento, $anio, 'Subordinado', $razon_social);
			$a = suma_deptos($departamento, $anio, utf8_encode('Autoevaluación'), $razon_social);
			$g = suma_deptos($departamento, $anio, '', $razon_social);

			if (isset($j) && !empty($j) && isset($s) && !empty($s) && isset($a) && !empty($a)) {
				$data[$n]['promedio_jefe'] = $j['promedio_individual'];
				$data[$n]['promedio_subordinado'] = $s['promedio_individual'];
				$data[$n]['promedio_autoevaluacion'] = $a['promedio_individual'];
				$sobre_5 = $j['promedio_individual'] * 0.3;
				$sobre_5_Auto = $a['promedio_individual'] * 0.3;
				$sobre_5_subor = $s['promedio_individual'] * 0.4;
			} elseif (isset($s) && !empty($s) && isset($a) && !empty($a)) {
				$data[$n]['promedio_subordinado'] = $s['promedio_individual'];
				$data[$n]['promedio_autoevaluacion'] = $a['promedio_individual'];
				$sobre_5_Auto = $a['promedio_individual'] * 0.4;
				$sobre_5_subor = $s['promedio_individual'] * 0.6;
			} elseif (isset($j) && !empty($j) && isset($a) && !empty($a)) {
				$data[$n]['promedio_jefe'] = $j['promedio_individual'];
				$data[$n]['promedio_autoevaluacion'] = $a['promedio_individual'];
				$sobre_5 = $j['promedio_individual'] * 0.6;
				$sobre_5_Auto = $a['promedio_individual'] * 0.4;
			} elseif (isset($j) && !empty($j)) {
				$data[$n]['promedio_jefe'] = $j['promedio_individual'];
				$sobre_5 = $j['promedio_individual'];
			} elseif (isset($a) && !empty($a)) {
				$data[$n]['promedio_autoevaluacion'] = $a['promedio_individual'];
				$sobre_5_Auto = $a['promedio_individual'];
			} elseif (isset($s) && !empty($s)) {
				$data[$n]['promedio_subordinado'] = $s['promedio_individual'];
				$sobre_5_subor = $s['promedio_individual'] * 0.6;
			}
			$sobre_5_total = ($sobre_5_Auto ?? 0) + ($sobre_5_subor ?? 0) + ($sobre_5 ?? 0);

			if (isset($g)) {
				$data[$n]['promedio_general'] = number_format($sobre_5_total, 2);
			}

			$n++;
		}
	}
	return $data;
}


function es_jefe($nombre_completo)
{
	global $dbConnection;
	$q = "SELECT * FROM `CH_base_ev_desempeno` WHERE supervisor LIKE '%$nombre_completo%'";
	$stmt = $dbConnection->prepare($q);
	$stmt->execute();

	if ($stmt->rowCount() > 0) {
		return 'Si';
	} else {
		return 'No';
	}
}
function detalle_ev_deptos_empleados($departamento, $razon_social)
{
	global $dbConnection, $anio;
	$data = array();
	//SI ES SERVICIOS GENERALES, SEPARAR A LOS EMPLEADOS POR ITL Y CEL
	$rs = '';
	if ($departamento == 'SERVICIOS GENERALES') {
		$rs = ($razon_social == 'ITL') ? "AND CH_base_ev_desempeno.razon_social = 'ITL'" : "AND CH_base_ev_desempeno.razon_social = 'CEL'";
	}

	$q = "SELECT
			CH_evaluacion_desempeno.clave_evaluada,
			CH_base_ev_desempeno.asignacion_evaluacion,
			CH_base_ev_desempeno.nombre_evaluacion,
			CH_base_ev_desempeno.nombre_completo, CH_base_ev_desempeno.nombre_grafica
		FROM
			CH_evaluacion_desempeno
			INNER JOIN CH_base_ev_desempeno ON CH_evaluacion_desempeno.clave_evaluada = CH_base_ev_desempeno.clave_registro 
		WHERE
			CH_evaluacion_desempeno.estatus = 0 
			AND CH_base_ev_desempeno.estatus = 0 
			AND YEAR ( CH_evaluacion_desempeno.fecha_alta ) =:anio
			AND CH_base_ev_desempeno.departamento =:departamento $rs
		GROUP BY
			CH_evaluacion_desempeno.clave_evaluada 
		ORDER BY
		CASE			
			WHEN CH_base_ev_desempeno.asignacion_evaluacion LIKE 'D%' THEN
			0 ELSE 1 
		END,
		CH_base_ev_desempeno.asignacion_evaluacion,
		CH_base_ev_desempeno.puesto_actual";

	$stmt = $dbConnection->prepare($q);
	$stmt->execute([':anio' => $anio, ':departamento' => $departamento]);
	if ($stmt->rowCount() == 0) {
		$q = "SELECT
			CH_evaluacion_desempeno.clave_evaluada,
			CH_base_ev_desempeno.asignacion_evaluacion,
			CH_base_ev_desempeno.nombre_evaluacion,
			CH_base_ev_desempeno.nombre_completo,
			CH_base_ev_desempeno.nombre_grafica
		FROM
			CH_evaluacion_desempeno
			INNER JOIN CH_base_ev_desempeno ON CH_evaluacion_desempeno.clave_evaluada = CH_base_ev_desempeno.clave_registro
			INNER JOIN CH_base_ev_deptos_especiales ON CH_evaluacion_desempeno.clave_evaluada = CH_base_ev_deptos_especiales.clave_registro 
		WHERE
			CH_evaluacion_desempeno.estatus = 0 
			AND CH_base_ev_desempeno.estatus = 0 
			AND YEAR ( CH_evaluacion_desempeno.fecha_alta ) =:anio 
			AND CH_base_ev_deptos_especiales.departamento_especial =:departamento 
		GROUP BY
			CH_evaluacion_desempeno.clave_evaluada 
		ORDER BY
		CASE				
			WHEN CH_base_ev_desempeno.asignacion_evaluacion LIKE 'D%' THEN
			0 ELSE 1 
		END,
			CH_base_ev_desempeno.asignacion_evaluacion,
			CH_base_ev_desempeno.puesto_actual";

		$stmt = $dbConnection->prepare($q);
		$stmt->execute([':anio' => $anio, ':departamento' => $departamento]);
	}
	$n = 0;
	if ($stmt->rowCount() > 0) {
		foreach ($stmt as $d) {
			$clave_evaluada = $d['clave_evaluada'];
			$nombre_evaluacion = $d['nombre_evaluacion'];
			$nombre_completo = utf8_encode($d['nombre_completo']);
			$nombre_grafica = utf8_encode($d['nombre_grafica']);
			$asignacion_evaluacion = $d['asignacion_evaluacion'];

			// Determinar la asignación de evaluación
			$asignacion = '';
			if ($asignacion_evaluacion == 'DIR Y JEF') {
				$es_jefe = es_jefe($nombre_completo);
				$asignacion = $es_jefe == 'Si' ? '(JEFE)' : '';
			}

			// Obtener detalles y manejar valores predeterminados para estadísticas
			$detalles = detalle_evaluacion($clave_evaluada);
			foreach ($detalles as $detalle) {
				$eg = $detalle['estadistica_general'];

				// Asegurarse de que las claves específicas tengan valores predeterminados si no existen
				foreach (['6', '9', '12', '13', '14'] as $key) {
					if (!isset($eg[$key])) {
						$eg[$key] = 0;
					}
				}

				$data[$n] = $eg;
				$data[$n]['evaluado'] = "$nombre_grafica $asignacion"; //$evaluado;
				$n++;
			}
		}
	}

	usort($data, function ($a, $b) {
		// Priorizar 'OSCAR'
		if (strpos(
			$a['evaluado'],
			'OSCAR RODRIGUEZ (JEFE)'
		) !== false) return -1;
		if (
			strpos($b['evaluado'], 'OSCAR RODRIGUEZ (JEFE)') !== false
		) return 1;

		// Luego priorizar los que contengan '(JEFE)'
		if (
			strpos(
				$a['evaluado'],
				'(JEFE)'
			) !== false && strpos($b['evaluado'], '(JEFE)') === false
		) return -1;
		if (
			strpos($b['evaluado'], '(JEFE)') !== false && strpos($a['evaluado'], '(JEFE)') === false
		) return 1;

		// Mantener el orden original para los demás
		return 0;
	});
	return $data;
}
function preguntas_abiertas_empleado($clave)
{
	global $dbConnection;
	$data = array();
	$q = "SELECT 
			YEAR ( CH_evaluacion_desempeno.fecha_alta ) AS anio
		FROM
			CH_evaluacion_desempeno
		WHERE
			CH_evaluacion_desempeno.clave_evaluada = '$clave'
			AND	CH_evaluacion_desempeno.estatus = 0 
			GROUP BY YEAR ( CH_evaluacion_desempeno.fecha_alta )
			ORDER BY fecha_alta DESC";

	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {
			$anio = $d['anio'];
			$pa = abiertas($clave, $anio);

			$data = $pa;
			$n++;
		}
	}

	return $data;
}

function asignacion_evaluacion($clave)
{
	global $dbConnection;
	$q = "SELECT asignacion_evaluacion FROM CH_base_ev_desempeno WHERE clave_registro = '$clave'";
	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {
			$asignacion_evaluacion = $d['asignacion_evaluacion'];
		}
	}
	return $asignacion_evaluacion;
}
function abiertas($clave, $anio)
{
	global $dbConnection;
	$data = array();

	$q = "SELECT
			CH_evaluacion_desempeno.id,
			CH_evaluacion_desempeno.clave_evaluada,
			CH_evaluacion_desempeno.abiertas,
			CH_base_ev_desempeno.nombre_evaluacion,
			CH_base_ev_desempeno.nombre_completo,
			CH_base_ev_desempeno.nombre_grafica 
		FROM
			CH_evaluacion_desempeno
			INNER JOIN CH_base_ev_desempeno ON CH_evaluacion_desempeno.clave = CH_base_ev_desempeno.clave_registro 
		WHERE
			CH_evaluacion_desempeno.clave_evaluada = '$clave' 
			AND CH_evaluacion_desempeno.estatus = 0 
			AND YEAR ( CH_evaluacion_desempeno.fecha_alta ) = '$anio' GROUP BY CH_evaluacion_desempeno.clave";

	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {
			$id = $d['id'];
			$clave = $d['clave_evaluada'];
			$abiertas = ($d['abiertas']);
			$nombre_evaluacion = $d['nombre_evaluacion'];
			$nombre_completo = $d['nombre_completo'];
			$nombre_grafica = utf8_encode($d['nombre_grafica']);
			$nombre = obtenerPrimeraPalabra($nombre_evaluacion);
			$apellido = obtenerPrimeraPalabra($nombre_completo);

			$asignacion_evaluacion = asignacion_evaluacion($clave);
			if ($asignacion_evaluacion == 'VIGILANCIA' || $asignacion_evaluacion == 'INT Y MTTO') {
				$array = json_decode($abiertas, true);

				if (json_last_error() === JSON_ERROR_NONE) {
					// Verifica si existen ambas claves en el array y si son cadenas
					if (isset($array['15-0']) && isset($array['15-1']) && is_string($array['15-0']) && is_string($array['15-1'])) {
						// Intercambia los valores
						$temp = $array['15-0'];
						$array['15-0'] = $array['15-1'];
						$array['15-1'] = $temp;
					}

					// Convertir el array de vuelta a JSON
					$json_invertido = json_encode($array, JSON_UNESCAPED_UNICODE);

					// Mostrar el JSON invertido
					$abiertas = $json_invertido;
				}
			}

			$data[$n]['pregunta'] = $abiertas;
			$data[$n]['nombre'] =  $nombre_grafica; //$nombre . ' ' . $apellido;

			$n++;
		}
	}
	foreach ($data as &$item) {
		// Decodificar JSON de la clave 'pregunta'
		$preguntas = json_decode($item["pregunta"], true);

		// Verificar si la decodificación fue exitosa
		if (is_array($preguntas)) {
			// Recorrer y modificar los valores no vacíos
			foreach ($preguntas as $key => $value) {
				if (!empty($value)) {
					$preguntas[$key] = $value . " (" . $item["nombre"] . ")";
				}
			}
			// Volver a codificar el JSON y asignarlo de nuevo a la clave 'pregunta'
			$item["pregunta"] = json_encode($preguntas);
		}
	}
	/* ------------------------------------ */
	$outputArray = [];

	foreach ($data as $entry) {
		$pregunta = json_decode($entry['pregunta'], true);

		if (!is_array($pregunta)) {
			error_log('Invalid JSON in pregunta: ' . $entry['pregunta']);
			continue; // Skip this entry or handle as needed
		}

		foreach ($pregunta as $key => $value) {
			if (!empty($value)) {
				if (!isset($outputArray[$key])) {
					$outputArray[$key] = [];
				}
				$outputArray[$key][] = $value;
			}
		}
	}
	return $outputArray;
}

/* ABIERTAS POR DEPARTAMENTO */
/* function preguntas_abiertas_depto($departamento, $razon_social)
{
	global $dbConnection;
	$data = array();

	// Primera consulta
	$q = "SELECT YEAR
				( CH_evaluacion_desempeno.fecha_alta ) AS anio,
				CH_base_ev_desempeno.departamento
			FROM
				CH_evaluacion_desempeno
				INNER JOIN CH_base_ev_desempeno ON CH_evaluacion_desempeno.clave_evaluada = CH_base_ev_desempeno.clave_registro 
			WHERE
				CH_base_ev_desempeno.departamento =:departamento
				AND CH_evaluacion_desempeno.estatus = 0 GROUP BY CH_base_ev_desempeno.departamento";
	$stmt = $dbConnection->prepare($q);
	$stmt->execute(array(':departamento' => $departamento));

	// Si no hay resultados, ejecutamos la segunda consulta
	if ($stmt->rowCount() == 0) {
		$q = "SELECT YEAR
				( CH_evaluacion_desempeno.fecha_alta ) AS anio,
				CH_base_ev_deptos_especiales.departamento_especial 
			FROM
				CH_evaluacion_desempeno
				INNER JOIN CH_base_ev_desempeno ON CH_evaluacion_desempeno.clave_evaluada = CH_base_ev_desempeno.clave_registro
				INNER JOIN CH_base_ev_deptos_especiales ON CH_evaluacion_desempeno.clave_evaluada = CH_base_ev_deptos_especiales.clave_registro 
			WHERE
				CH_base_ev_deptos_especiales.departamento_especial =:departamento
				AND CH_evaluacion_desempeno.estatus = 0 
			GROUP BY
				CH_base_ev_deptos_especiales.departamento_especial";

		$stmt = $dbConnection->prepare($q);
		$stmt->execute(array(':departamento' => $departamento));
	}

	// Procesar resultados de ambas consultas
	if ($stmt->rowCount() > 0) {
		foreach ($stmt as $d) {
			$anio = $d['anio'];
			$pa = abiertas_depto($departamento, $anio, $razon_social);
			// Acumulamos resultados de funcion2 en $data
			$data = array_merge($data, $pa);
		}
	}

	return $data;
} */

function preguntas_abiertas_depto($departamento, $razon_social) //abiertas_depto($departamento, $anio, $razon_social)
{
	global $dbConnection;
	$anio = 2024;
	$data = array();
	//SI ES SERVICIOS GENERALES, SEPARAR A LOS EMPLEADOS POR ITL Y CEL
	if ($departamento == 'SERVICIOS GENERALES' && $razon_social == 'ITL') {
		$rs = "AND base_evaluador.razon_social = 'ITL'";
	} elseif ($departamento == 'SERVICIOS GENERALES' && $razon_social == 'CEL') {
		$rs = "AND base_evaluador.razon_social = 'CEL'";
	} else {
		$rs = '';
	}

	$q = "SELECT
			base_evaluador.nombre_completo AS completo_evaluador,
			base_evaluador.nombre_evaluacion AS nombre_evaluador,
			base_evaluador.nombre_grafica AS evaluador_grafica,
			base_evaluado.nombre_completo,
			base_evaluado.nombre_evaluacion, 
			base_evaluado.nombre_grafica AS evaluado_grafica, 
			evaluador.tipo_evaluacion,
			evaluador.abiertas,
			base_evaluado.clave_registro
		FROM
			CH_evaluacion_desempeno AS evaluador
			INNER JOIN CH_base_ev_desempeno AS base_evaluador ON evaluador.clave = base_evaluador.clave_registro
			INNER JOIN CH_base_ev_desempeno AS base_evaluado ON evaluador.clave_evaluada = base_evaluado.clave_registro 
		WHERE
			base_evaluado.departamento =:departamento
			AND evaluador.estatus = 0 $rs
			AND YEAR ( evaluador.fecha_alta ) =:anio 
		GROUP BY
			evaluador.id";

	$stmt = $dbConnection->prepare($q);
	$stmt->execute(array(':departamento' => $departamento, ':anio' => $anio));
	if ($stmt->rowCount() == 0) {
		$q = "SELECT
				base_evaluador.nombre_completo AS completo_evaluador,
				base_evaluador.nombre_evaluacion AS nombre_evaluador,
				base_evaluador.nombre_grafica AS evaluador_grafica,
				base_evaluado.nombre_completo,
				base_evaluado.nombre_evaluacion,
				base_evaluado.nombre_grafica AS evaluado_grafica,
				evaluador.tipo_evaluacion,
				evaluador.abiertas,
				base_evaluado.clave_registro
			FROM
				CH_evaluacion_desempeno AS evaluador
				INNER JOIN CH_base_ev_desempeno AS base_evaluador ON evaluador.clave = base_evaluador.clave_registro
				INNER JOIN CH_base_ev_desempeno AS base_evaluado ON evaluador.clave_evaluada = base_evaluado.clave_registro
				INNER JOIN CH_base_ev_deptos_especiales AS evaluador_especial ON evaluador.clave_evaluada = evaluador_especial.clave_registro 
				INNER JOIN CH_base_ev_deptos_especiales AS evaluado_especial  ON evaluador.clave = evaluado_especial.clave_registro 
			WHERE
				evaluado_especial.departamento_especial =:departamento
				AND evaluador.estatus = 0 
				AND YEAR ( evaluador.fecha_alta ) =:anio 
			GROUP BY
				evaluador.id";

		$stmt = $dbConnection->prepare($q);
		$stmt->execute(array(':departamento' => $departamento,  ':anio' => $anio));
	}
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {
			$clave = $d['clave_registro'];
			$completo_evaluador = utf8_encode($d['completo_evaluador']);
			$abiertas = utf8_decode($d['abiertas']);

			$evaluador_grafica = utf8_encode($d['evaluador_grafica']);
			$evaluado_grafica = utf8_encode($d['evaluado_grafica']);
			$tipo_evaluacion = $d['tipo_evaluacion'];

			$completo_evaluador = $evaluador_grafica; //$nombre . ' ' . $apellido;
			$completo_evaluado = $evaluado_grafica; //$nombre2 . ' ' . $apellido2;

			if ($tipo_evaluacion == 'Jefe' || $tipo_evaluacion == 'Subordinado') {
				$nombres = "$completo_evaluador A $completo_evaluado";
			} else {
				$nombres = "$completo_evaluado - Autoevaluaci&oacute;n";
			}

			$asignacion_evaluacion = asignacion_evaluacion($clave);
			if ($asignacion_evaluacion == 'VIGILANCIA' || $asignacion_evaluacion == 'INT Y MTTO') {
				$array = json_decode($abiertas, true);

				if (json_last_error() === JSON_ERROR_NONE) {
					// Verifica si existen ambas claves en el array y si son cadenas
					if (isset($array['15-0']) && isset($array['15-1']) && is_string($array['15-0']) && is_string($array['15-1'])) {
						// Intercambia los valores
						$temp = $array['15-0'];
						$array['15-0'] = $array['15-1'];
						$array['15-1'] = $temp;
					}

					// Convertir el array de vuelta a JSON
					$json_invertido = json_encode($array, JSON_UNESCAPED_UNICODE);

					// Mostrar el JSON invertido
					$abiertas = $json_invertido;
				}
			}

			$data[$n]['pregunta'] = $abiertas;
			$data[$n]['nombre'] = $nombres;

			$n++;
		}
	}
	foreach ($data as &$item) {
		// Decodificar JSON de la clave 'pregunta'
		$preguntas = json_decode($item["pregunta"], true);

		// Verificar si la decodificación fue exitosa
		if (is_array($preguntas)) {
			// Recorrer y modificar los valores no vacíos
			foreach ($preguntas as $key => $value) {
				if (!empty($value)) {
					$preguntas[$key] = $value . " (" . $item["nombre"] . ")";
				}
			}
			// Volver a codificar el JSON y asignarlo de nuevo a la clave 'pregunta'
			$item["pregunta"] = json_encode($preguntas);
		}
	}
	/* ------------------------------------ */
	$outputArray = [];

	if (is_array($data)) {
		foreach ($data as $entry) {
			$pregunta = json_decode($entry['pregunta'], true);

			if (is_array($pregunta)) {
				foreach ($pregunta as $key => $value) {
					if (!empty($value)) {
						if (!isset($outputArray[$key])) {
							$outputArray[$key] = [];
						}
						$outputArray[$key][] = $value;
					}
				}
			} else {
			}
		}
	}
	return $outputArray;
}
/* ------------------- */
function evaluaciones_auto($departamento)
{
	global $dbConnection;
	$data = array();
	$autoev = array();

	$q = "SELECT
			COUNT(CH_base_ev_desempeno.id) AS cuenta_autoev
		FROM
			CH_base_ev_desempeno
			WHERE
			CH_base_ev_desempeno.departamento = '$departamento'";
	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {
			$cuenta_autoev = $d['cuenta_autoev'];
		}
	}

	return $cuenta_autoev;
}

function cantidad_evaluaciones($departamento)
{
	global $dbConnection;
	$data = array();
	$subor = array();

	$q = "SELECT
			COUNT(CH_base_ev_desempeno.id) AS cuenta_subor
		FROM
			CH_base_ev_desempeno
			WHERE
			CH_base_ev_desempeno.departamento = '$departamento'AND CH_base_ev_desempeno.asignacion_evaluacion != 'DIR Y JEF'";
	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {
			$cuenta_subor = $d['cuenta_subor'];
			$cuenta_auto = evaluaciones_auto($departamento);
			$cuenta_jefe = $cuenta_subor;

			$totales = $cuenta_auto + $cuenta_jefe + $cuenta_subor;
		}
	}
	return $totales;
}

function obtenerPrimeraPalabra($cadena)
{
	// Divide la cadena en un array usando el espacio como delimitador
	$palabras = explode(" ", $cadena);

	// Retorna la primera palabra
	return $palabras[0];
}

function checkAllZero($array)
{
	foreach ($array as $item) {
		foreach ($item as $key => $value) {
			if (is_numeric($key) && $value != 0) {
				return $array;
			}
		}
	}
	return null;
}

function replaceAllZeroesWithNull(&$array)
{
	// Identificar claves numéricas
	$array = array_values($array);
	$numericKeys = array_keys($array[0]);
	$numericKeys = array_filter($numericKeys, 'is_numeric');

	// Almacenar claves que son completamente ceros
	$keysWithAllZeros = [];

	foreach ($numericKeys as $key) {
		$allZeros = true;
		foreach ($array as $subArray) {
			if ($subArray[$key] !== 0) {
				$allZeros = false;
				break;
			}
		}
		if ($allZeros) {
			$keysWithAllZeros[] = $key;
		}
	}

	// Reemplazar ceros por null en claves identificadas
	foreach ($array as &$subArray) {
		foreach ($keysWithAllZeros as $key) {
			$subArray[$key] = null;
		}
	}
}

function todos_empleados()
{
	global $dbConnection;
	$data = array();
	$q = "SELECT clave_registro, nombre_completo, departamento, asignacion_evaluacion FROM `CH_base_ev_desempeno` GROUP BY clave_registro";
	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$n = 0;
		$tabla = "<table>
	<thead>
		<th>Nombre</th>
		<th>Departamento</th>
		<th>Asignación</th>
		<th>compromiso</th>
		<th>orientacion</th>
		<th>adaptabilidad</th>
		<th>comunicacion</th>
		<th>t_colaborativo</th>
		<th>liderazgo</th>
		<th>efectividad</th>
		<th>integridad</th>
		<th>planeacion</th>
		<th>orden</th>
		<th>dinamismo</th>
		<th>puntualidad</th>
		<th>ev_general</th>
	</thead><tbody>
	";

		foreach ($stmt as $d) {
			$clave_registro = $d['clave_registro'];
			$nombre_completo = utf8_encode($d['nombre_completo']);
			$asignacion_evaluacion = $d['asignacion_evaluacion'];
			$departamento = utf8_encode($d['departamento']);
			$detalle = detalle_evaluacion($clave_registro);

			foreach ($detalle as $d) {
				$d = $d['estadistica_general'];
				$p = $d['preguntas_abiertas'];

				$data[$n]['nombre_completo'] = $nombre_completo;
				$compromiso = $d['1'];
				$orientacion = $d['2'];
				$adaptabilidad = $d['3'];
				$comunicacion = $d['4'];
				$t_colaborativo = $d['5'];
				$liderazgo = $d['6'];
				$efectividad = $d['7'];
				$integridad = $d['8'];
				$planeacion = $d['9'];
				$orden = $d['12'];
				$dinamismo = $d['13'];
				$puntualidad = $d['14'];
				$ev_general = $d['15'];

				$data[$n]['1'] = $compromiso;
				$data[$n]['2'] = $orientacion;
				$data[$n]['3'] = $adaptabilidad;
				$data[$n]['4'] = $comunicacion;
				$data[$n]['5'] = $t_colaborativo;
				$data[$n]['6'] = $liderazgo;
				$data[$n]['7'] = $efectividad;
				$data[$n]['8'] = $integridad;
				$data[$n]['9'] = $planeacion;
				$data[$n]['12'] = $orden;
				$data[$n]['13'] = $dinamismo;
				$data[$n]['14'] = $puntualidad;
				$data[$n]['15'] = $ev_general;
				$n++;

				$tabla .= "<tr>
				<td>$nombre_completo</td>
				<td>$departamento</td>
				<td>$asignacion_evaluacion</td>
				<td>$compromiso</td>
				<td>$orientacion</td>
				<td>$adaptabilidad</td>
				<td>$comunicacion</td>
				<td>$t_colaborativo</td>
				<td>$liderazgo</td>
				<td>$efectividad</td>
				<td>$integridad</td>
				<td>$planeacion</td>
				<td>$orden</td>
				<td>$dinamismo</td>
				<td>$puntualidad</td>
				<td>$ev_general</td>
				</tr>";
			}
		}
	}
	$tabla .= "
	</tbody>
	</table>";

	return $tabla;
}

/* ---------------EVALUACIONES DE LOS SUBORDINADOS DE CADA JEFE------------------------------- */
function nombre_supervisor($matricula)
{
	global $dbConnection, $anio;
	$nombre_completo = '';
	$q = "SELECT
			CH_base_ev_desempeno.nombre_completo
		FROM
			CH_base_ev_desempeno
		WHERE
			CH_base_ev_desempeno.estatus = 0 AND CH_base_ev_desempeno.clave_registro =:matricula";
	$stmt = $dbConnection->prepare($q);
	$stmt->execute(array(':matricula' => $matricula));
	$n = 0;
	if ($stmt->rowCount() > 0) {
		foreach ($stmt as $d) {
			$nombre_completo = utf8_encode($d['nombre_completo']);
		}
	}
	return $nombre_completo;
}
function suma_subordinados($supervisor, $anio, $tipo_evaluacion)
{
	global $dbConnection;
	$data = array();
	$promedio = array();
	if ($tipo_evaluacion == '') {
		$t = '';
	} else {
		$t = "AND CH_evaluacion_desempeno.tipo_evaluacion='$tipo_evaluacion'";
	}
	$q = "SELECT
			CH_evaluacion_desempeno.id,
			CH_evaluacion_desempeno.clave,
			CH_evaluacion_desempeno.tipo_evaluacion,
			CH_evaluacion_desempeno.compromiso,
			CH_evaluacion_desempeno.orientacion_cliente,
			CH_evaluacion_desempeno.adaptabilidad_cambio,
			CH_evaluacion_desempeno.comunicacion,
			CH_evaluacion_desempeno.trabajo_equipo,
			CH_evaluacion_desempeno.liderazgo,
			CH_evaluacion_desempeno.efectividad_eficiencia,
			CH_evaluacion_desempeno.integridad,
			CH_evaluacion_desempeno.planeacion_organizacion,
			CH_evaluacion_desempeno.pulcritud,
			CH_evaluacion_desempeno.dinamismo_energia,
			CH_evaluacion_desempeno.puntualidad_asist,
			CH_evaluacion_desempeno.ev_general,
			YEAR ( CH_evaluacion_desempeno.fecha_alta ) AS anio,
			CH_base_ev_desempeno.asignacion_evaluacion 
		FROM
			CH_evaluacion_desempeno
			INNER JOIN CH_base_ev_desempeno ON CH_evaluacion_desempeno.clave_evaluada = CH_base_ev_desempeno.clave_registro
		WHERE
			CH_evaluacion_desempeno.estatus = 0 
			AND YEAR ( CH_evaluacion_desempeno.fecha_alta ) =:anio
			AND (CH_base_ev_desempeno.supervisor =:supervisor OR CH_base_ev_desempeno.nombre_completo =:supervisor2) 
			$t GROUP BY CH_evaluacion_desempeno.id";

	$stmt = $dbConnection->prepare($q);
	$stmt->execute(array(':anio' => $anio, ':supervisor' => $supervisor, ':supervisor2' => $supervisor));
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {
			$id = $d['id'];
			$asignacion_evaluacion = $d['asignacion_evaluacion'];
			$matricula = $d['clave'];
			$compromiso = $d['compromiso'];
			$orientacion_cliente = $d['orientacion_cliente'];
			$adaptabilidad_cambio = $d['adaptabilidad_cambio'];
			$comunicacion = $d['comunicacion'];
			$trabajo_equipo = $d['trabajo_equipo'];
			$liderazgo = $d['liderazgo'];
			$efectividad_eficiencia = $d['efectividad_eficiencia'];
			$integridad = $d['integridad'];
			$planeacion_organizacion = $d['planeacion_organizacion'];
			$pulcritud = $d['pulcritud'];
			$dinamismo_energia = $d['dinamismo_energia'];
			$puntualidad_asist = $d['puntualidad_asist'];
			$ev_general = $d['ev_general'];
			$anio = $d['anio'];

			if ($ev_general == '') {
				$ev_general = 0;
			}

			if ($asignacion_evaluacion == 'DIR Y JEF') {
				$pulcritud = 0;
				$dinamismo_energia = 0;
				$puntualidad_asist = 0;
			} else {
				if ($asignacion_evaluacion == 'ADMVOS') {
					$liderazgo = 0;
					$pulcritud = 0;
					$dinamismo_energia = 0;
					$puntualidad_asist = 0;
				} elseif ($asignacion_evaluacion == 'INT Y MTTO' || $asignacion_evaluacion == 'VIGILANCIA') {
					$liderazgo = 0;
					$planeacion_organizacion = 0;
				}
			}

			$sum_c = calcularPromedio(json_decode($compromiso));
			$sum_oc = calcularPromedio(json_decode($orientacion_cliente));
			$sum_ac = calcularPromedio(json_decode($adaptabilidad_cambio));
			$sum_com = calcularPromedio(json_decode($comunicacion));
			$sum_te = calcularPromedio(json_decode($trabajo_equipo));
			$sum_li = calcularPromedio(json_decode($liderazgo));
			$sum_ee = calcularPromedio(json_decode($efectividad_eficiencia));
			$sum_i = calcularPromedio(json_decode($integridad));
			$sum_po = calcularPromedio(json_decode($planeacion_organizacion));
			$sum_pul = calcularPromedio(json_decode($pulcritud));
			$sum_din = calcularPromedio(json_decode($dinamismo_energia));
			$sum_pa = calcularPromedio(json_decode($puntualidad_asist));

			$evaluador = nombre_evaluador($matricula);

			$data[$n]['nombre_evaluador'] = $evaluador;
			$data[$n]['1'] = $sum_c;
			$data[$n]['2'] = $sum_oc;
			$data[$n]['3'] = $sum_ac;
			$data[$n]['4'] = $sum_com;
			$data[$n]['5'] = $sum_te;
			$data[$n]['6'] = $sum_li;
			$data[$n]['7'] = $sum_ee;
			$data[$n]['8'] = $sum_i;
			$data[$n]['9'] = $sum_po;
			$data[$n]['12'] = $sum_pul;
			$data[$n]['13'] = $sum_din;
			$data[$n]['14'] = $sum_pa;
			$data[$n]['15'] = $ev_general;
			$n++;
		}

		foreach (range(1, 15) as $key) {
			if ($key == 10 || $key == 11) continue; // Skip keys 10 and 11 as they are not in use
			$promedio[$key] = promedio_total_deptos($data, (string)$key);
		}
		if ($promedio['12'] == null && $promedio['13'] == null && $promedio['14'] == null) {
			//$sum_pul = $sum_din = $sum_pa = null;
			unset($promedio['12']);
			unset($promedio['13']);
			unset($promedio['14']);
		} elseif ($promedio['13']  == null) {
			//$sum_din = null;
			unset($promedio['13']);
		} elseif ($promedio['6']  == null) {
			//$sum_li = null;
			unset($promedio['6']);
		} elseif ($promedio['9']  == null) {
			//$sum_po = null;
			unset($promedio['9']);
		}
		foreach ($promedio as $key => $value) {
			$filtered_array[$key] = ($value != 0) ? $value : null;
		}

		$promedios_sin_cero = array_filter($filtered_array, function ($val) {
			return $val !== null;
		});

		$num_elementos_sin_cero = count($promedios_sin_cero);
		$suma_sin_cero = array_sum($promedios_sin_cero);

		if ($num_elementos_sin_cero > 0) {
			$promedio_sin_cero = $suma_sin_cero / $num_elementos_sin_cero;
			$truncate_promedio_indiv = number_format($promedio_sin_cero, 2);
			$promedio['promedio_individual'] = $truncate_promedio_indiv;
		} else {
			$promedio['promedio_individual'] = null;
		}

		return $promedio;
	}

	return null;
}
function suma_general_subordinados($supervisor)
{
	$suma = [];
	$contador = [];
	$arrays = detalle_ev_subordinados($supervisor);
	// Inicializar arrays de suma y contador
	foreach ($arrays as $array) {
		foreach ($array as $key => $value) {
			if ($key !== 'count') {
				if (!isset($suma[$key])) {
					$suma[$key] = 0;
					$contador[$key] = 0;
				}
				if ($value != 0 && $value !== null) {
					$suma[$key] += $value;
					$contador[$key]++;
				}
			}
		}
	}
	// Calcular promedio
	$promedios = [];
	foreach ($suma as $key => $total) {
		if ($contador[$key] > 0) {
			$promedios[$key] = number_format($total / $contador[$key], 2);
		} else {
			$promedios[$key] = null;
		}
	}
	return $promedios;
}
function detalle_ev_subordinados($supervisor)
{
	global $dbConnection, $anio;
	$data = array();
	$q = "SELECT
			CH_evaluacion_desempeno.id,
			CH_evaluacion_desempeno.clave_evaluada,
			YEAR ( CH_evaluacion_desempeno.fecha_alta ) AS anio,
			CH_base_ev_desempeno.asignacion_evaluacion,
			CH_base_ev_desempeno.nombre_completo, CH_base_ev_desempeno.nombre_grafica
		FROM
			CH_evaluacion_desempeno
			INNER JOIN CH_base_ev_desempeno ON CH_evaluacion_desempeno.clave_evaluada = CH_base_ev_desempeno.clave_registro 
		WHERE
			CH_evaluacion_desempeno.estatus = 0 
			AND CH_base_ev_desempeno.estatus = 0 
			AND YEAR ( CH_evaluacion_desempeno.fecha_alta ) =:anio
			AND (CH_base_ev_desempeno.supervisor =:supervisor OR CH_base_ev_desempeno.nombre_completo =:supervisor2)
		GROUP BY
			CH_evaluacion_desempeno.clave_evaluada 
		ORDER BY
		CASE			
			WHEN CH_base_ev_desempeno.asignacion_evaluacion LIKE 'D%' THEN
			0 ELSE 1 
		END,
		CH_base_ev_desempeno.asignacion_evaluacion,
		CH_base_ev_desempeno.puesto_actual";

	$stmt = $dbConnection->prepare($q);
	$stmt->execute([':anio' => $anio, ':supervisor' => $supervisor, ':supervisor2' => $supervisor]);
	$n = 0;
	if ($stmt->rowCount() > 0) {
		foreach ($stmt as $d) {
			$clave_evaluada = $d['clave_evaluada'];
			$nombre_completo = utf8_encode($d['nombre_completo']);
			$nombre_grafica = utf8_encode($d['nombre_grafica']);
			$asignacion_evaluacion = $d['asignacion_evaluacion'];

			// Determinar la asignación de evaluación
			$asignacion = '';
			if ($asignacion_evaluacion == 'DIR Y JEF') {
				$es_jefe = es_jefe($nombre_completo);
				$asignacion = $es_jefe == 'Si' ? '(JEFE)' : '';
			}
			// Obtener detalles y manejar valores predeterminados para estadísticas
			$detalles = detalle_evaluacion($clave_evaluada);
			foreach ($detalles as $detalle) {
				$eg = $detalle['estadistica_general'];

				// Asegurarse de que las claves específicas tengan valores predeterminados si no existen
				foreach (['6', '9', '12', '13', '14'] as $key) {
					if (!isset($eg[$key])) {
						$eg[$key] = 0;
					}
				}

				$data[$n] = $eg;
				$data[$n]['evaluado'] = "$nombre_grafica $asignacion"; //$evaluado;
				$n++;
			}
		}
	}
	usort($data, function ($a, $b) {
		// Priorizar 'OSCAR'
		if (strpos(
			$a['evaluado'],
			'OSCAR RODRIGUEZ (JEFE)'
		) !== false) return -1;
		if (
			strpos($b['evaluado'], 'OSCAR RODRIGUEZ (JEFE)') !== false
		) return 1;

		// Luego priorizar los que contengan '(JEFE)'
		if (
			strpos(
				$a['evaluado'],
				'(JEFE)'
			) !== false && strpos($b['evaluado'], '(JEFE)') === false
		) return -1;
		if (
			strpos($b['evaluado'], '(JEFE)') !== false && strpos($a['evaluado'], '(JEFE)') === false
		) return 1;

		// Mantener el orden original para los demás
		return 0;
	});
	return $data;
}
/* Gráficas por competencia*/
function grafica_por_competencias($matricula)
{
	global $dbConnection, $anio;
	$data = array();
	$supervisor = nombre_supervisor($matricula);
	$q = "SELECT
			CH_evaluacion_desempeno.id,
			CH_evaluacion_desempeno.clave_evaluada,
			YEAR ( CH_evaluacion_desempeno.fecha_alta ) AS anio,
			CH_base_ev_desempeno.asignacion_evaluacion,
			CH_base_ev_desempeno.nombre_evaluacion,
			CH_base_ev_desempeno.nombre_completo, CH_base_ev_desempeno.nombre_grafica
		FROM
			CH_evaluacion_desempeno
			INNER JOIN CH_base_ev_desempeno ON CH_evaluacion_desempeno.clave_evaluada = CH_base_ev_desempeno.clave_registro 
		WHERE
			CH_evaluacion_desempeno.estatus = 0 
			AND CH_base_ev_desempeno.estatus = 0 
			AND YEAR ( CH_evaluacion_desempeno.fecha_alta ) =:anio
			AND (CH_base_ev_desempeno.supervisor =:supervisor OR CH_base_ev_desempeno.nombre_completo =:supervisor2)
		GROUP BY
			CH_evaluacion_desempeno.clave_evaluada 
		ORDER BY
		CASE			
			WHEN CH_base_ev_desempeno.asignacion_evaluacion LIKE 'D%' THEN
			0 ELSE 1 
		END,
		CH_base_ev_desempeno.asignacion_evaluacion,
		CH_base_ev_desempeno.puesto_actual";

	$stmt = $dbConnection->prepare($q);
	$stmt->execute([':anio' => $anio, ':supervisor' => $supervisor, ':supervisor2' => $supervisor]);
	$n = 0;
	if ($stmt->rowCount() > 0) {
		foreach ($stmt as $d) {
			$clave_evaluada = $d['clave_evaluada'];
			$nombre_completo = utf8_encode($d['nombre_completo']);
			$nombre_grafica = utf8_encode($d['nombre_grafica']);
			$asignacion_evaluacion = $d['asignacion_evaluacion'];

			// Determinar la asignación de evaluación
			$asignacion = '';
			if (
				$asignacion_evaluacion == 'DIR Y JEF'
			) {
				$es_jefe = es_jefe($nombre_completo);
				$asignacion = $es_jefe == 'Si' ? '(JEFE)' : '';
			}
			// Obtener detalles y manejar valores predeterminados para estadísticas
			$detalles = detalle_evaluacion($clave_evaluada);
			foreach ($detalles as $detalle) {
				$eg = $detalle['estadistica_general'];

				// Asegurarse de que las claves específicas tengan valores predeterminados si no existen
				foreach (['6', '9', '12', '13', '14'] as $key) {
					if (!isset($eg[$key])) {
						$eg[$key] = 0;
					}
				}

				$data[$n] = $eg;
				$data[$n]['evaluado'] = "$nombre_grafica $asignacion"; //$evaluado;
				$n++;
			}
		}
	}
	return $data;
}
/* Gráfica general */
function grafica_general_empleados($matricula)
{
	global $anio;
	$data = array();
	$supervisor = nombre_supervisor($matricula);
	$g = suma_general_subordinados($supervisor);
	$data['promedio_general'] = $g;
	return $data;
}

/* Promedio */
function promedio_evaluacion_subordinados($matricula)
{
	global $dbConnection;
	$data = array();
	$supervisor = nombre_supervisor($matricula);
	$q = "SELECT YEAR
			( CH_evaluacion_desempeno.fecha_alta ) AS anio,
			CH_base_ev_desempeno.asignacion_evaluacion 
		FROM
			CH_evaluacion_desempeno
			INNER JOIN CH_base_ev_desempeno ON CH_evaluacion_desempeno.clave_evaluada = CH_base_ev_desempeno.clave_registro
		WHERE
			CH_base_ev_desempeno.supervisor =:supervisor OR CH_base_ev_desempeno.nombre_completo =:supervisor2
			AND CH_evaluacion_desempeno.estatus = 0 
		GROUP BY
			YEAR ( CH_evaluacion_desempeno.fecha_alta ) 
		ORDER BY
			fecha_alta DESC";

	$stmt = $dbConnection->prepare($q);
	$stmt->execute(array(':supervisor' => $supervisor, ':supervisor2' => $supervisor));

	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {

			$anio = $d['anio'];
			$data[$n]['anio'] = $anio;

			$j = suma_subordinados($supervisor, $anio, 'Jefe');
			$s = suma_subordinados($supervisor, $anio, 'Subordinado');
			$a = suma_subordinados($supervisor, $anio, utf8_encode('Autoevaluación'));
			$g = suma_subordinados($supervisor, $anio, '');

			if (
				isset($j) && !empty($j) && isset($s) && !empty($s) && isset($a) && !empty($a)
			) {
				$data[$n]['promedio_jefe'] = $j['promedio_individual'];
				$data[$n]['promedio_subordinado'] = $s['promedio_individual'];
				$data[$n]['promedio_autoevaluacion'] = $a['promedio_individual'];
				$sobre_5 = $j['promedio_individual'] * 0.3;
				$sobre_5_Auto = $a['promedio_individual'] * 0.3;
				$sobre_5_subor = $s['promedio_individual'] * 0.4;
			} elseif (isset($s) && !empty($s) && isset($a) && !empty($a)) {
				$data[$n]['promedio_subordinado'] = $s['promedio_individual'];
				$data[$n]['promedio_autoevaluacion'] = $a['promedio_individual'];
				$sobre_5_Auto = $a['promedio_individual'] * 0.4;
				$sobre_5_subor = $s['promedio_individual'] * 0.6;
			} elseif (isset($j) && !empty($j) && isset($a) && !empty($a)) {
				$data[$n]['promedio_jefe'] = $j['promedio_individual'];
				$data[$n]['promedio_autoevaluacion'] = $a['promedio_individual'];
				$sobre_5 = $j['promedio_individual'] * 0.6;
				$sobre_5_Auto = $a['promedio_individual'] * 0.4;
			} elseif (isset($j) && !empty($j)) {
				$data[$n]['promedio_jefe'] = $j['promedio_individual'];
				$sobre_5 = $j['promedio_individual'];
			} elseif (isset($a) && !empty($a)) {
				$data[$n]['promedio_autoevaluacion'] = $a['promedio_individual'];
				$sobre_5_Auto = $a['promedio_individual'];
			} elseif (isset($s) && !empty($s)) {
				$data[$n]['promedio_subordinado'] = $s['promedio_individual'];
				$sobre_5_subor = $s['promedio_individual'] * 0.6;
			}
			$sobre_5_total = ($sobre_5_Auto ?? 0) + ($sobre_5_subor ?? 0) + ($sobre_5 ?? 0);

			if (isset($g)) {
				$data[$n]['promedio_general'] = number_format($sobre_5_total, 2);
			}

			$n++;
		}
	}
	return $data;
}
function preguntas_abiertas_subordinado($matricula)
{
	global $dbConnection;
	$data = array();
	$supervisor = nombre_supervisor($matricula);
	// Primera consulta
	$q = "SELECT YEAR
				( CH_evaluacion_desempeno.fecha_alta ) AS anio,
				CH_base_ev_desempeno.supervisor
			FROM
				CH_evaluacion_desempeno
				INNER JOIN CH_base_ev_desempeno ON CH_evaluacion_desempeno.clave_evaluada = CH_base_ev_desempeno.clave_registro 
			WHERE
				CH_base_ev_desempeno.supervisor =:supervisor OR CH_base_ev_desempeno.nombre_completo=:supervisor2
				AND CH_evaluacion_desempeno.estatus = 0 GROUP BY CH_base_ev_desempeno.supervisor";
	$stmt = $dbConnection->prepare($q);
	$stmt->execute(array(':supervisor' => $supervisor, ':supervisor2' => $supervisor));

	// Procesar resultados de ambas consultas
	if ($stmt->rowCount() > 0) {
		foreach ($stmt as $d) {
			$anio = $d['anio'];
			$pa = abiertas_subordinado($supervisor, $anio);
			// Acumulamos resultados de funcion2 en $data
			$data = array_merge($data, $pa);
		}
	}

	return $pa;
}

function abiertas_subordinado($supervisor, $anio)
{
	global $dbConnection;
	$data = array();

	$q = "SELECT
			base_evaluador.nombre_completo AS completo_evaluador,
			base_evaluador.nombre_grafica AS evaluador_grafica,
			base_evaluado.nombre_grafica AS evaluado_grafica,
			evaluador.tipo_evaluacion,
			evaluador.abiertas, base_evaluado.clave_registro 
		FROM
			CH_evaluacion_desempeno AS evaluador
			INNER JOIN CH_base_ev_desempeno AS base_evaluador ON evaluador.clave = base_evaluador.clave_registro
			INNER JOIN CH_base_ev_desempeno AS base_evaluado ON evaluador.clave_evaluada = base_evaluado.clave_registro 
		WHERE
			(base_evaluado.supervisor =:supervisor OR base_evaluado.nombre_completo =:supervisor2 ) 
			AND evaluador.estatus = 0 
			AND YEAR ( evaluador.fecha_alta ) =:anio
		GROUP BY
			evaluador.id";

	$stmt = $dbConnection->prepare($q);
	$stmt->execute(array(':supervisor' => $supervisor, ':supervisor2' => $supervisor, ':anio' => $anio));

	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {
			$completo_evaluador = utf8_encode($d['completo_evaluador']);
			$abiertas = utf8_decode($d['abiertas']);
			$evaluador_grafica = utf8_encode($d['evaluador_grafica']);
			$evaluado_grafica = utf8_encode($d['evaluado_grafica']);
			$clave = $d['clave_registro'];
			$tipo_evaluacion = $d['tipo_evaluacion'];

			$completo_evaluador = $evaluador_grafica; //$nombre . ' ' . $apellido;
			$completo_evaluado = $evaluado_grafica; //$nombre2 . ' ' . $apellido2;

			if ($tipo_evaluacion == 'Jefe' || $tipo_evaluacion == 'Subordinado') {
				$nombres = "A $completo_evaluado";
			} else {
				$nombres = "A $completo_evaluado";
			}
			$asignacion_evaluacion = asignacion_evaluacion($clave);
			if ($asignacion_evaluacion == 'VIGILANCIA' || $asignacion_evaluacion == 'INT Y MTTO') {
				$array = json_decode($abiertas, true);

				if (json_last_error() === JSON_ERROR_NONE) {
					// Verifica si existen ambas claves en el array y si son cadenas
					if (isset($array['15-0']) && isset($array['15-1']) && is_string($array['15-0']) && is_string($array['15-1'])) {
						// Intercambia los valores
						$temp = $array['15-0'];
						$array['15-0'] = $array['15-1'];
						$array['15-1'] = $temp;
					}

					// Convertir el array de vuelta a JSON
					$json_invertido = json_encode($array, JSON_UNESCAPED_UNICODE);

					// Mostrar el JSON invertido
					$abiertas = $json_invertido;
				}
			}

			$data[$n]['pregunta'] = $abiertas;
			$data[$n]['nombre'] = $nombres;

			$n++;
		}
	}
	foreach ($data as &$item) {
		// Decodificar JSON de la clave 'pregunta'
		$preguntas = json_decode($item["pregunta"], true);

		// Verificar si la decodificación fue exitosa
		if (is_array($preguntas)) {
			// Recorrer y modificar los valores no vacíos
			foreach ($preguntas as $key => $value) {
				if (!empty($value)) {
					$preguntas[$key] = $value . " (" . $item["nombre"] . ")";
				}
			}
			// Volver a codificar el JSON y asignarlo de nuevo a la clave 'pregunta'
			$item["pregunta"] = json_encode($preguntas);
		}
	}

	/* ------------------------------------ */
	$outputArray = [];

	if (is_array($data)) {
		foreach ($data as $entry) {
			$pregunta = json_decode($entry['pregunta'], true);

			if (is_array($pregunta)) {
				foreach ($pregunta as $key => $value) {
					if (!empty($value)) {
						if (!isset($outputArray[$key])) {
							$outputArray[$key] = [];
						}
						$outputArray[$key][] = $value;
					}
				}
			} else {
			}
		}
	}
	//$instrucciones = "Crea una redacción de retroalimentación de acuerdo a los comentarios ingresados, sin tomar en cuenta comentarios ofensivos.";
	//$ia = obtenerRespuestaChatGPT($outputArray, $instrucciones);
	return $outputArray;
}
function lista_subordinados($matricula)
{
	global $dbConnection;
	$subordinados = array();
	$supervisor = nombre_supervisor($matricula);
	$q = "SELECT
			CH_base_ev_desempeno.clave_registro,
			CH_base_ev_desempeno.nombre_grafica
		FROM
			CH_base_ev_desempeno			
		WHERE
			CH_base_ev_desempeno.supervisor LIKE '%$supervisor%'";
	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {
			$nombre_subordinado = utf8_encode($d['nombre_grafica']);
			$clave_registro = $d['clave_registro'];
			$subordinados[$n]['nombre_subordinado'] = $nombre_subordinado;
			$subordinados[$n]['clave_registro'] = $clave_registro;
			$n++;
		}
	}
	return $subordinados;
}
function grafica_general($matricula)
{
	global $dbConnection;
	$q = "SELECT YEAR
			( CH_evaluacion_desempeno.fecha_alta ) AS anio,
			CH_base_ev_desempeno.asignacion_evaluacion 
		FROM
			CH_evaluacion_desempeno
			INNER JOIN CH_base_ev_desempeno ON CH_evaluacion_desempeno.clave_evaluada = CH_base_ev_desempeno.clave_registro
		WHERE
			CH_evaluacion_desempeno.clave_evaluada = $matricula
			AND CH_evaluacion_desempeno.estatus = 0 
		GROUP BY
			YEAR ( CH_evaluacion_desempeno.fecha_alta ) 
		ORDER BY
			fecha_alta DESC";

	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$n = 0;
		foreach ($stmt as $d) {
			$anio = $d['anio'];
		}
	}
	$jefe = suma_secciones($matricula, 'Subordinado', $anio);
	$subordinados = suma_secciones($matricula, 'Jefe', $anio);
	$autoev = suma_secciones($matricula, utf8_encode('Autoevaluación'), $anio);


	$s = promedio_valores($subordinados);
	$j = promedio_valores($jefe);
	$a = promedio_valores($autoev);
	if (empty($s) && empty($j)) {
		$data['autoevaluacion'] = $a;
	} elseif (empty($a) && empty($s)) {
		$data['jefe'] = $j;
	} elseif (empty($a) && empty($j)) {
		$data['subordinados'] = $s;
	} elseif (empty($j)) {
		$data['subordinados'] = $s;
		$data['autoevaluacion'] = $a;
	} elseif (empty($s)) {
		$data['jefe'] = $j;
		$data['autoevaluacion'] = $a;
	} else {
		$data['subordinados'] = $s;
		$data['jefe'] = $j;
		$data['autoevaluacion'] = $a;
	}

	$keysToRemove = ['id', 'nombre_evaluador', 'quien_me_evaluo', 'anio', 'promedio_individual', 'promedio_total_jefe', 'promedio_autoev', 'promedio_total_subordinado'];

	foreach ($data as $key => &$value) {
		foreach ($value as &$innerArray) {
			if (is_array($innerArray)) {
				foreach ($keysToRemove as $keyToRemove) {
					unset($innerArray[$keyToRemove]);
				}
			}
		}
	}
	unset($data['subordinado']['promedio_total_subordinados']);
	unset($data['jefe']['promedio_total_jefe']);
	unset($data['autoevaluacion']['promedio_autoev']);

	return $data;
}

function promedio_valores($array)
{
	$promedios = [];
	if (is_array($array)) {
		// Recorrer cada subarray
		foreach ($array as $subArray) {
			// Verificar si cada $subArray es un array antes de procesarlo
			if (is_array($subArray)) {
				foreach ($subArray as $key => $value) {
					// Verificar si la key es numérica
					if (is_numeric($key)) {
						if (!isset($promedios[$key])) {
							$promedios[$key] = ['sum' => 0, 'count' => 0];
						}
						// Acumular los valores y contar solo los que no son 0
						if ($value != 0) {
							$promedios[$key]['sum'] += $value;
							$promedios[$key]['count']++;
						}
					}
				}
			}
		}
		// Calcular los promedios finales, incluyendo promedios de 0 si no hubo valores válidos
		$result = [];
		foreach ($promedios as $key => $data) {
			// Si no hay valores válidos, el promedio es 0
			if ($data['count'] == 0) {
				$result[$key] = 0;
			} else {
				$result[$key] = $data['sum'] / $data['count'];
			}
		}
	}
	return $result;
}

/* ==========COMENTARIOS CON IA=================== */

function realimentacion($clave_registro)
{
	global $dbConnection;
	$q = "SELECT comentario FROM comentarios_Ev_Desempeno WHERE clave_registro = $clave_registro ";

	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		foreach ($stmt as $d) {
			$comentario = $d['comentario'];
		}
	}

	return $comentario;
}

function tabla_realimentacion()
{
	global $dbConnection;
	$q = "SELECT
	comentarios_Ev_Desempeno.clave_registro, comentarios_Ev_Desempeno.nombre_grafica, comentarios_Ev_Desempeno.comentario
FROM
	comentarios_Ev_Desempeno
	INNER JOIN
	CH_base_ev_desempeno
	ON 
		comentarios_Ev_Desempeno.clave_registro = CH_base_ev_desempeno.clave_registro where CH_base_ev_desempeno.asignacion_evaluacion='DIR Y JEF' GROUP BY CH_base_ev_desempeno.clave_registro ORDER BY comentarios_Ev_Desempeno.id asc";

	$stmt = $dbConnection->prepare($q);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$tabla = "<table>
	<thead>
		<th>Clave de registro</th>
		<th>Nombre</th>
		<th>Comentario</th>
	</thead><tbody>
	";
		foreach ($stmt as $d) {
			$clave_registro = $d['clave_registro'];
			$nombre_grafica = $d['nombre_grafica'];
			$comentario = nl2br($d['comentario']);
			$result = str_replace("\\n", "\n", $comentario); 
			$result2 = str_replace("\ ", "", $result);
			$tabla .= "<tr>
				<td>$clave_registro</td>
				<td>$nombre_grafica</td>
				<td>$result2</td></tr>";
		}
	}
	$tabla .="</tbody></table";

	return $tabla;
}