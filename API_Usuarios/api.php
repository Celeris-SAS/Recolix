<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$config = [
'base_path' => '/recolix/API_Usuarios/api.php/',    'resources' => [


        'solicitudes' => [
            'singular'    => 'SolicitudRegistro',
            'controller'  => 'SolicitudRegistroController',
            'file'        => 'Controlador/solicitudRegistroController.php',
            'getAllMethod' => null,
            'getMethod'   => null,
            'insertMethod' => 'crearSolicitud',
            'updateMethod' => null,
            'deleteMethod' => null,
            'actions' => ['reglasValidacion', 'getSolicitudesPendientes', 'aprobar', 'rechazar'],
            'filters' => []
        ],


        'usuarios' => [
            'singular'    => 'Usuario',
            'controller'  => 'UsuarioController',
            'file'        => 'Controlador/UsuarioController.php',
            'getAllMethod' => 'listarUsuarios',
            'getMethod'   => 'getPerfil',
            'insertMethod' => 'crearUsuario',
            'updateMethod' => 'updatePerfil',
            'deleteMethod' => 'eliminarCuenta',
            'actions' => [
                'reglasValidacion',
                'login',
                'logout',
                'cambiarContrasenia',
                'getIncidencias',
                'getNotificaciones',
                'marcarNotificacionLeida',
                'filtrarRol',
                'filtrarAcceso',
                'cambiarRol',
                'cambiarAcceso',
                'setMunicipio',
                'obtenerPorCuadrilla',
                'asignarCuadrilla',
                'quitarCuadrilla',
                'obtenerPorCentro',
                'asignarCentro',
                'quitarCentro'
            ],
            'filters' => []
        ],

        'cuadrillas' => [
            'singular'    => 'Cuadrilla',
            'controller'  => 'CuadrillaController',
            'file'        => 'Controlador/cuadrillaController.php',
            'getAllMethod' => 'getAllCuadrillas',
            'getMethod'   => 'getCuadrilla',
            'insertMethod' => 'insertCuadrilla',
            'updateMethod' => 'updateCuadrilla',
            'deleteMethod' => 'deleteCuadrilla',
            'actions' => [
                'reglasValidacion',
                'esDisponible',
                'filtrarEstado',
                'filtrarMunicipio',
                'filtrarDisponibilidad',
                'obtenerHistorial'
            ],
            'filters' => []
        ]

    ]
];

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); 

$basePath = $config['base_path']; 

// DEBUG TEMPORAL - borrar después
if (isset($_GET['debug'])) {
    $relativePath = (strpos($uri, $basePath) === 0) ? substr($uri, strlen($basePath)) : 'NO COINCIDE';
    $uriSegments = explode('/', trim($relativePath, '/'));
    echo json_encode([
        'uri_recibida' => $uri,
        'base_path_esperado' => $basePath,
        'coincide' => (strpos($uri, $basePath) === 0),
        'relativePath' => $relativePath,
        'uriSegments' => $uriSegments,
        'method' => $method,
        'resources_disponibles' => array_keys($config['resources'])
    ]);
    exit;
}

if (strpos($uri, $basePath) === 0) { 
    $relativePath = substr($uri, strlen($basePath));
    $uriSegments  = explode('/', trim($relativePath, '/'));

    $resource = isset($uriSegments[0]) ? $uriSegments[0] : null;
    $id       = null;

    if (!$resource || !isset($config['resources'][$resource])) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Recurso no encontrado']);
        exit;
    }

    $resourceConfig = $config['resources'][$resource];
    require_once __DIR__ . '/Controlador/AccesoDenegadoException.php';
    require_once __DIR__ . '/' . $resourceConfig['file'];

    $controllerClass = $resourceConfig['controller'];
    try {
        $controller = new $controllerClass(); 
    } catch (Throwable $e) {
        http_response_code(500); 
        echo json_encode(['status' => 'error', 'message' => 'Error al inicializar controller: ' . $e->getMessage()]);
        exit;
    }

    $action = null;
    $action = isset($uriSegments[1]) && !is_numeric($uriSegments[1]) ? $uriSegments[1] : null;
    if ($action) {
        $id = isset($uriSegments[2]) ? $uriSegments[2] : null;
    } else {
        if (isset($uriSegments[1]) && is_numeric($uriSegments[1])) {
            $id = $uriSegments[1];
            if (isset($uriSegments[2]) && !is_numeric($uriSegments[2])) {
                $action = $uriSegments[2];
            }
        }
    }

    if (isset($_GET['action']) && $_GET['action']) {
        $action = $_GET['action'];
    }

    if ($action && isset($resourceConfig['actions']) && in_array($action, $resourceConfig['actions']) && method_exists($controller, $action)) {
        $callParams = [];
        if ($id !== null) $callParams[] = $id;
        
        // Leer el body una sola vez
        $bodyRaw = file_get_contents('php://input');
        
        if ($method === 'GET') {
            $query = $_GET;
            unset($query['action']);
            if (!empty($query)) $callParams = array_merge($callParams, array_values($query));
        } else {
            $body = json_decode($bodyRaw, true);
            if (is_array($body) && !empty($body)) {
                $callParams = array_merge($callParams, array_values($body));
            } else {
                $query = $_GET;
                unset($query['action']);
                if (!empty($query)) $callParams = array_merge($callParams, array_values($query));
            }
        }
        
        // Debug de acción
        if (isset($_GET['debug'])) {
            echo json_encode([
                'action' => $action,
                'method' => $method,
                'callParams' => $callParams,
                'body_raw' => $bodyRaw,
                'body_parsed' => json_decode($bodyRaw, true),
                'inArray' => in_array($action, $resourceConfig['actions']),
                'methodExists' => method_exists($controller, $action)
            ]);
            exit;
        }

        try {
            $result = call_user_func_array([$controller, $action], $callParams);
            echo json_encode(['status' => 'success', 'data' => $result]);
        } catch (Throwable $e) {
            http_response_code($e instanceof AccesoDenegadoException ? 403 : 400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    } else {
        // Debug cuando acción no es válida
        if (isset($_GET['debug'])) {
            echo json_encode([
                'accion_no_valida' => true,
                'action' => $action,
                'resource' => $resource,
                'inArray' => $action ? in_array($action, $resourceConfig['actions']) : 'no hay action',
                'methodExists' => $action ? method_exists($controller, $action) : 'no hay action',
                'resourceConfig_actions' => $resourceConfig['actions'] ?? 'no actions'
            ]);
            exit;
        }
    }

    switch ($method) {
        case 'GET':
            if ($id !== null) {
                if (!$resourceConfig['getMethod']) {
                    http_response_code(405);
                    echo json_encode(['status' => 'error', 'message' => 'Este recurso no admite consulta por ID']);
                    break;
                }
                try {
                    $result = $controller->{$resourceConfig['getMethod']}($id);
                    if ($result) {
                        echo json_encode(['status' => 'success', 'data' => $result]);
                    } else {
                        http_response_code(404);
                        echo json_encode(['status' => 'error', 'message' => $resourceConfig['singular'] . ' no encontrado']);
                    }
                } catch (Throwable $e) {
                    http_response_code($e instanceof AccesoDenegadoException ? 403 : 400);
                    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                }
            } else {
                if (!$resourceConfig['getAllMethod']) {
                    http_response_code(405);
                    echo json_encode(['status' => 'error', 'message' => 'Este recurso no admite listado general']);
                    break;
                }
                try {
                    $result = $controller->{$resourceConfig['getAllMethod']}();
                    echo json_encode(['status' => 'success', 'data' => $result]);
                } catch (Throwable $e) {
                    http_response_code($e instanceof AccesoDenegadoException ? 403 : 400);
                    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                }
            }
            break;

        case 'POST':
            if (!$resourceConfig['insertMethod']) {
                http_response_code(405);
                echo json_encode(['status' => 'error', 'message' => 'Este recurso no admite creación directa']);
                break;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Datos inválidos o vacíos']);
            } else {
                try {
                    $result = call_user_func_array(
                        [$controller, $resourceConfig['insertMethod']],
                        array_values($data)
                    );
                    http_response_code(201);
                    echo json_encode(['status' => 'success', 'message' => $resourceConfig['singular'] . ' creado', 'data' => $result]);
                } catch (Throwable $e) {
                    http_response_code($e instanceof AccesoDenegadoException ? 403 : 400);
                    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                }
            }
            break;

        case 'PUT':
            if ($id === null) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'ID requerido']);
            } elseif (!$resourceConfig['updateMethod']) {
                http_response_code(405);
                echo json_encode(['status' => 'error', 'message' => 'Este recurso no admite modificación directa']);
            } else {
                $data = json_decode(file_get_contents('php://input'), true);
                if (!$data) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos o vacíos']);
                } else {
                    try {
                        $params = array_merge([$id], array_values($data));
                        $result = call_user_func_array([$controller, $resourceConfig['updateMethod']], $params);
                        echo json_encode(['status' => 'success', 'message' => $resourceConfig['singular'] . ' actualizado', 'data' => $result]);
                    } catch (Throwable $e) {
                        http_response_code($e instanceof AccesoDenegadoException ? 403 : 400);
                        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                    }
                }
            }
            break;

        case 'DELETE':
            if ($id === null) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'ID requerido']);
            } elseif (!$resourceConfig['deleteMethod']) {
                http_response_code(405);
                echo json_encode(['status' => 'error', 'message' => 'Este recurso no admite baja directa']);
            } else {
                try {
                    $result = $controller->{$resourceConfig['deleteMethod']}($id);
                    echo json_encode(['status' => 'success', 'message' => $resourceConfig['singular'] . ' dado de baja', 'data' => $result]);
                } catch (Throwable $e) {
                    http_response_code($e instanceof AccesoDenegadoException ? 403 : 400);
                    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                }
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
            break;
    }

} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Ruta base inválida. Use: ' . $config['base_path']]);
}