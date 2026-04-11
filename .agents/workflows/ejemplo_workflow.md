---
description: Ejemplo de Flujo de Trabajo (Workflow) para el proyecto
---
# Compilar Proyecto

Este es un archivo de ejemplo. Puedes editarlo para listar los comandos exactos o pasos lógicos que quieres automatizar de forma repetitiva en tu plataforma (como limpiar caché, reiniciar Docker, subir a producción, etc).

Ejemplo de pasos:
1. Ejecutar el comando para construir el Frontend en Docker: `docker compose exec app npm run build`.
2. Ejecutar caché en el servidor de base de datos.
3. Informar de la terminación de los procesos.
