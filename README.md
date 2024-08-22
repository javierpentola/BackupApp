**Descripción para GitHub:**

**Backup App**

**Descripción:**
Backup App es una aplicación web sencilla que permite gestionar y crear backups de proyectos en un servidor. La aplicación está desarrollada en PHP con MySQL como base de datos, y utiliza la librería NES.css para darle un toque retro al diseño.

**Características:**

- Guardar información de proyectos junto con comentarios y rutas en la base de datos.
- Comprimir el contenido de un proyecto en un archivo ZIP y almacenarlo en una carpeta específica.
- Listar todos los backups realizados con opciones para descargarlos.
- Eliminar backups antiguos automáticamente cuando se excede un límite definido.
- Diseño retro utilizando la librería NES.css.

**Cómo usar:**

1. Clona el repositorio en tu servidor local.
2. Asegúrate de tener configurado un servidor web con PHP y MySQL.
3. Crea una base de datos MySQL y ejecuta el script SQL incluido para crear la tabla de backups.
4. Configura las credenciales de tu base de datos en el archivo PHP.
5. Utiliza `npm install nes.css` para instalar la librería de estilos NES.css o descarga el CSS desde el CDN proporcionado.
6. Accede a la aplicación desde tu navegador, añade la ruta de un proyecto, comentarios y realiza el backup.
