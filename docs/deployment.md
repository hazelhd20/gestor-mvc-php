# Guía de despliegue

## Actualización de comentarios por hito

La versión actual del gestor permite abrir conversaciones generales en cada hito, incluso antes de registrar la primera entrega. Para aplicar el cambio en instalaciones existentes:

1. Ejecuta el script de actualización para garantizar que la tabla `comments` tenga la columna `milestone_id` y que `submission_id` admita valores nulos:

   ```bash
   php scripts/upgrade_comments_table.php
   ```

   El script forzará la migración automática definida en `Comment::ensureTable()` y confirmará que la estructura quedó actualizada.

2. Despliega el código normalmente (sin pasos adicionales). Las nuevas plantillas detectarán automáticamente los comentarios previos y habilitarán el formulario permanente de feedback por hito.

> Si el script avisa que la columna aún no existe o que `submission_id` continúa siendo obligatorio, revisa manualmente la base de datos antes de continuar con el despliegue.
