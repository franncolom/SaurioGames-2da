<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 3600");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
	http_response_code(200);
	exit();
}

require_once "Database.php";
require_once "Usuario.php";
require_once "Partida.php";

$database = new Database();
$db = $database->connect();
$usuarios = new Usuario($db);
$partida = new Partida($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
	case 'GET':
		manejarGetUsuarios($usuarios);
		break;

	case 'POST':
		manejarPost($db, $usuarios, $partida);
		break;

	default:
		http_response_code(405);
		echo json_encode(["message" => "Método no permitido"]);
		break;
}

function manejarGetUsuarios($usuarios)
{
	// Si viene ?nombre=... busca por nombre o correo. Si no, lista todos
	if (isset($_GET['nombre'])) {
		$nombre = htmlspecialchars($_GET['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
		if ($nombre === '') {
			http_response_code(400);
			echo json_encode(["message" => "Nombre inválido"]);
			return;
		}

		$user = $usuarios->getByNombreUsuario($nombre);
		if (!$user) {
			$user = $usuarios->getByCorreo($nombre);
		}
		echo json_encode($user ?: ["message" => "Usuario no encontrado"]);
		return;
	}

	$stmt = $usuarios->getAll();
	echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

function manejarPost($db, $usuarios, $partida)
{
	$inputText = file_get_contents('php://input');
	$input = json_decode($inputText, true);
	if ($input === null) {
		http_response_code(400);
		echo json_encode(["message" => "JSON inválido"]);
		return;
	}

	// Acciones sencillas por querystring:
	// - ?action=login  => login de usuario por nombre o correo
	// - ?entity=partida => crear partida
	// - ?entity=usuarios => crear usuario
	$action = isset($_GET['action']) ? strtolower($_GET['action']) : null;
	$entity = isset($_GET['entity']) ? strtolower($_GET['entity']) : null;

	if ($action === 'login') {
		loginBasico($usuarios, $input);
		return;
	}

	if ($entity === 'usuarios') {
		crearUsuarioBasico($usuarios, $input);
		return;
	}

	if ($entity === 'partida') {
		crearPartidaBasica($db, $partida, $input);
		return;
	}

	http_response_code(400);
	echo json_encode(["message" => "Acción o entidad no soportada"]);
}

function loginBasico($usuarios, $input)
{
	try {
		if (!isset($input['nombre_usuario'], $input['contraseña'])) {
			http_response_code(400);
			echo json_encode(["message" => "Faltan credenciales"]);
			return;
		}

		$identifier = $input['nombre_usuario'];
		$plainPassword = $input['contraseña'];

		$user = $usuarios->getByNombreUsuario($identifier);
		if (!$user) {
			$user = $usuarios->getByCorreo($identifier);
		}

		if (!$user) {
			http_response_code(401);
			echo json_encode(["message" => "Usuario o contraseña incorrecta"]);
			return;
		}

		$stored = $user['contraseña'] ?? '';
		$loginOk = false;

		if ($stored !== '' && strlen($stored) >= 20) {
			$loginOk = password_verify($plainPassword, $stored);
			if ($loginOk && password_needs_rehash($stored, PASSWORD_DEFAULT)) {
				$usuarios->updatePasswordById($user['id_usuario'], password_hash($plainPassword, PASSWORD_DEFAULT));
			}
		} else {
			$loginOk = ($plainPassword === $stored);
			if ($loginOk) {
				$usuarios->updatePasswordById($user['id_usuario'], password_hash($plainPassword, PASSWORD_DEFAULT));
			}
		}

		if ($loginOk) {
			unset($user['contraseña']);
			echo json_encode($user);
		} else {
			http_response_code(401);
			echo json_encode(["message" => "Usuario o contraseña incorrecta"]);
		}
	} catch (Throwable $e) {
		http_response_code(500);
		echo json_encode(["message" => "Error interno", "error" => $e->getMessage()]);
	}
}

function crearUsuarioBasico($usuarios, $input)
{
	try {
		if (!isset($input['correo'], $input['nombre_usuario'], $input['contraseña'])) {
			http_response_code(400);
			echo json_encode(["message" => "Faltan campos requeridos"]);
			return;
		}
		$usuarios->correo = $input['correo'];
		$usuarios->nombre_usuario = $input['nombre_usuario'];
		$usuarios->contraseña = password_hash($input['contraseña'], PASSWORD_DEFAULT);

		if ($usuarios->create()) {
			echo json_encode(["message" => "Usuario creado"]);
		} else {
			http_response_code(500);
			echo json_encode(["message" => "Error al crear usuario"]);
		}
	} catch (Throwable $e) {
		http_response_code(500);
		echo json_encode(["message" => "Error interno", "error" => $e->getMessage()]);
	}
}

function crearPartidaBasica($db, $partida, $input)
{
	try {
		// Validaciones mínimas
		$puntaje = isset($input['puntaje_partida']) ? (int)$input['puntaje_partida'] : 0;
		$cant = isset($input['can_jugadores']) ? (int)$input['can_jugadores'] : 2;
		$estado = isset($input['estado']) ? (string)$input['estado'] : 'iniciada';

		$partida->puntaje_partida = $puntaje;
		$partida->can_jugadores = $cant;
		$partida->estado = $estado;

		if ($partida->create()) {
			$id = method_exists($db, 'lastInsertId') ? $db->lastInsertId() : null;
			http_response_code(201);
			echo json_encode(["message" => "Partida creada", "id" => $id]);
		} else {
			http_response_code(500);
			echo json_encode(["message" => "Error al crear partida"]);
		}
	} catch (Throwable $e) {
		http_response_code(500);
		echo json_encode(["message" => "Error interno", "error" => $e->getMessage()]);
	}
}
