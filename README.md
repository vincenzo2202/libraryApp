# LibraryApp

LibraryApp es una innovadora aplicación diseñada para facilitar la gestión integral de una biblioteca personal o profesional. Su objetivo principal es permitir a los usuarios añadir, buscar y administrar de manera eficiente su colección de libros y revistas.

La idea surgió de una necesidad real planteada por un amigo y colega apasionado por su colección literaria, quien buscaba una solución práctica para llevar un control detallado de sus tesoros bibliográficos. LibraryApp no solo organiza la colección, sino que también ayuda a calcular su valor real, brindando una herramienta valiosa para quienes desean cuidar y maximizar su inversión en literatura.

Con LibraryApp, gestionar tu biblioteca nunca ha sido tan sencillo y satisfactorio. Es más que una herramienta, es el aliado perfecto para los amantes de los libros y las revistas.

## Características

- Buscar libros y Revistas por título, autor o ISBN
- Registrar nuevas adquisiciones en la biblioteca personal
- Registrar préstamos y devoluciones de libros
- Gestión de usuarios y sus préstamos 
- Controla y actualiza el valor de la colección.

## Instalación

1. Clona el repositorio:
    ```bash
    git clone https://github.com/tu-usuario/libraryApp.git
    ```
2. Navega al directorio del proyecto:
    ```bash
    cd libraryApp
    ```
3. Instala las dependencias usando Composer:
    ```bash
    composer install
    ```

## Migrar la base de datos

1. Configura las variables de entorno en el archivo `.env.local`, asegurándote de que la configuración de la base de datos sea correcta.
2. Ejecuta las migraciones para crear las tablas necesarias:
    ```bash
    php bin/console doctrine:migrations:migrate
    ```
3. (Opcional) Si necesitas reiniciar la base de datos desde cero, ejecuta:
    ```bash
    php bin/console doctrine:database:drop --force
    php bin/console doctrine:database:create
    php bin/console doctrine:migrations:migrate
    ```
## Uso

1. Configura tu entorno. Copia el archivo `.env` y ajusta las variables necesarias:
    ```bash
    cp .env .env.local
    ```
2. Inicia el servidor de desarrollo:
    ```bash
    symfony server:start
    ``` 

## Contribuir

1. Haz un fork del proyecto
2. Crea una nueva rama (`git checkout -b feature/nueva-funcionalidad`)
3. Realiza tus cambios y haz commit (`git commit -am 'Añadir nueva funcionalidad'`)
4. Sube tus cambios (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

## Licencia

Este proyecto está licenciado bajo la Licencia MIT. Consulta el archivo [LICENSE](LICENSE) para más detalles.

## Contacto

Para contactar con Vincenzo Donnarumma :

<a href = "mailto:vincenzodonnarumma22@gmail.com"  target="_blank">
<img src="https://img.shields.io/badge/Gmail-C6362C?style=for-the-badge&logo=gmail&logoColor=white" target="_blank">
</a>
<a href="https://github.com/vincenzo2202"  target="_blank">
    <img src= "https://img.shields.io/badge/GitHub-100000?style=for-the-badge&logo=github&logoColor=white"  target="_blank"/>
</a>  
<a href="https://www.linkedin.com/in/vincenzo2202/" target="_blank">
<img src="https://img.shields.io/badge/-LinkedIn-%230077B5?style=for-the-badge&logo=linkedin&logoColor=white" target="_blank" >
</a> 

## Agradecimientos

Quiero expresar mi más sincero agradecimiento a Antonio Corraliza por su invaluable apoyo y contribuciones al desarrollo de LibraryApp. Su experiencia y compromiso han sido clave para el éxito de este proyecto. Más allá de ser un maestro ejemplar en esta hermosa carrera, ha llegado a ocupar un lugar especial como amigo. 

Para contactar con Antonio Corraliza:

<a href="https://github.com/antoniocorraliza">
<img src="https://img.shields.io/badge/github-24292F?style=for-the-badge&logo=github&logoColor=red" style="margin-right: 60px;" />
</a>

## Futuras Integraciones

Para mejorar la administración y el seguimiento de los préstamos entre los usuarios de la aplicación, estamos desarrollando las siguientes integraciones de backend:

- **API RESTful para préstamos**: Implementación de una API RESTful que permitirá gestionar los préstamos y devoluciones de libros de manera eficiente. 
- **Notificaciones por correo electrónico**: Envío automático de notificaciones por correo electrónico para recordar a los usuarios sobre los préstamos y devoluciones pendientes.
- **Historial de préstamos**: Registro detallado del historial de préstamos de cada usuario, permitiendo un seguimiento preciso de los libros prestados y devueltos.
- **Panel de administración**: Desarrollo de un panel de administración para que los bibliotecarios puedan gestionar fácilmente los préstamos, devoluciones y usuarios desde una interfaz centralizada.
 