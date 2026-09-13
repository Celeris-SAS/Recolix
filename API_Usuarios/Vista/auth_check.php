<?php
/**
 * Verifica que haya una sesión activa y que el rol del usuario logueado
 * esté dentro de los roles permitidos para la vista actual.
 *
 * Uso: llamar DESPUÉS de session_start() y del require de i18n.php,
 * y ANTES de imprimir cualquier HTML.
 *
 * Ejemplo (cuadrilla.php):
 *   verificarAcceso(['cuadrilla']);
 *
 * Ejemplo si una vista es compartida por varios roles:
 *   verificarAcceso(['cuadrilla', 'administrador']);
