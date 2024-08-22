
Backup App is a simple web application that allows you to manage and create backups of projects on a server. The application is developed in PHP with MySQL as the database, and it uses the NES.css library to give it a retro design touch.

- Save project information along with comments and paths in the database.
- Compress the contents of a project into a ZIP file and store it in a specific folder.
- List all backups made with options to download them.
- Automatically delete old backups when a defined limit is exceeded.
- Retro design using the NES.css library.


1. Clone the repository to your local server.
2. Make sure you have a web server configured with PHP and MySQL.
3. Create a MySQL database and run the included SQL script to create the backups table.
4. Configure your database credentials in the PHP file.
5. Use `npm install nes.css` to install the NES.css style library or download the CSS from the provided CDN.
6. Access the application from your browser, add the path of a project, comments, and perform the backup.
