<?php 
// конектимся к бд
$connection = new PDO('mysql:host=localhost; dbname=email_verif; charset=utf8', 'root', '');

// создаем функцию генератор случайной строки
function generateRandomString(){
	$char = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$random = "";
	for ($i = 0; $i < 20; $i++) {
		$random .= $char[rand(0, (strlen($char) - 1))];
		
	}
	return $random;
}

// проверяем отправлены ли данные на регистрацию нового пользователя
$out = '';
if ($_POST['username']) {
	$username = trim(strip_tags($_POST['username']));
	$email = $_POST['email'];
	$authKey = generateRandomString();
	$query = $connection->query("INSERT INTO email (`username`, `email`, `auth_key`) VALUES ('$username', '$email', '$authKey')");
	if ($query) { // если запрос прошел отправляем письмо юзеру на почту
		mail($email, 'Подтвердите почту', "Перейдите по ссылке http://emailverif/?auth=$authKey");
		$out = "Письмо отправлено, подтвердите почту!";
	} else { // если почта такая есть в бд и она не подтверждена 
		$findUser = $connection->query("SELECT * FROM email WHERE email = '$email'");
		$findUser = $findUser->fetch();
		if (!$findUser['validate']) {
			$out = "Ваша почта так и не подтверждена...";
		} else {// если такая почта есть и подтверждена
			$out = "Такой email уже существует";
		}
	}
}

// проверяем гет запрос от пользователя из письма для подтверждения верификации
if ($_GET['auth']) {
	$auth = $_GET['auth'];
	$search = $connection->query("SELECT * FROM email WHERE auth_key = '$auth'");

	if ($search) {
		$connection->query("UPDATE email SET validate = true, updated_at = current_timestamp WHERE auth_key = '$auth'");
		$out = "Ваша почта подтверждена!";
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>email verification</title>
</head>
<style>
	* {
		box-sizing: border-box;
	}
	.form-body {
		width: 400px;
		margin: 40px auto 0;
	}
	.form-body p {
		text-align: center;
		
	}
	form {
		display: flex;
		justify-content: center;
		align-items: center;
		flex-direction: column;

	}
	form input {
		margin: 10px 0 0;
		width: 160px;
		display: block;
	}

	.input-text {
		height: 30px;
		border-radius: 5px;
		border: 1px solid gray;
		padding: 2px 5px;
	}
	.form-btn {
		height: 25px;
		border-radius: 5px;
	}
	
</style>
<body>
	<div class="form-body">
		<p>форма регистрации</p>
		<p><?= $out ?></p>
		<form action="" method="post">
			
			<input class="input-text" type="text" name="username"  required>
			
			<input class="input-text" type="email" name="email"  required>
			<input class="form-btn" type="submit">
		</form>
	</div>
	
	
</body>
</html>