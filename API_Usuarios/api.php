<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$config = [
    'base_path' => '/recolix/App_Vecino/',
    'resources' => [

        // RF02 — Solicitud de registro de vecino (formulario de la Landing Page).
        // Solo acepta POST; el alta la completa el administrador desde su módulo.
        'solicitudes' => [
            'singular'    => 'SolicitudRegistro',
            'controller'  => 'SolicitudRegistroController',
            'file'        => 'Controlador/SolicitudRegistroController.php',
            'getAllMethod' => null,
            'getMethod'   => null,
            'insertMethod' => 'crearSolicitud',
            'updateMethod' => null,
            'deleteMethod' => null,
            'actions' => ['reglasValidacion'],
            'filters' => []
        ],

        // RF81, RF82, RF83, RF90, RF91, RF92 — Login, perfil y contraseña del vecino.
        'usuarios' => [
            'singular'    => 'Usuario',
            'controller'  => 'UsuarioController',
            'file'        => 'Controlador/UsuarioController.php',
            'getAllMethod' => null,
            'getMethod'   => 'getPerfil',
            'insertMethod' => null,
            'updateMethod' => 'updatePerfil',
            'deleteMethod' => null,
            'actions' => [
                'reglasValidacion',
                'login',
                'logout',
                'cambiarContrasenia',
                'getIncidencias',
                'getNotificaciones',
                'marcarNotificacionLeida'
            ],
            'filters' => []
        ]

    ]
];

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // El sistema analiza la url que escribió el usuario o que llamó la aplicación

$basePath = rtrim($config['base_path'], '/'); // Si la ruta termina en / se la borra

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
    require_once __DIR__ . '/' . $resourceConfig['file'];

    $controllerClass = $resourceConfig['controller'];
    try {
        $controller = new $controllerClass();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al inicializar controller: ' . $e->getMessage()]);
        exit;
    }

    // Determinar acción e id
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

    // Ejecutar acción si existe
    if ($action && isset($resourceConfig['actions']) && in_array($action, $resourceConfig['actions']) && method_exists($controller, $action)) {
        $callParams = [];
        if ($id) $callParams[] = $id;
        if ($method === 'GET') {
            $query = $_GET;
            unset($query['action']);
            if (!empty($query)) $callParams = array_merge($callParams, array_values($query));
        } else {
            $body = json_decode(file_get_contents('php://input'), true);
            if (is_array($body) && !empty($body)) {
                $callParams = array_merge($callParams, array_values($body));
            } else {
                $query = $_GET;
                unset($query['action']);
                if (!empty($query)) $callParams = array_merge($callParams, array_values($query));
            }
        }

        try {
            $result = call_user_func_array([$controller, $action], $callParams);
            echo json_encode(['status' => 'success', 'data' => $result]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // Procesar según método HTTP
    switch ($method) {
        case 'GET':
            if ($id) {
                if (!$resourceConfig['getMethod']) {
                    http_response_code(405);
                    echo json_encode(['status' => 'error', 'message' => 'Este recurso no admite consulta por ID']);
                    break;
                }
                $result = $controller->{$resourceConfig['getMethod']}($id);
                if ($result) {
                    echo json_encode(['status' => 'success', 'data' => $result]);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => $resourceConfig['singular'] . ' no encontrado']);
                }
            } else {
                if (!$resourceConfig['getAllMethod']) {
                    http_response_code(405);
                    echo json_encode(['status' => 'error', 'message' => 'Este recurso no admite listado general']);
                    break;
                }
                $result = $controller->{$resourceConfig['getAllMethod']}();
                echo json_encode(['status' => 'success', 'data' => $result]);
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
                } catch (Exception $e) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                }
            }
            break;

        case 'PUT':
            if (!$id) {
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
                    } catch (Exception $e) {
                        http_response_code(400);
                        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                    }
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
?>