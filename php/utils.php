<?php
require_once 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

function connect() {
	$C = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
	if($C->connect_error) {
		return false;
	}
	return $C;
}

function sqlSelect($C, $query, $format = false, ...$vars) {
	$stmt = $C->prepare($query);
	if($format) {
		$stmt->bind_param($format, ...$vars);
	}
	if($stmt->execute()) {
		$res = $stmt->get_result();
		$stmt->close();
		return $res;
	}
	$stmt->close();
	return false;
}

function sqlInsert($C, $query, $format = false, ...$vars) {
	$stmt = $C->prepare($query);
	if($format) {
		$stmt->bind_param($format, ...$vars);
	}
	if($stmt->execute()) {
		$id = $stmt->insert_id;
		$stmt->close();
		return $id;
	}
	$stmt->close();
	return -1;
}

function sqlUpdate($C, $query, $format = false, ...$vars) {
	$stmt = $C->prepare($query);
	if($format) {
		$stmt->bind_param($format, ...$vars);
	}
	if($stmt->execute()) {
		$stmt->close();
		return true;
	}
	$stmt->close();
	return false;
}

function sqlDelete($C, $query, $format = false, ...$vars) {
    $stmt = $C->prepare($query);
    if ($format) {
        $stmt->bind_param($format, ...$vars);
    }
    if ($stmt->execute()) {
        $stmt->close();
        return true;
    }
    $stmt->close();
    return false;
}


function createToken() {
	$seed = urlSafeEncode(random_bytes(8));
	$t = time();
	$hash = urlSafeEncode(hash_hmac('sha256', session_id() . $seed . $t, CSRF_TOKEN_SECRET, true));
	return urlSafeEncode($hash . '|' . $seed . '|' . $t);
}

function validateToken($token) {
	$parts = explode('|', urlSafeDecode($token));
	if(count($parts) === 3) {
		$hash = hash_hmac('sha256', session_id() . $parts[1] . $parts[2], CSRF_TOKEN_SECRET, true);
		if(hash_equals($hash, urlSafeDecode($parts[0]))) {
			return true;
		}
	}
	return false;
}

function urlSafeEncode($m) {
	return rtrim(strtr(base64_encode($m), '+/', '-_'), '=');
}
function urlSafeDecode($m) {
	return base64_decode(strtr($m, '-_', '+/'));
}

function sendEmail($to, $toName, $subj, $msg) {
	$mail = new PHPMailer(true);
	try {

		//Server settings
	$mail->isSMTP();
	$mail->Host       = SMTP_HOST;
	$mail->SMTPAuth   = true;
	$mail->Username   = SMTP_USERNAME;
	$mail->Password   = SMTP_PASSWORD;
	$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
	$mail->Port       = SMTP_PORT;

	//Recipients
	$mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
	$mail->addAddress($to, $toName);

	// Content
	$mail->isHTML(true);
	$mail->Subject = $subj;
	$mail->Body    = $msg;

	$mail->send();
	return true;
	} 
	catch(Exception $e) {
		echo $e;
		return false;
	}
}

function isMobile(): bool {
	$useragent=$_SERVER['HTTP_USER_AGENT'];

	if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)|| preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))) {
		return true;
	} else {
		return false;
	}
}

function callApiRequest(string $url) {
    // Fetch the API response
    $response = file_get_contents($url);
    
    // Check if the request was successful
    if ($response === FALSE) {
        return ["error" => "Unable to fetch data"];
    }

    $jsonApiData = json_decode($response, true);

    return $jsonApiData;
}