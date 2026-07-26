El sistema se organiza en base a 3 APIs, ya que la letra del proyecto exige estructurar el código de esta manera. Las 3 APIs son:
	API_Usuarios — autenticación, perfiles, roles y solicitudes de alta de vecinos.
	API_Gestion — contenedores, tipos de residuo y predicciones de llenado.
	API_Recoleccion — camiones, traslados de residuos y mantenimientos.
Esta separación responde a que el sistema tiene tres responsabilidades bien
distintas que casi no comparten reglas de negocio entre sí. Nos permitió
además repartir el trabajo en equipo por módulo sin pisarnos el código, y
que cada API pueda evolucionar de forma independiente 

**Para probar el sistema en primera instancia, se debe abrir el archivo crear_admin_test.php y utilizar esas credenciales en el login del portal de empleados.**

Cada API tiene un archivo (api.php) que utiliza una tabla de configuración
($config['resources']), en vez de tener un archivo o un switch por cada
endpoint. La tabla declara, por recurso, qué controller usar y qué método
corresponde a cada operación (alta, baja, modificación, consulta, filtros,
acciones de negocio).
De esta manera, cada vez que se agregue algo nuevo, se agrega como recurso y no es necesario modificar el switch y agregar más código.


Los controladores no ejecutan SQL: validan tipos, rangos y reglas de
negocio, y delegan en el modelo correspondiente, que solo se comunica
con la base de datos. Algunas decisiones de diseño que valen la pena remarcar son:
Un contenedor en estado en_deposito no tiene calle, número ni
coordenadas porque todavía no está instalado en la vía pública según el RF27.
Solo se puede registrar una reparación si el contenedor está en estado roto según el RF26.
Antes de asignar un tipo de residuo o un circuito a un contenedor, se
verifica que ese tipo/circuito exista.


Cada controller expone un método reglasvalidacion() que devuelve, en
JSON, los límites y tipos esperados de cada campo (mínimos, máximos,
valores permitidos). La Vista puede pedir esas reglas antes de mostrar un
formulario, en vez de que dupliquemos a mano en JavaScript los mismos
límites que ya están en PHP.


bajacontenedor no borra la fila: actualiza el estado a inactivo. Lo
mismo aplica a los usuarios. Un contenedor tiene historial
(incidencias, levantes, reparaciones) y un usuario tiene solicitudes y
acciones asociadas; borrarlo físicamente rompería esas referencias y
perderíamos trazabilidad. Preferimos marcar como inactivo y filtrar en las
consultas.


En términos de seguridad, lo que se debe tener en cuenta es:
Todas las consultas usan sentencias preparadas, nunca concatenamos input del usuario en el SQL.
Las contraseñas se guardan hasheadas y se verifican con passwordverify(), nunca en texto plano.
El acceso al Backoffice valida rol y estado de habilitación en cada
request (autenticacion.php).


Revisamos que cada requisito funcional (RF) del enunciado esté efectivamente implementado en el código, y evitamos documentar en el README funcionalidades que el sistema todavía no cubre, para que el alcance declarado coincida con el alcance real.
