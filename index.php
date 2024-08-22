<?php

$basedir = 'C:\Users\wfranklin\Documents\Repos\backups';

function getConnection() {
    $dsn = "mysql:host=localhost;dbname=wftutorials";
    $user = "root";
    $passwd = "";
    return new PDO($dsn, $user, $passwd);
}

function saveBackup($project, $location, $comment, $backupPath) {
    $conn = getConnection();
    $stmt = $conn->prepare("INSERT INTO backups (project, project_path, comment, backup_path) VALUES (?, ?, ?, ?)");
    $stmt->execute([$project, $location, $comment, $backupPath]);
    return $conn->lastInsertId();
}

function getBackupById($id) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT * FROM backups WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function getAllBackups() {
    try {
        $conn = getConnection();
        return $conn->query("SELECT * FROM backups ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function getUniqueBackups() {
    try {
        $conn = getConnection();
        return $conn->query("SELECT id, project, project_path FROM backups GROUP BY project ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function deleteOldBackups($limit) {
    $allBackups = getAllBackups();
    if (count($allBackups) > $limit) {
        foreach (array_slice($allBackups, $limit) as $backup) {
            $backupPath = $backup['backup_path'];
            if (file_exists($backupPath)) {
                unlink($backupPath);
            }
        }
    }
}

if (isset($_GET['download'])) {
    $backup = getBackupById($_GET['download']);
    $file = $backup["backup_path"];
    
    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        ob_clean();
        readfile($file);
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save-backup'])) {
    $project = $_POST['project'] ?? null;
    $path = $_POST['path'] ?? null;
    $comment = $_POST['comment'] ?? null;
    $backupId = $_POST['backup'] ?? null;

    if ($backupId) {
        $backup = getBackupById($backupId);
        $path = $backup["project_path"];
        $project = $backup["project"];
    }

    $zipFileName = $basedir . DIRECTORY_SEPARATOR . "backup_" . time() . ".zip";
    $zip = new ZipArchive();
    $zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($path) + 1);
            $zip->addFile($filePath, $relativePath);
        }
    }
    $zip->close();

    saveBackup($project, $path, $comment, $zipFileName);

    deleteOldBackups(5);
}

$backupOptions = getUniqueBackups();
$projectDetails = [
    'name' => 'Default Project',
    'description' => 'This is the default project for backups',
    'path' => '/default/path'
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Backup App</title>
    <link href="https://unpkg.com/nes.css/css/nes.min.css" rel="stylesheet" />
    <style>
        body {
            background: #2c3e50;
            font-family: Arial, Helvetica, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container, .backup-listing {
            background: #ecf0f1;
            border: 1px solid #bdc3c7;
            padding: 20px;
            width: 400px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .backup-listing-container {
            overflow: auto;
            height: 250px;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
        }
        .backup-listing-item {
            margin-bottom: 10px;
            padding: 10px;
            box-shadow: 2px 2px 5px rgba(0,0,0,0.1);
        }
        input, textarea, select, button {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            border: 1px solid #bdc3c7;
            box-shadow: 2px 2px 5px rgba(0,0,0,0.1);
        }
        button {
            background-color: #3498db;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            border: none;
        }
        .nes-container {
            margin: 20px;
        }
        .nes-input, .nes-textarea, .nes-select, .nes-btn {
            width: 100%;
            margin-bottom: 10px;
        }
        h2 {
            color: #3498db;
        }
        .nes-container.is-rounded {
            border-width: 2px;
        }
    </style>
</head>
<body>
    <div>
        <h2 class="nes-text is-primary" style="text-align: center;">Backup App</h2>
        <div class="nes-container is-rounded form-container">
            <h3 class="nes-text">Add a Backup</h3>
            <form method="post">
                <label>Choose a previous backup:</label>
                <div class="nes-select">
                    <select name="backup">
                        <option value="">--select backup--</option>
                        <?php foreach ($backupOptions as $backup): ?>
                            <option value="<?php echo $backup['id']; ?>" title="<?php echo $backup['project_path']; ?>">
                                <?php echo $backup['project']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <hr>
                <label>New Project:</label>
                <input type="text" name="project" class="nes-input" placeholder="Project Name" autocomplete="off" value="<?php echo $projectDetails['name']; ?>" />
                <label>Project Path:</label>
                <input type="text" name="path" class="nes-input" placeholder="Project Path" value="<?php echo $projectDetails['path']; ?>" />
                <label>Add Comment:</label>
                <textarea name="comment" class="nes-textarea" placeholder="Comment"><?php echo $projectDetails['description']; ?></textarea>
                <button name="save-backup" class="nes-btn is-primary">Save Backup</button>
            </form>
        </div>
        <div class="nes-container is-rounded backup-listing">
            <h3 class="nes-text">Listing of Backups</h3>
            <div class="backup-listing-container">
                <?php foreach (getAllBackups() as $backup): ?>
                    <div class="backup-listing-item nes-container is-rounded">
                        <p>
                            <strong>#<?php echo $backup['id']; ?> - <?php echo $backup['project']; ?></strong> (<?php echo $backup['project_path']; ?>)
                            <br>
                            <span><?php echo $backup['comment']; ?></span>
                            <br>
                            <a href="?download=<?php echo $backup['id']; ?>" class="nes-btn is-success">Download Backup</a>
                            <br>
                            <small>Created On: <?php echo $backup['created_at']; ?></small>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>
