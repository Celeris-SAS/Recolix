<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$config = [
    'base_path' => '/recolix/API_Gestion/',
    'resources' => [
        'contenedores' => [
            'singular'    => 'Contenedor',
            'controller'  => 'ContenedorController',
            'file'        => 'Controlador/contenedorController.php',
            'getAllMethod' => 'getAllContenedores',
            'getMethod'   => 'getContenedor',
            'insertMethod' => 'insertContenedor',
            'updateMethod' => 'updateContenedor',
            'deleteMethod' => 'bajaContenedor',
                'actions' => [
                'reglasValidacion',
                'cambiarEstado',
                'clasificarPorTipoResiduo',
                'asignarACircuito',
                'registrarReparacion',
                'getNivelLlenado',
                'getHistorial',
                'filtrarMunicipio',
                'filtrarTipoResiduo',
                'filtrarEstado',
                'filtrarCriticos'
            ],
            'filters' => [
                'municipio'   => 'filtrarMunicipio',
                'tipoResiduo' => 'filtrarTipoResiduo',
                'estado'      => 'filtrarEstado'
            ]
        ],
        'tipos-residuo' => [
            'singular'    => 'TipoResiduo',
            'controller'  => 'TipoResiduoController',
            'file'        => 'Controlador/tipoResiduoController.php',
            'getAllMethod' => 'getAllTiposResiduo',
            'getMethod'   => 'getTipoResiduo',
            'insertMethod' => 'insertTipoResiduo',
            'updateMethod' => 'updateTipoResiduo',
            'deleteMethod' => 'bajaTipoResiduo',
            'actions' => [
                'reglasValidacion',
                'filtrarReciclable'
            ],
            'filters' => [
                'reciclable' => 'filtrarReciclable'
            ]
        ],
        'predicciones' => [
            'singular'    => 'Prediccion',
            'controller'  => 'PrediccionLlenadoController',
            'file'        => 'Controlador/prediccionLlenadoController.php',
            'getAllMethod' => 'getAllPredicciones',
            'getMethod'   => 'getPrediccion',
            'insertMethod' => 'calcularPrediccion',
            'updateMethod' => null,
            'deleteMethod' => null,
            'actions' => [
                'calcularPrediccion',
                'getUltimaPrediccion',
                'getAllPredicciones'
            ],
            'filters' => []
        ],
        'centros-acopio' => [
            'singular'    => 'Centro',
            'controller'  => 'CentroAcopioController',
            'file'        => 'Controlador/centroAcopioController.php',
            'getAllMethod' => 'getAllCentros',
            'getMethod'   => 'getCentro',
            'insertMethod' => 'insertCentro',
            'updateMethod' => 'updateCentro',
            'deleteMethod' => 'bajaCentro',
            'actions' => [
                'reglasValidacion',
                'getOcupacion',
                'asignarTipoResiduo',
                'getResiduosPorCentro',
                'actualizarOcupacion',
                'filtrarMunicipio',
                'filtrarTipo',
                'filtrarEstado'
            ],
            'filters' => [
                'municipio' => 'filtrarMunicipio',
                'tipo'      => 'filtrarTipo',
                'estado'    => 'filtrarEstado'
            ]
        ],
        'incidencias' => [
            'singular'    => 'Incidencia',
            'controller'  => 'IncidenciaController',
            'file'        => 'Controlador/incidenciaController.php',
            'getAllMethod' => 'getAllIncidencias',
            'getMethod'   => 'getIncidencia',
            'insertMethod' => 'crearIncidencia',
            'updateMethod' => null,
            'deleteMethod' => null,
            'actions' => [
                'reglasValidacion',
                'listarUrgentes',
                'getActivasConUbicacion',
                'getContenedoresVinculados',
                'asignarCuadrilla',
                'cerrar',
                'filtrarEstado',
                'filtrarTipo',
                'filtrarPrioridad'
            ],
            'filters' => [
                'estado'    => 'filtrarEstado',
                'tipo'      => 'filtrarTipo',
                'prioridad' => 'filtrarPrioridad'
            ]
        ],
        'reportes' => [
            'singular'    => 'Reporte',
            'controller'  => 'ReporteController',
            'file'        => 'Controlador/reporteController.php',
            'getAllMethod' => 'getResumenGeneral',
            'getMethod'   => null,
            'insertMethod' => null,
            'updateMethod' => null,
            'deleteMethod' => null,
            'actions' => [
                'getResumenGeneral',
                'getDistribucionLlenado',
                'getIndicadores',
                'getZonasCriticas',
                'getVolumenPorTipoYPeriodo',
                'getIncidenciasDetalle',
                'getFrecuenciaRecoleccion',
                'getCapacidadVertederos',
                'getClasificacionResiduos',
                'getEvolucionUsuarios'
            ],
            'filters' => []
        ],
        'maquinaria' => [
            'singular'    => 'Maquinaria',
            'controller'  => 'MaquinariaController',
            'file'        => 'Controlador/maquinariaController.php',
            'getAllMethod' => 'getAllMaquinarias',
            'getMethod'   => 'getMaquinaria',
            'insertMethod' => 'insertMaquinaria',
            'updateMethod' => 'updateMaquinaria',
            'deleteMethod' => 'bajaMaquinaria',
            'actions' => [
                'reglasValidacion',
                'cambiarEstado',
                'obtenerPorCentro',
                'filtrarPorTipo',
                'filtrarPorEstado'
            ],
            'filters' => [
                'centro' => 'obtenerPorCentro',
                'tipo'   => 'filtrarPorTipo',
                'estado' => 'filtrarPorEstado'
            ]
        ]
    ]
];

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$basePath = rtrim($config['base_path'], '/');

if (strpos($uri, $basePath) === 0) {
    $relativePath = substr($uri, strlen($basePath));
    $uriSegments  = explode('/', trim($relativePath, '/'));

    if (isset($uriSegments[0])) {
        $resource = $uriSegments[0];
    } else {
        $resource = null;
    }
    $id= null;
    

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
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al inicializar controller: ' . $e->getMessage()]);
        exit;
    }

    $action = null;
    if (isset($uriSegments[1]) && !is_numeric($uriSegments[1])) {
        $action = $uriSegments[1];
    } else {
        $action = null;
    }
    if ($action) {
        if (isset($uriSegments[2])) {
            $id = $uriSegments[2];
        } else {
            $id = null;
        }
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
        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    switch ($method) {
        case 'GET':
            if ($id !== null) {
                try {
                    $result = $controller->{$resourceConfig['getMethod']}($id);
                    if ($result) {
                        echo json_encode(['status' => 'success', 'data' => $result]);
                    } else {
                        http_response_code(404);
                        echo json_encode(['status' => 'error', 'message' => $resourceConfig['singular'] . ' no encontrado']);
                    }
                } catch (Throwable $e) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                }
            } else {
                $query   = $_GET;
                unset($query['action']);
                $handled = false;

                if (!empty($query) && isset($resourceConfig['filters']) && is_array($resourceConfig['filters'])) {
                    foreach ($query as $qk => $qv) {
                        if (isset($resourceConfig['filters'][$qk])) {
                            $filterMethod = $resourceConfig['filters'][$qk];
                            if (method_exists($controller, $filterMethod)) {
                                try {
                                    if (is_array($qv)) {
                                        $result = call_user_func_array([$controller, $filterMethod], array_values($qv));
                                    } else {
                                        $result = $controller->{$filterMethod}($qv);
                                    }
                                    echo json_encode(['status' => 'success', 'data' => $result]);
                                } catch (Throwable $e) {
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
                    try {
                        $result = $controller->{$resourceConfig['getAllMethod']}();
                        echo json_encode(['status' => 'success', 'data' => $result]);
                    } catch (Throwable $e) {
                        http_response_code(400);
                        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                    }
                }
            }
            break;

        case 'POST':
            if (!$resourceConfig['insertMethod']) {
                http_response_code(405);
                echo json_encode(['status' => 'error', 'message' => 'Este recurso es de solo lectura']);
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
                    http_response_code(400);
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
                        $result = call_user_func_array(
                            [$controller, $resourceConfig['updateMethod']],
                            $params
                        );
                        echo json_encode(['status' => 'success', 'message' => $resourceConfig['singular'] . ' actualizado', 'data' => $result]);
                    } catch (Throwable $e) {
                        http_response_code(400);
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
                echo json_encode(['status' => 'error', 'message' => 'Este recurso no admite eliminación']);
            } else {
                try {
                    $result = $controller->{$resourceConfig['deleteMethod']}($id);
                    echo json_encode(['status' => 'success', 'message' => $resourceConfig['singular'] . ' dado de baja']);
                } catch (Throwable $e) {
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
