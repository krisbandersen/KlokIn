<?php 
	require_once 'php/utils.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta name="csrf_token" content="<?php echo createToken(); ?>" />

<meta name='viewport' content='width=device-width, initial-scale=1.0, viewport-fit=cover'>
<title>KlokIn - Valider email</title>
<link rel='stylesheet' href='/style.css'>
<link rel='manifest' href='/site.webmanifest'>

<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="KlokIn">

<link rel="icon" type="image/png" sizes="196x196" href="favicon-196.png">
<link rel="apple-touch-icon" href="apple-icon-180.png">
<meta name="apple-mobile-web-app-capable" content="yes">
<link rel="apple-touch-startup-image" href="apple-splash-2048-2732.jpg" media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="apple-splash-2732-2048.jpg" media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
<link rel="apple-touch-startup-image" href="apple-splash-1668-2388.jpg" media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="apple-splash-2388-1668.jpg" media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
<link rel="apple-touch-startup-image" href="apple-splash-1536-2048.jpg" media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="apple-splash-2048-1536.jpg" media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
<link rel="apple-touch-startup-image" href="apple-splash-1488-2266.jpg" media="(device-width: 744px) and (device-height: 1133px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="apple-splash-2266-1488.jpg" media="(device-width: 744px) and (device-height: 1133px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
<link rel="apple-touch-startup-image" href="apple-splash-1640-2360.jpg" media="(device-width: 820px) and (device-height: 1180px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="apple-splash-2360-1640.jpg" media="(device-width: 820px) and (device-height: 1180px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
<link rel="apple-touch-startup-image" href="apple-splash-1668-2224.jpg" media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="apple-splash-2224-1668.jpg" media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
<link rel="apple-touch-startup-image" href="apple-splash-1620-2160.jpg" media="(device-width: 810px) and (device-height: 1080px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="apple-splash-2160-1620.jpg" media="(device-width: 810px) and (device-height: 1080px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
<link rel="apple-touch-startup-image" href="apple-splash-1290-2796.jpg" media="(device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="apple-splash-2796-1290.jpg" media="(device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
<link rel="apple-touch-startup-image" href="apple-splash-1179-2556.jpg" media="(device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="apple-splash-2556-1179.jpg" media="(device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
<link rel="apple-touch-startup-image" href="apple-splash-1284-2778.jpg" media="(device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="apple-splash-2778-1284.jpg" media="(device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
<link rel="apple-touch-startup-image" href="apple-splash-1170-2532.jpg" media="(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="apple-splash-2532-1170.jpg" media="(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
<link rel="apple-touch-startup-image" href="apple-splash-1125-2436.jpg" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="apple-splash-2436-1125.jpg" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
<link rel="apple-touch-startup-image" href="apple-splash-1242-2688.jpg" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="apple-splash-2688-1242.jpg" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
<link rel="apple-touch-startup-image" href="apple-splash-828-1792.jpg" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="apple-splash-1792-828.jpg" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
<link rel="apple-touch-startup-image" href="apple-splash-1242-2208.jpg" media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="apple-splash-2208-1242.jpg" media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
<link rel="apple-touch-startup-image" href="apple-splash-750-1334.jpg" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="apple-splash-1334-750.jpg" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
<link rel="apple-touch-startup-image" href="apple-splash-640-1136.jpg" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="apple-splash-1136-640.jpg" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
</head>
<body>
	<?php
		if(isset($_GET['id']) && $_GET['id'] !== '' && isset($_GET['hash']) && $_GET['hash'] !== '') {
			echo '<div class="message">';
			$C = connect();
			if($C) {
				$res = sqlSelect($C, 'SELECT user,hash,timestamp FROM requests WHERE id=? AND type=0', 'i', $_GET['id']);
				if($res && $res->num_rows === 1) {
					$request = $res->fetch_assoc();
					if($request['timestamp'] > time() - 60*60*24) {
						if(password_verify(urlSafeDecode($_GET['hash']), $request['hash'])) {
							if(sqlUpdate($C, 'UPDATE users SET verified=1 WHERE id=?', 'i', $request['user'])) {
								sqlUpdate($C, 'DELETE FROM requests WHERE user=? AND type=0', 'i', $request['user']);
								echo '<h2>Email Verified</h2>';
							}
							else {
								echo '<h2>Failed to Update Database</h2>';
							}
						}
						else {
							echo '<h2>Invalid Verification Request</h2>';
						}
					}
					else {
						echo '<h2>Verification Request Expired</h2><a href="./validate">Click here to send another one</a>';
					}
					$res->free_result();
				}
				else {
					echo '<h2>Invalid Verification Request</h2>';
				}
				$C->close();
			}
			else {
				echo '<h2>Failed to Connect to Database</h2>';
			}
			echo '</div>';
		}
		else {
			?>
			<div class="formWrapper">
				<form id="validateEmailForm">
					<h1>Email Verification Request</h1>
					<div id="errs" class="errorcontainer"></div>
					<div class="inputblock">
						<label for="validateEmail">Email</label>
						<input id="validateEmail" name="validateEmail" type="email" autocomplete="email" required placeholder="Enter your email" onkeydown="if(event.key === 'Enter'){event.preventDefault();sendValidateEmailRequest();}"/>
					</div>
					<br>
					<div class="btn" onclick="sendValidateEmailRequest();">Send Verification Request</div>
					<br>
					<br>
					<br>
					<a href="/login">Already Verified? Log In</a>
				</form>

				<svg class="wave" xmlns="http://www.w3.org/2000/svg" viewBox="0 32 1440 320"><defs><linearGradient id="a" x1="50%" x2="50%" y1="-10.959%" y2="100%"><stop stop-color="#ffffff" stop-opacity=".10" offset="0%"/><stop stop-color="#FFFFFF" stop-opacity=".05" offset="100%"/></linearGradient></defs><path fill="url(#a)" fill-opacity="1" d="M 0 320 L 48 288 C 96 256 192 192 288 160 C 384 128 480 128 576 112 C 672 96 768 64 864 48 C 960 32 1056 32 1152 32 C 1248 32 1344 32 1392 32 L 1440 32 L 1440 2000 L 1392 2000 C 1344 2000 1248 2000 1152 2000 C 1056 2000 960 2000 864 2000 C 768 2000 672 2000 576 2000 C 480 2000 384 2000 288 2000 C 192 2000 96 2000 48 2000 L 0 2000 Z"></path></svg>
				<svg class="wave" xmlns="http://www.w3.org/2000/svg" viewBox="0 32 1440 320"><defs><linearGradient id="a" x1="50%" x2="50%" y1="-10.959%" y2="100%"><stop stop-color="#ffffff" stop-opacity=".10" offset="0%"/><stop stop-color="#FFFFFF" stop-opacity=".05" offset="100%"/></linearGradient></defs><path fill="url(#a)" fill-opacity="1" d="M 0 320 L 48 288 C 96 256 192 192 288 160 C 384 128 480 128 576 112 C 672 96 768 64 864 48 C 960 32 1056 32 1152 32 C 1248 32 1344 32 1392 32 L 1440 32 L 1440 2000 L 1392 2000 C 1344 2000 1248 2000 1152 2000 C 1056 2000 960 2000 864 2000 C 768 2000 672 2000 576 2000 C 480 2000 384 2000 288 2000 C 192 2000 96 2000 48 2000 L 0 2000 Z"></path></svg>
			</div>
			<?php
		}
	?>



	<script src="script.js"></script>
</body>
</html>
