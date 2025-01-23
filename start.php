<?php
// читаем данные для соединения с БД
$config = require 'db_conn.php';

// Создаем соединение
$conn = new mysqli($config['host'], $config['user'], $config['password'], $config['database']);

// Проверяем соединение
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
if ($_POST) {
	// добавление игрока
	if( isset($_POST['new_player'])) {
		$sql = 'INSERT INTO players (name) VALUES ("'.$_POST['name'].'")';
		$conn->query($sql);
		error_log("Inserting...", 0);
		if( $conn->query($sql) ) {
			echo "<p style='color: darkgreen;'>Данные успешно сохранены</p>";
			unset($_POST['new_player'], $_POST['name']);
		}
	}
}
unset($_POST['new_player'], $_POST['name']);
?>
	
<!DOCTYPE html>
<html>
<meta name="viewport" content="width=device-width, initial-scale=1">
<head>
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="curling.css">
  <title>Керлинг</title>
</head>
<body>
	<h3 style="font-weight: normal; color: blueviolet;">Статистика по играм</h3>
	<form style="text-align: left;" action="input.php" method="post">
		<?php
		// загружаем список игроков
		$sql = 'SELECT * FROM players';
		$res_pl = $conn->query($sql);
		while ($player = $res_pl->fetch_object()) {
			echo '<label><input type="checkbox" name="players[]" value="'.$player->id_player.'"/>'.$player->name.'</label><br>';
		}
		?>
		<input style="width: 200px;" type="text" name="descr" />
		<input type="submit" name="new_game" value="Новая игра" />
	</form>
	<form style="text-align: left;" action="" method="post">
		<input style="width: 200px" type="text" name="name" />
		<input type="submit" name="new_player" value="Новый игрок" />
	</form>
	<form action="input.php" method="post">
		<p>Переход к игре: <input style="width: 60px" type="text" name="num" />
		<input type="submit" name="go_to" value="Перейти" /></p>
	</form>
</body>
</html>
