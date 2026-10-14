<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// ERR-22: los rechazos por restricciones de la base (clave foránea, duplicados) se muestran
// con un mensaje de negocio en lugar del texto técnico de MySQL.
function mensajeError(Throwable $e) {
    if ($e instanceof mysqli_sql_exception) {
        switch ((int) $e->getCode()) {
            case 1452:
                return 'El registro referenciado no existe. Verificá los datos ingresados.';
            case 1451:
                return 'No se puede eliminar o modificar porque tiene registros asociados.';
            case 1062:
                return 'Ya existe un registro con esos datos.';
        }
    }
    return $e->getMessage();
}

$config = [
    'base_path' => '/recolix/API_Recoleccion/',
    // Endpoints que pueden usarse sin ser personal interno (landing pública).
    // Formato: 'METODO recurso', 'METODO recurso/accion' o 'METODO recurso/:id'.
    'publicos' => [
        'GET camiones/obtenerEnOperativa',
    ],
    'resources' => [
        'camiones' => [
            'singular' => 'Camion',
            'controller' => 'CamionController',
            'file' => 'Controlador/camionController.php',
            'getAllMethod' => 'getAllCamiones',
            'getMethod' => 'getCamion',
            'insertMethod' => 'insertCamion',
            'updateMethod' => 'updateCamion',
            'deleteMethod' => 'deleteCamion',
            'rolesMetodo' => [
                'GET' => ['administrador', 'cuadrilla'],
                'POST' => ['administrador'],
                'PUT' => ['administrador', 'cuadrilla'],
                'DELETE' => ['administrador'],
            ],
            'rolesAccion' => [
                'reglasValidacion' => ['administrador', 'cuadrilla', 'operario'],
                'esDisponible' => ['administrador', 'cuadrilla'],
                'filtrarEstado' => ['administrador', 'cuadrilla'],
                'filtrarTipoCarga' => ['administrador', 'cuadrilla'],
                'filtrarDisponibilidad' => ['administrador', 'cuadrilla'],
                'filtrarMantenimiento' => ['administrador', 'cuadrilla'],
                'filtrarAnio' => ['administrador', 'cuadrilla'],
                'filtrarModelo' => ['administrador', 'cuadrilla'],
                'filtrarCapacidad' => ['administrador', 'cuadrilla'],
                'obtenerHistorial' => ['administrador', 'cuadrilla'],
                'obtenerEstados' => ['administrador', 'cuadrilla'],
            ],
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
                'obtenerHistorial',
                'obtenerEstados',
                'obtenerEnOperativa'
            ],
            'filters' => [
                'estado' => 'filtrarEstado',
                'tipoCarga' => 'filtrarTipoCarga',
                'anio' => 'filtrarAnio',
                'modelo' => 'filtrarModelo',
                'capacidad' => 'filtrarCapacidad'
            ]
        ],
        'traslados' => [
            'singular' => 'Traslado',
            'controller' => 'TrasladoController',
            'file' => 'Controlador/trasladoController.php',
            'getAllMethod' => 'getAllTrasladosResiduos',
            'getMethod' => 'getTrasladoResiduo',
            'insertMethod' => 'insertTrasladoResiduo',
            'updateMethod' => 'updateTrasladoResiduo',
            'deleteMethod' => 'deleteTrasladoResiduo',
            'rolesMetodo' => [
                'GET' => ['administrador', 'cuadrilla', 'operario'],
                'POST' => ['administrador', 'cuadrilla'],
                'PUT' => ['administrador'],
                'DELETE' => ['administrador'],
            ],
            'rolesAccion' => [
                'reglasValidacion' => ['administrador', 'cuadrilla', 'operario'],
                'obtenerPorCamion' => ['administrador', 'cuadrilla', 'operario'],
                'obtenerPorCentro' => ['administrador', 'cuadrilla', 'operario'],
                'filtrarPorFecha' => ['administrador', 'cuadrilla', 'operario'],
                'obtenerPesoTotal' => ['administrador', 'cuadrilla', 'operario'],
            ],
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
            'singular' => 'Mantenimiento',
            'controller' => 'MantenimientoController',
            'file' => 'Controlador/mantenimientoController.php',
            'getAllMethod' => 'getAllMantenimientos',
            'getMethod' => 'getMantenimiento',
            'insertMethod' => 'insertMantenimiento',
            'updateMethod' => 'updateMantenimiento',
            'deleteMethod' => 'deleteMantenimiento',
            'rolesMetodo' => [
                'GET' => ['administrador', 'cuadrilla'],
                'POST' => ['administrador', 'cuadrilla'],
                'PUT' => ['administrador'],
                'DELETE' => ['administrador'],
            ],
            'rolesAccion' => [
                'reglasValidacion' => ['administrador', 'cuadrilla'],
                'programar' => ['administrador'],
                'obtenerPorCamion' => ['administrador', 'cuadrilla'],
                'filtrarPorTipo' => ['administrador', 'cuadrilla'],
                'obtenerCostoTotal' => ['administrador'],
                'proximos' => ['administrador', 'cuadrilla'],
            ],
            'actions' => [
                'reglasValidacion',
                'programar',
                'obtenerPorCamion',
                'filtrarPorTipo',
                'obtenerCostoTotal',
                'proximos',
                'notificarDesperfecto'
            ],
            'filters' => [
                'camionId' => 'obtenerPorCamion',
                'tipo' => 'filtrarPorTipo'
            ]
        ],
        'rutas' => [
            'singular' => 'Ruta',
            'controller' => 'RutaController',
            'file' => 'Controlador/rutaController.php',
            'getAllMethod' => 'getAllRutas',
            'getMethod' => 'getRuta',
            'insertMethod' => 'insertRuta',
            'updateMethod' => 'updateRuta',
            'deleteMethod' => 'deleteRuta',
            'rolesMetodo' => [
                'GET' => ['administrador', 'cuadrilla'],
                'POST' => ['administrador'],
                'PUT' => ['administrador'],
                'DELETE' => ['administrador'],
            ],
            'rolesAccion' => [
                'reglasValidacion' => ['administrador', 'cuadrilla'],
                'obtenerPorCircuito' => ['administrador', 'cuadrilla'],
                'filtrarPorActividad' => ['administrador', 'cuadrilla'],
                'filtrarPorNombre' => ['administrador', 'cuadrilla'],
                'agregarContenedor' => ['administrador'],
                'quitarContenedor' => ['administrador'],
                'obtenerContenedores' => ['administrador', 'cuadrilla'],
                'asignarCuadrilla' => ['administrador'],
                'quitarCuadrilla' => ['administrador'],
                'obtenerCuadrillas' => ['administrador', 'cuadrilla'],
                'obtenerPorCuadrilla' => ['administrador', 'cuadrilla'],
                'obtenerLevantes' => ['administrador', 'cuadrilla'],
                'generarOptima' => ['administrador'],
                'obtenerResumen' => ['administrador', 'cuadrilla'],
                'obtenerCargaEstimada' => ['administrador', 'cuadrilla'],
                'obtenerHistorial' => ['administrador'],
            ],
            'actions' => [
                'reglasValidacion',
                'obtenerPorCircuito',
                'filtrarPorActividad',
                'filtrarPorNombre',
                'agregarContenedor',
                'quitarContenedor',
                'obtenerContenedores',
                'asignarCuadrilla',
                'quitarCuadrilla',
                'obtenerCuadrillas',
                'obtenerPorCuadrilla',
                'obtenerLevantes',
                'generarOptima',
                'obtenerResumen',
                'obtenerCargaEstimada',
                'obtenerHistorial'
            ],
            'filters' => [
                'circuitoId' => 'obtenerPorCircuito',
                'actividad' => 'filtrarPorActividad',
                'nombre' => 'filtrarPorNombre'
            ]
        ],
        'levantes' => [
            'singular' => 'Levante',
            'controller' => 'LevanteController',
            'file' => 'Controlador/levanteController.php',
            'getAllMethod' => 'getAllLevantes',
            'getMethod' => 'getLevante',
            'insertMethod' => 'planificar',
            'updateMethod' => 'updateLevante',
            'deleteMethod' => 'deleteLevante',
            'rolesMetodo' => [
                'GET' => ['administrador', 'cuadrilla'],
                'POST' => ['administrador'],
                'PUT' => ['administrador'],
                'DELETE' => ['administrador'],
            ],
            'rolesAccion' => [
                'reglasValidacion' => ['administrador', 'cuadrilla'],
                'obtenerPorRuta' => ['administrador', 'cuadrilla'],
                'obtenerCuadrillas' => ['administrador', 'cuadrilla'],
                'obtenerActivoPorCuadrilla' => ['administrador', 'cuadrilla'],
                'planificar' => ['administrador'],
                'iniciar' => ['administrador', 'cuadrilla'],
                'registrarContenedor' => ['administrador', 'cuadrilla'],
                'completar' => ['administrador', 'cuadrilla'],
                'cancelar' => ['administrador'],
                'obtenerResumen' => ['administrador', 'cuadrilla'],
                'obtenerPendientesVencidos' => ['administrador', 'cuadrilla'],
            ],
            'actions' => [
                'reglasValidacion',
                'obtenerPorRuta',
                'obtenerCuadrillas',
                'obtenerActivoPorCuadrilla',
                'planificar',
                'iniciar',
                'registrarContenedor',
                'completar',
                'cancelar',
                'obtenerResumen',
                'obtenerPendientesVencidos',
                'generarAlertaVencidos'
            ],
            'filters' => [
                'rutaId' => 'obtenerPorRuta'
            ]
        ],
        'circuitos' => [
            'singular' => 'Circuito',
            'controller' => 'CircuitoController',
            'file' => 'Controlador/circuitoController.php',
            'getAllMethod' => 'getAllCircuitos',
            'getMethod' => 'getCircuito',
            'insertMethod' => 'insertCircuito',
            'updateMethod' => 'updateCircuito',
            'deleteMethod' => 'deleteCircuito',
            'rolesMetodo' => [
                'GET' => ['administrador', 'cuadrilla'],
                'POST' => ['administrador'],
                'PUT' => ['administrador'],
                'DELETE' => ['administrador'],
            ],
            'rolesAccion' => [
                'reglasValidacion' => ['administrador', 'cuadrilla'],
                'obtenerPorMunicipio' => ['administrador', 'cuadrilla'],
                'filtrarPorTurno' => ['administrador', 'cuadrilla'],
                'filtrarPorActividad' => ['administrador', 'cuadrilla'],
                'filtrarPorFrecuencia' => ['administrador', 'cuadrilla'],
                'filtrarPorDia' => ['administrador', 'cuadrilla'],
                'obtenerRutas' => ['administrador', 'cuadrilla'],
                'obtenerContenedores' => ['administrador', 'cuadrilla'],
            ],
            'actions' => [
                'reglasValidacion',
                'obtenerPorMunicipio',
                'filtrarPorTurno',
                'filtrarPorActividad',
                'filtrarPorFrecuencia',
                'filtrarPorDia',
                'obtenerRutas',
                'obtenerContenedores'
            ],
            'filters' => [
                'municipioId' => 'obtenerPorMunicipio',
                'turno' => 'filtrarPorTurno',
                'actividad' => 'filtrarPorActividad',
                'frecuencia' => 'filtrarPorFrecuencia',
                'dia' => 'filtrarPorDia'
            ]
        ],
        'centros' => [
            'singular' => 'Centro',
            'controller' => 'CentroController',
            'file' => 'Controlador/centroController.php',
            'getAllMethod' => 'getAllCentros',
            'getMethod' => 'getCentro',
            'rolesMetodo' => [
                'GET' => ['administrador', 'cuadrilla', 'operario'],
            ],
            'rolesAccion' => [
                'filtrarOperativos' => ['administrador', 'cuadrilla', 'operario'],
            ],
            'actions' => [
                'filtrarOperativos'
            ]
        ],
    ]
];

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

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

    // Control de acceso: todo exige sesión de personal interno (administrador, cuadrilla u operario),
    // salvo los endpoints listados en $config['publicos'].
    $claveEndpoint = $method . ' ' . $resource . ($action ? '/' . $action : ($id !== null ? '/:id' : ''));
    $rolSesion     = $_SESSION['usuario']['rol'] ?? '';
    $esPersonalInterno = $rolSesion !== '' && $rolSesion !== 'vecino';
    if (!in_array($claveEndpoint, $config['publicos'], true)) {
        if (!$esPersonalInterno) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'No tenés permiso para acceder a este recurso.']);
            exit;
        }
        // Permisos por rol: lo que no figura en rolesAccion / rolesMetodo es solo del administrador.
        if ($action) {
            $requerido = $resourceConfig['rolesAccion'][$action] ?? ['administrador'];
        } else {
            $requerido = $resourceConfig['rolesMetodo'][$method] ?? ['administrador'];
        }
        if (!in_array('*', $requerido, true) && !in_array($rolSesion, $requerido, true)) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'No tenés permiso para acceder a este recurso.']);
            exit;
        }
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
                $query = $_GET; unset($query['action']);
                if (!empty($query)) $callParams = array_merge($callParams, array_values($query));
            }
        }

        try {
            $result = call_user_func_array([$controller, $action], $callParams);
            echo json_encode(['status' => 'success', 'data' => $result]);
        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => mensajeError($e)]);
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
                    echo json_encode(['status' => 'error', 'message' => mensajeError($e)]);
                }
            } else {
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
                                        $params = array_values($qv);
                                        $result = call_user_func_array([$controller, $filterMethod], $params);
                                    } else {
                                        $result = $controller->{$filterMethod}($qv);
                                    }
                                    echo json_encode(['status' => 'success', 'data' => $result]);
                                } catch (Throwable $e) {
                                    http_response_code(400);
                                    echo json_encode(['status' => 'error', 'message' => mensajeError($e)]);
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
                        echo json_encode(['status' => 'error', 'message' => mensajeError($e)]);
                    }
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
                    echo json_encode(['status' => 'success', 'message' => $resourceConfig['singular'] . ' creado', 'data' => $result]);
                } catch (Throwable $e) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => mensajeError($e)]);
                }
            }
            break;

        case 'PUT':
            if ($id === null) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'ID requerido']);
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
                        echo json_encode(['status' => 'error', 'message' => mensajeError($e)]);
                    }
                }
            }
            break;

        case 'DELETE':
            if ($id === null) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'ID requerido']);
            } else {
                try {
                    $result = $controller->{$resourceConfig['deleteMethod']}($id);
                    echo json_encode(['status' => 'success', 'message' => $resourceConfig['singular'] . ' eliminado']);
                } catch (Throwable $e) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => mensajeError($e)]);
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
