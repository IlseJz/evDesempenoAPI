<?
/* header("Content-Type:application/json");
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1); */
$call=$_REQUEST['call'];
if(!empty($call))
{

	switch ($call) {
		case 'EvaluacionEmpleado':
			if (!empty($_REQUEST['apikey']) && !empty($_REQUEST['matricula_admin'])) //Verificamos parámetros
			{
				$apikey = $_REQUEST['apikey'];
				$hash_usuario_M = $_REQUEST['matricula_admin'];
				$validacion = valida_apiKey($apikey); // devuelve valido o invalido && $_REQUEST['administrativos'] !=''

				if ($validacion == 'valido') {
					//Cargamos la biblioteca de funciones
					require('data.php');
					//Corremos en $data la función debe devolver un array
					$data = evaluacion_empleado($hash_usuario_M);
					if (empty($data)) {
						response(200, "Data not found", NULL);
					} else {
						response(200, "Data Found", $data);
					}
				} else {
					response(400, "Invalid API Key", NULL);
				}
			} else {
				response(400, "Invalid Parameters", NULL);
			}
			break;
		
		case 'GuardarEvaluacion':
			if(!empty($_REQUEST['apikey']) && !empty($_REQUEST['clave']) && !empty($_REQUEST['tipo_evaluacion']) && !empty($_REQUEST['id_evaluado']) && !empty($_REQUEST['compromiso']) && !empty($_REQUEST['orientacion_cliente']) && !empty($_REQUEST['adaptabilidad_cambio']) && !empty($_REQUEST['comunicacion']) && !empty($_REQUEST['trabajo_equipo']) && !empty($_REQUEST['efectividad_eficiencia']) && !empty($_REQUEST['integridad']) )//Verificamos parámetros
			{
				$apikey=$_REQUEST['apikey'];
				$clave=$_REQUEST['clave'];	
				$tipo_evaluacion=$_REQUEST['tipo_evaluacion'];	
				$id_evaluado=$_REQUEST['id_evaluado'];	
				$compromiso=$_REQUEST['compromiso'];	
				$orientacion_cliente=$_REQUEST['orientacion_cliente'];	
				$adaptabilidad_cambio=$_REQUEST['adaptabilidad_cambio'];	
				$comunicacion=$_REQUEST['comunicacion'];	
				$trabajo_equipo=$_REQUEST['trabajo_equipo'];			
				$liderazgo=$_REQUEST['liderazgo'];	
				$efectividad_eficiencia=$_REQUEST['efectividad_eficiencia'];	
				$integridad=$_REQUEST['integridad'];	
				$planeacion_organizacion=$_REQUEST['planeacion_organizacion'];	
				$principales_logros=$_REQUEST['principales_logros'];	
				$sugerencias=$_REQUEST['sugerencias'];
				$usuario=$_REQUEST['usuario'];

				$innov_creatividad = $_REQUEST['innov_creatividad'];
				$proactividad = $_REQUEST['proactividad'];
				$pulcritud = $_REQUEST['pulcritud'];
				$dinamismo_energia = $_REQUEST['dinamismo_energia'];
				$puntualidad_asis = $_REQUEST['puntualidad_asis'];
				$hash = $_REQUEST['hash'];
				$ev_general = $_REQUEST['ev_general'];
				$abiertas = $_REQUEST['abiertas'];
				$clave_evaluada = $_REQUEST['clave_evaluada'];		
				$validacion=valida_apiKey($apikey);// devuelve valido o invalido && $_REQUEST['administrativos'] !=''
				
				if($validacion=='valido')
				{
					//Cargamos la biblioteca de funciones
					require('data.php');
					//Corremos en $data la función debe devolver un array
					$data =
					guardar_evaluacion($clave, $tipo_evaluacion, $id_evaluado, $compromiso, $orientacion_cliente, $adaptabilidad_cambio, $comunicacion, $trabajo_equipo, $liderazgo, $efectividad_eficiencia, $integridad, $planeacion_organizacion, $innov_creatividad, $proactividad, $pulcritud, $dinamismo_energia, $puntualidad_asis, $ev_general, $abiertas, $clave_evaluada); //$principales_logros, $sugerencias,
					if(empty($data))
					{
						response(200,"Data not found",NULL);
					}
					else{response(200,"Data Found",$data);}
				}
				else{response(400,"Invalid API Key",NULL);}
			}
			else{response(400,"Invalid Parameters",NULL);}
		break;
		case 'DetalleEvaluacion':
			if (!empty($_REQUEST['apikey']) && !empty($_REQUEST['id'])) //Verificamos parámetros
			{
				$apikey = $_REQUEST['apikey'];
				$id = $_REQUEST['id'];
				$validacion = valida_apiKey($apikey); // devuelve valido o invalido && $_REQUEST['administrativos'] !=''

				if ($validacion == 'valido') {
					//Cargamos la biblioteca de funciones
					require('data.php');
					//Corremos en $data la función debe devolver un array
					$data = detalle_evaluacion($id);
					if (empty($data)) {
						response(200, "Data not found", NULL);
					} else {
						response(200, "Data Found", $data);
					}
				} else {
					response(400, "Invalid API Key", NULL);
				}
			} else {
				response(400, "Invalid Parameters", NULL);
			}
			break;
		case 'DetalleEvDeptosEmpleados':
			if (!empty($_REQUEST['apikey']) && !empty($_REQUEST['departamento'])) //Verificamos parámetros
			{
				$apikey = $_REQUEST['apikey'];
				$departamento = $_REQUEST['departamento'];
				$razon_social = $_REQUEST['razon_social'];
				$tipo_evaluacion = $_REQUEST['tipo_evaluacion'];
				$validacion = valida_apiKey($apikey); // devuelve valido o invalido && $_REQUEST['administrativos'] !=''

				if ($validacion == 'valido') {
					//Cargamos la biblioteca de funciones
					require('data.php');
					//Corremos en $data la función debe devolver un array
					$data = detalle_ev_deptos_empleados($departamento, $razon_social, $tipo_evaluacion);
					if (empty($data)) {
						response(200, "Data not found", NULL);
					} else {
						response(200, "Data Found", $data);
					}
				} else {
					response(400, "Invalid API Key", NULL);
				}
			} else {
				response(400, "Invalid Parameters", NULL);
			}
			break;
		case 'PromedioEvaluacion':
			if (!empty($_REQUEST['apikey']) && !empty($_REQUEST['id'])) //Verificamos parámetros
			{
				$apikey = $_REQUEST['apikey'];
				$id = $_REQUEST['id'];
				$validacion = valida_apiKey($apikey); // devuelve valido o invalido && $_REQUEST['administrativos'] !=''

				if ($validacion == 'valido') {
					//Cargamos la biblioteca de funciones
					require('data.php');
					//Corremos en $data la función debe devolver un array
					$data = promedio_evaluacion($id);
					if (empty($data)) {
						response(200, "Data not found", NULL);
					} else {
						response(200, "Data Found", $data);
					}
				} else {
					response(400, "Invalid API Key", NULL);
				}
			} else {
				response(400, "Invalid Parameters", NULL);
			}
			break;
		case 'SeccionPreguntas':
			if (!empty($_REQUEST['apikey']) && !empty($_REQUEST['id'])) //Verificamos parámetros
			{
				$apikey = $_REQUEST['apikey'];
				$id = $_REQUEST['id'];
				$validacion = valida_apiKey($apikey); // devuelve valido o invalido && $_REQUEST['administrativos'] !=''

				if ($validacion == 'valido') {
					//Cargamos la biblioteca de funciones
					require('data.php');
					//Corremos en $data la función debe devolver un array
					$data = secciones_preguntas($id);
					if (empty($data)) {
						response(200, "Data not found", NULL);
					} else {
						response(200, "Data Found", $data);
					}
				} else {
					response(400, "Invalid API Key", NULL);
				}
			} else {
				response(400, "Invalid Parameters", NULL);
			}
			break;
		case 'SeccionPreguntasNueva':
			if (!empty($_REQUEST['apikey']) && !empty($_REQUEST['id'])) //Verificamos parámetros
			{
				$apikey = $_REQUEST['apikey'];
				$id = $_REQUEST['id'];
				$validacion = valida_apiKey($apikey); // devuelve valido o invalido && $_REQUEST['administrativos'] !=''

				if ($validacion == 'valido') {
					//Cargamos la biblioteca de funciones
					require('data.php');
					//Corremos en $data la función debe devolver un array
					$data = secciones_preguntas_nueva($id);
					if (empty($data)) {
						response(200, "Data not found", NULL);
					} else {
						response(200, "Data Found", $data);
					}
				} else {
					response(400, "Invalid API Key", NULL);
				}
			} else {
				response(400, "Invalid Parameters", NULL);
			}
			break;
		case 'ImagenCabecera':
			if (!empty($_REQUEST['apikey']) && !empty($_REQUEST['id'])) //Verificamos parámetros
			{
				$apikey = $_REQUEST['apikey'];
				$id = $_REQUEST['id'];
				$validacion = valida_apiKey($apikey); // devuelve valido o invalido && $_REQUEST['administrativos'] !=''

				if ($validacion == 'valido') {
					//Cargamos la biblioteca de funciones
					require('data.php');
					//Corremos en $data la función debe devolver un array
					$data = imagen_cabecera($id);
					if (empty($data)) {
						response(200, "Data not found", NULL);
					} else {
						response(200, "Data Found", $data);
					}
				} else {
					response(400, "Invalid API Key", NULL);
				}
			} else {
				response(400, "Invalid Parameters", NULL);
			}
			break;
		case 'ListEmpleados':
			if (!empty($_REQUEST['apikey'])) //Verificamos parámetros
			{
				$apikey = $_REQUEST['apikey'];
				$validacion = valida_apiKey($apikey); // devuelve valido o invalido && $_REQUEST['administrativos'] !=''

				if ($validacion == 'valido') {
					//Cargamos la biblioteca de funciones
					require('data.php');
					//Corremos en $data la función debe devolver un array
					$data = list_empleados();
					if (empty($data)) {
						response(200, "Data not found", NULL);
					} else {
						response(200, "Data Found", $data);
					}
				} else {
					response(400, "Invalid API Key", NULL);
				}
			} else {
				response(400, "Invalid Parameters", NULL);
			}
		break;
		case 'ListDepartamentos':
			if (!empty($_REQUEST['apikey'])) //Verificamos parámetros
			{
				$apikey = $_REQUEST['apikey'];
				$validacion = valida_apiKey($apikey); // devuelve valido o invalido && $_REQUEST['administrativos'] !=''

				if ($validacion == 'valido') {
					//Cargamos la biblioteca de funciones
					require('data.php');
					//Corremos en $data la función debe devolver un array
					$data = list_departamentos();
					if (empty($data)) {
						response(200, "Data not found", NULL);
					} else {
						response(200, "Data Found", $data);
					}
				} else {
					response(400, "Invalid API Key", NULL);
				}
			} else {
				response(400, "Invalid Parameters", NULL);
			}
			break;
		case 'DetalleEvaluacionDepartamentos':
			if (!empty($_REQUEST['apikey']) && !empty($_REQUEST['departamento'])) //Verificamos parámetros
			{
				$apikey = $_REQUEST['apikey'];
				$departamento = $_REQUEST['departamento'];
				$razon_social = $_REQUEST['razon_social'];
				$validacion = valida_apiKey($apikey); // devuelve valido o invalido && $_REQUEST['administrativos'] !=''

				if ($validacion == 'valido') {
					//Cargamos la biblioteca de funciones
					require('data.php');
					//Corremos en $data la función debe devolver un array
					$data = detalle_ev_deptos($departamento, $razon_social);
					if (empty($data)) {
						response(200, "Data not found", NULL);
					} else {
						response(200, "Data Found", $data);
					}
				} else {
					response(400, "Invalid API Key", NULL);
				}
			} else {
				response(400, "Invalid Parameters", NULL);
			}
			break;
		case 'PromedioEvaluacionDepartamentos':
			if (!empty($_REQUEST['apikey']) && !empty($_REQUEST['departamento'])) //Verificamos parámetros
			{
				$apikey = $_REQUEST['apikey'];
				$departamento = $_REQUEST['departamento'];
				$razon_social = $_REQUEST['razon_social'];
				$validacion = valida_apiKey($apikey); // devuelve valido o invalido && $_REQUEST['administrativos'] !=''

				if ($validacion == 'valido') {
					//Cargamos la biblioteca de funciones
					require('data.php');
					//Corremos en $data la función debe devolver un array
					$data = promedio_evaluacion_deptos($departamento, $razon_social);
					if (empty($data)) {
						response(200, "Data not found", NULL);
					} else {
						response(200, "Data Found", $data);
					}
				} else {
					response(400, "Invalid API Key", NULL);
				}
			} else {
				response(400, "Invalid Parameters", NULL);
			}
			break;
		case 'CantidadEvaluaciones':
			if (!empty($_REQUEST['apikey']) && !empty($_REQUEST['departamento'])) //Verificamos parámetros
			{
				$apikey = $_REQUEST['apikey'];
				$departamento = $_REQUEST['departamento'];
				$validacion = valida_apiKey($apikey); // devuelve valido o invalido && $_REQUEST['administrativos'] !=''

				if ($validacion == 'valido') {
					//Cargamos la biblioteca de funciones
					require('data.php');
					//Corremos en $data la función debe devolver un array
					$data = cantidad_evaluaciones($departamento);
					if (empty($data)) {
						response(200, "Data not found", NULL);
					} else {
						response(200, "Data Found", $data);
					}
				} else {
					response(400, "Invalid API Key", NULL);
				}
			} else {
				response(400, "Invalid Parameters", NULL);
			}
		break;
		case 'PreguntasAbiertasDepto':
			if (!empty($_REQUEST['apikey']) && !empty($_REQUEST['departamento'])) //Verificamos parámetros
			{
				$apikey = $_REQUEST['apikey'];
				$departamento = $_REQUEST['departamento'];
				$razon_social = $_REQUEST['razon_social'];
				$validacion = valida_apiKey($apikey); // devuelve valido o invalido && $_REQUEST['administrativos'] !=''

				if ($validacion == 'valido') {
					//Cargamos la biblioteca de funciones
					require('data.php');
					//Corremos en $data la función debe devolver un array
					$data = preguntas_abiertas_depto($departamento, $razon_social);
					if (empty($data)) {
						response(200, "Data not found", NULL);
					} else {
						response(200, "Data Found", $data);
					}
				} else {
					response(400, "Invalid API Key", NULL);
				}
			} else {
				response(400, "Invalid Parameters", NULL);
			}
		break;
		case 'GraficaCompetenciasSubordinados':
			if (!empty($_REQUEST['apikey']) && !empty($_REQUEST['matricula'])) //Verificamos parámetros
			{
				$apikey = $_REQUEST['apikey'];
				$matricula = $_REQUEST['matricula'];
				$validacion = valida_apiKey($apikey); // devuelve valido o invalido && $_REQUEST['administrativos'] !=''

				if ($validacion == 'valido') {
					//Cargamos la biblioteca de funciones
					require('data.php');
					//Corremos en $data la función debe devolver un array
					$data = grafica_por_competencias($matricula);
					if (empty($data)) {
						response(200, "Data not found", NULL);
					} else {
						response(200, "Data Found", $data);
					}
				} else {
					response(400, "Invalid API Key", NULL);
				}
			} else {
				response(400, "Invalid Parameters", NULL);
			}
		break;
		case 'GraficaGeneralSubordinados':
			if (!empty($_REQUEST['apikey']) && !empty($_REQUEST['matricula'])) //Verificamos parámetros
			{
				$apikey = $_REQUEST['apikey'];
				$matricula = $_REQUEST['matricula'];
				$validacion = valida_apiKey($apikey); // devuelve valido o invalido && $_REQUEST['administrativos'] !=''

				if ($validacion == 'valido') {
					//Cargamos la biblioteca de funciones
					require('data.php');
					//Corremos en $data la función debe devolver un array
					$data = grafica_general_empleados($matricula);
					if (empty($data)) {
						response(200, "Data not found", NULL);
					} else {
						response(200, "Data Found", $data);
					}
				} else {
					response(400, "Invalid API Key", NULL);
				}
			} else {
				response(400, "Invalid Parameters", NULL);
			}
		break;
		case 'PromedioSubordinados':
			if (!empty($_REQUEST['apikey']) && !empty($_REQUEST['matricula'])) //Verificamos parámetros
			{
				$apikey = $_REQUEST['apikey'];
				$matricula = $_REQUEST['matricula'];
				$validacion = valida_apiKey($apikey); // devuelve valido o invalido && $_REQUEST['administrativos'] !=''

				if ($validacion == 'valido') {
					//Cargamos la biblioteca de funciones
					require('data.php');
					//Corremos en $data la función debe devolver un array
					$data = promedio_evaluacion_subordinados($matricula);
					if (empty($data)) {
						response(200, "Data not found", NULL);
					} else {
						response(200, "Data Found", $data);
					}
				} else {
					response(400, "Invalid API Key", NULL);
				}
			} else {
				response(400, "Invalid Parameters", NULL);
			}
		break;
		case 'PreguntasSubordinados':
			if (!empty($_REQUEST['apikey']) && !empty($_REQUEST['matricula'])) //Verificamos parámetros
			{
				$apikey = $_REQUEST['apikey'];
				$matricula = $_REQUEST['matricula'];
				$validacion = valida_apiKey($apikey); // devuelve valido o invalido && $_REQUEST['administrativos'] !=''

				if ($validacion == 'valido') {
					//Cargamos la biblioteca de funciones
					require('data.php');
					//Corremos en $data la función debe devolver un array
					$data = preguntas_abiertas_subordinado($matricula);
					if (empty($data)) {
						response(200, "Data not found", NULL);
					} else {
						response(200, "Data Found", $data);
					}
				} else {
					response(400, "Invalid API Key", NULL);
				}
			} else {
				response(400, "Invalid Parameters", NULL);
			}
		break;
		case 'ListaSubordinados':
			if (!empty($_REQUEST['apikey']) && !empty($_REQUEST['matricula'])) //Verificamos parámetros
			{
				$apikey = $_REQUEST['apikey'];
				$matricula = $_REQUEST['matricula'];
				$validacion = valida_apiKey($apikey); // devuelve valido o invalido && $_REQUEST['administrativos'] !=''

				if ($validacion == 'valido') {
					//Cargamos la biblioteca de funciones
					require('data.php');
					//Corremos en $data la función debe devolver un array
					$data = lista_subordinados($matricula);
					if (empty($data)) {
						response(200, "Data not found", NULL);
					} else {
						response(200, "Data Found", $data);
					}
				} else {
					response(400, "Invalid API Key", NULL);
				}
			} else {
				response(400, "Invalid Parameters", NULL);
			}
		break;
		case 'GraficaGeneralDimensiones':
			if (!empty($_REQUEST['apikey']) && !empty($_REQUEST['matricula'])) //Verificamos parámetros
			{
				$apikey = $_REQUEST['apikey'];
				$matricula = $_REQUEST['matricula'];
				$validacion = valida_apiKey($apikey); // devuelve valido o invalido && $_REQUEST['administrativos'] !=''

				if ($validacion == 'valido') {
					//Cargamos la biblioteca de funciones
					require('data.php');
					//Corremos en $data la función debe devolver un array
					$data = grafica_general($matricula);
					if (empty($data)) {
						response(200, "Data not found", NULL);
					} else {
						response(200, "Data Found", $data);
					}
				} else {
					response(400, "Invalid API Key", NULL);
				}
			} else {
				response(400, "Invalid Parameters", NULL);
			}
			break;
		case 'ComentarioRealimentacion':
			if (!empty($_REQUEST['apikey']) && !empty($_REQUEST['matricula'])) //Verificamos parámetros
			{
				$apikey = $_REQUEST['apikey'];
				$matricula = $_REQUEST['matricula'];
				$validacion = valida_apiKey($apikey); // devuelve valido o invalido && $_REQUEST['administrativos'] !=''

				if ($validacion == 'valido') {
					//Cargamos la biblioteca de funciones
					require('data.php');
					//Corremos en $data la función debe devolver un array
					$data = realimentacion($matricula);
					if (empty($data)) {
						response(200, "Data not found", NULL);
					} else {
						response(200, "Data Found", $data);
					}
				} else {
					response(400, "Invalid API Key", NULL);
				}
			} else {
				response(400, "Invalid Parameters", NULL);
			}
			break;
		default:
			response(400,"Invalid Call",NULL);
			break;
	}

}
else
{
	exit;
}






/*//////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////*/

//Función de transformación de respuesta array ajson
function response($status,$status_message,$data)
{
	header("HTTP/1.1 ".$status);

	$response['status']=$status;
	$response['status_message']=$status_message;
	$response['data']=$data;

	$json_response = json_encode($response);
	echo $json_response;
}
//Funcion de validación de token
function valida_token($token,$source)// devuelve valido o invalido
{
	$dbConnection=new PDO('mysql:dbname=udlacademia;host=localhost;','ortseam','139floz');
	$dbConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
	$dbConnection->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

	$q = "select id from APP_admin_tokens where token=:token and estatus='0' limit 1;";
	$stmt=$dbConnection->prepare($q);
	$stmt->execute(array(':token'=>$token,':source'=>$source));
	if($stmt->rowCount() > 0)
	{
		$n=0;
		foreach ($stmt as $d)
		{
			$id=$d['id'];
			$rv='valido';
		}
	}else
	{
		$rv='invalido';
	}

	return $rv;
}
//Funcion de validación de api key
function valida_apiKey($apikey)// devuelve valido o invalido
{
	$dbConnection=new PDO('mysql:dbname=udlacademia;host=localhost;','ortseam','139floz');
	$dbConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
	$dbConnection->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

	$q = "select id from SEAC_API_keys where api_key=:apikey and estatus='0' and disponible='0' limit 1;";
	$stmt=$dbConnection->prepare($q);
	$stmt->execute(array(':apikey'=>$apikey));
	if($stmt->rowCount() > 0)
	{
		$n=0;
		foreach ($stmt as $d)
		{
			$id=$d['id'];
			$rv='valido';
		}
	}else
	{
		$rv='invalido';
	}

	return $rv;
}

?>
