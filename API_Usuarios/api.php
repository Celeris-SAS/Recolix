<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$config = [
'base_path' => '/recolix/API_Usuarios/api.php',
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
            'actions' => ['reglasValidacion', 'getSolicitudesPendientes', 'aprobar', 'rechazar'],
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

if (strpos($uri, $basePath) === 0) { // Si la ruta del usuario empieza en la posición 0
    // 1. Extraer segmentos de la URL (ej: /recolix/API_Gestion/contenedores/1 -> ['contenedores', '1'])
    $relativePath = substr($uri, strlen($basePath));
    $uriSegments  = explode('/', trim($relativePath, '/'));

    $resource = isset($uriSegments[0]) ? $uriSegments[0] : null;
    $id       = null;

    // Validar que el recurso exista en la configuración (seguridad básica)
    if (!$resource || !isset($config['resources'][$resource])) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Recurso no encontrado']);
        exit;
    }

    // 2. Cargar el archivo controlador correspondiente y crear una instancia de la clase
    $resourceConfig = $config['resources'][$resource];
    require_once __DIR__ . '/' . $resourceConfig['file'];

    $controllerClass = $resourceConfig['controller'];
    try {
        $controller = new $controllerClass(); // Instanciación dinámica
    } catch (Exception $e) {
        http_response_code(500); // Error de servidor si no se puede cargar la clase
        echo json_encode(['status' => 'error', 'message' => 'Error al inicializar controller: ' . $e->getMessage()]);
        exit;
    }

    // 3. Determinar qué acción específica se solicita y si hay un ID involucrado
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

    // Permitir pasar la acción como parámetro GET (?action=...)
    if (isset($_GET['action']) && $_GET['action']) {
        $action = $_GET['action'];
    }

    // 4. Ejecutar acción personalizada si existe en el controlador
    if ($action && isset($resourceConfig['actions']) && in_array($action, $resourceConfig['actions']) && method_exists($controller, $action)) {
        $callParams = [];
        if ($id) $callParams[] = $id;
        
        // Preparar parámetros dependiendo de si es GET o POST/PUT
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
            // Ejecutar el método dinámicamente con los parámetros recolectados
            $result = call_user_func_array([$controller, $action], $callParams);
            echo json_encode(['status' => 'success', 'data' => $result]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // 5. Procesar operaciones estándar según el método HTTP (GET, POST, PUT)
    switch ($method) {
        case 'GET':
            if ($id) {
                // Obtener un único elemento por ID
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
                // Listar todos los elementos
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
            // Crear un nuevo registro
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
            // Actualizar un registro existente
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
            http_response_code(405); // Método HTTP no soportado
            echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
            break;
    }

} else {
    // Si la URI no coincide con la base_path configurada
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Ruta base inválida. Use: ' . $config['base_path']]);
}