<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Configuración parametrizable - Mapping de recursos a controllers
$config = [
    'base_path' => '/recolix/API_Recoleccion/',
    'resources' => [
        'camiones' => [
            'controller' => 'CamionController',
            'file' => 'Controlador/camionController.php',
            'getAllMethod' => 'getAllCamiones',
            'getMethod' => 'getCamion',
            'insertMethod' => 'insertCamion',
            'updateMethod' => 'updateCamion',
            'deleteMethod' => 'deleteCamion',
            'actions' => [
                'reglasValidacion',
                'esDisponible',
                'filtrarEstado',
                'filtrarTipoCarga',
                'filtrarDisponibilidad',
                'filtrarMantenimiento',
                'filtrarAnio',
                'filtrarModelo',
                'filtrarCapacidad',
                'obtenerHistorial'
            ],
            // filtros por query param: paramName => controllerMethod (null significa que no se pasa parámetro)
            'filters' => [
                'estado' => 'filtrarEstado',
                'tipoCarga' => 'filtrarTipoCarga',
                'disponibilidad' => 'filtrarDisponibilidad',
                'mantenimiento' => 'filtrarMantenimiento',
                'anio' => 'filtrarAnio',
                'modelo' => 'filtrarModelo',
                'capacidad' => 'filtrarCapacidad',
                'historial' => 'obtenerHistorial'
            ]
        ],
        'traslados' => [
            'controller' => 'TrasladoController',
            'file' => 'Controlador/trasladoController.php',
            'getAllMethod' => 'getAllTrasladosResiduos',
            'getMethod' => 'getTrasladoResiduo',
            'insertMethod' => 'insertTrasladoResiduo',
            'updateMethod' => 'updateTrasladoResiduo',
            'deleteMethod' => 'deleteTrasladoResiduo',
            'actions' => [
                'reglasValidacion',
                'obtenerPorCamion',
                'obtenerPorCentro',
                'filtrarPorFecha',
                'obtenerPesoTotal'
            ],
            'filters' => [
                'camionId' => 'obtenerPorCamion',
                'centroId' => 'obtenerPorCentro',
                'fecha' => 'filtrarPorFecha',
                'pesoTotal' => 'obtenerPesoTotal'
            ]
        ],
        'mantenimientos' => [
            'controller' => 'MantenimientoController',
            'file' => 'Controlador/mantenimientoController.php',
            'getAllMethod' => 'getAllMantenimientos',
            'getMethod' => 'getMantenimiento',
            'insertMethod' => 'insertMantenimiento',
            'updateMethod' => 'updateMantenimiento',
            'deleteMethod' => 'deleteMantenimiento',
            'actions' => [
                'reglasValidacion',
                'programar',
                'obtenerPorCamion',
                'filtrarPorTipo',
                'obtenerCostoTotal'
            ],
            'filters' => [
                'camionId' => 'obtenerPorCamion',
                'tipo' => 'filtrarPorTipo'
            ]
        ],
        'eventos' => [
            'controller' => 'EventoCamionController',
            'file' => 'Controlador/eventoCamionController.php',
            'getAllMethod' => 'getAllEventosCamion',
            'getMethod' => 'getEventoCamion',
            'insertMethod' => 'insertEventoCamion',
            'updateMethod' => 'updateEventoCamion',
            'deleteMethod' => 'deleteEventoCamion',
            'actions' => [
                'reglasValidacion',
                'obtenerPorCamion',
                'filtrarPorTipo',
                'obtenerEventosPorFecha'
            ],
            'filters' => [
                'camionId' => 'obtenerPorCamion',
                'tipoEvento' => 'filtrarPorTipo',
                'fecha' => 'obtenerEventosPorFecha'
            ]
        ]
    ]
];

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Obtener la ruta relativa después de la ruta base
$basePath = rtrim($config['base_path'], '/');
if (strpos($uri, $basePath) === 0) {
    $relativePath = substr($uri, strlen($basePath));
    $uriSegments = explode('/', trim($relativePath, '/'));
    
    $resource = isset($uriSegments[0]) ? $uriSegments[0] : null;
    $id = isset($uriSegments[1]) ? $uriSegments[1] : null;

    if (!$resource || !isset($config['resources'][$resource])) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Recurso no encontrado']);
        exit;
    }

    // Cargar el controller correspondiente
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

    // Determinar si la ruta contiene una acción: /resource/{action}/{id} o /resource/{id}/{action}
    $action = null;
    // recalcular segments para soportar acción como segundo segmento
    $action = isset($uriSegments[1]) && !is_numeric($uriSegments[1]) ? $uriSegments[1] : null;
    if ($action) {
        $id = isset($uriSegments[2]) ? $uriSegments[2] : null;
    } else {
        // si el segundo segmento es numérico, es el id; si el tercero existe y no es numérico, es una acción
        if (isset($uriSegments[1]) && is_numeric($uriSegments[1])) {
            $id = $uriSegments[1];
            if (isset($uriSegments[2]) && !is_numeric($uriSegments[2])) {
                $action = $uriSegments[2];
            }
        }
    }

    // permitir override de acción vía query parameter `action`
    if (isset($_GET['action']) && $_GET['action']) {
        $action = $_GET['action'];
    }

    // Si se solicita una acción y el controller la expone, llamarla directamente
    if ($action && isset($resourceConfig['actions']) && in_array($action, $resourceConfig['actions']) && method_exists($controller, $action)) {
        // construir parámetros: preferir ID como primer param, luego body (para POST/PUT) o query params (para GET)
        $callParams = [];
        if ($id) $callParams[] = $id;
        if ($method === 'GET') {
            $query = $_GET;
            unset($query['action']);
            // agregar valores de query
            if (!empty($query)) $callParams = array_merge($callParams, array_values($query));
        } else {
            $body = json_decode(file_get_contents('php://input'), true);
            if (is_array($body) && !empty($body)) {
                $callParams = array_merge($callParams, array_values($body));
            } else {
                // fallback a query params
                $query = $_GET; unset($query['action']);
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

    // Procesar la solicitud según el método HTTP
    switch ($method) {
        case 'GET':
            if ($id) {
                // GET con ID
                $result = $controller->{$resourceConfig['getMethod']}($id);
                if ($result) {
                    echo json_encode(['status' => 'success', 'data' => $result]);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => ucfirst(rtrim($resource, 's')) . ' no encontrado']);
                }
            } else {
                // GET sin ID (listar todos) — comprobar filtros por query params
                $query = $_GET;
                unset($query['action']);
                $handled = false;

                if (!empty($query) && isset($resourceConfig['filters']) && is_array($resourceConfig['filters'])) {
                    foreach ($query as $qk => $qv) {
                        if (isset($resourceConfig['filters'][$qk])) {
                            $filterMethod = $resourceConfig['filters'][$qk];
                            if (method_exists($controller, $filterMethod)) {
                                try {
                                    if (is_array($qv)) {
                                        // múltiples valores: pasar como parámetros
                                        $params = array_values($qv);
                                        $result = call_user_func_array([$controller, $filterMethod], $params);
                                    } else {
                                        $result = $controller->{$filterMethod}($qv);
                                    }
                                    echo json_encode(['status' => 'success', 'data' => $result]);
                                } catch (Exception $e) {
                                    http_response_code(400);
                                    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                                }
                                $handled = true;
                                break;
                            } else {
                                http_response_code(400);
                                echo json_encode(['status' => 'error', 'message' => 'Filtro no soportado por el controller']);
                                $handled = true;
                                break;
                            }
                        }
                    }
                }

                if (!$handled) {
                    $result = $controller->{$resourceConfig['getAllMethod']}();
                    echo json_encode(['status' => 'success', 'data' => $result]);
                }
            }
            break;

        case 'POST':
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
                    echo json_encode(['status' => 'success', 'message' => ucfirst(rtrim($resource, 's')) . ' creado', 'data' => $result]);
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
            } else {
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (!$data) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos o vacíos']);
                } else {
                    try {
                        // Agregar el ID al principio de los parámetros
                        $params = array_merge([$id], array_values($data));
                        $result = call_user_func_array(
                            [$controller, $resourceConfig['updateMethod']], 
                            $params
                        );
                        echo json_encode(['status' => 'success', 'message' => ucfirst(rtrim($resource, 's')) . ' actualizado', 'data' => $result]);
                    } catch (Exception $e) {
                        http_response_code(400);
                        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                    }
                }
            }
            break;

        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'ID requerido']);
            } else {
                try {
                    $result = $controller->{$resourceConfig['deleteMethod']}($id);
                    echo json_encode(['status' => 'success', 'message' => ucfirst(rtrim($resource, 's')) . ' eliminado']);
                } catch (Exception $e) {
                    http_response_code(400);
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
?>
