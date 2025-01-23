<?php
//
// Вывод статистики по игре
//

/*ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);*/

require 'functions.php'; 

if( isset($_POST['game'])) {
	$game = $_POST['game'];
}
if( isset($_GET['game'])) {
	$game = $_GET['game'];
}

// читаем данные для соединения с БД
$config = require 'db_conn.php';

// Создаем соединение
$conn = new mysqli($config['host'], $config['user'], $config['password'], $config['database']);

// Проверяем соединение
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html>
<meta name="viewport" content="width=device-width, initial-scale=1">
<head>
  <link rel="stylesheet" href="curling.css">
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
  <title>Статистика по игре</title>
</head>
<body>
	<form id="save_form" name="save-form" action="" method="post">
		<h3 style="font-weight: normal; color: blueviolet;">Статистика по игре
			<?php echo'<input style="width: 60px;" name="game" value="'.$game.'" type="number">'; ?></h3>
		<?php
		if(isset($game)){
			// информация по игре
			$sql = 'select * from game where id_game='.$game;
			$res = $conn->query($sql);
			$g = $res->fetch_array();
			printf('<p>%s. %s.</p>', date('d.m.Y', strtotime($g['date'])), $g['descr']);

			// массив игроков
			$player = array();
			$sql = 'select players.* from game_players, players where players.id_player=game_players.id_player and id_game='.$game;
			$res_pl = $conn->query($sql);
			while ($p = $res_pl->fetch_array()) {
				$player[$p['id_player']] = $p['name'];
			}
			// результаты бросков по каждому игроку
			foreach( $player as $id=>$name ) {
				$sql = 'select * from stat where id_player='.$id.' and id_game='.$game.' order by id_stat';
				$res = $conn->query($sql);
				$n = 1;
				if( $s = $res->fetch_array() ) {	// если есть информация по броскам
					// заголовок таблицы
					echo '<div class="table_block"><span class="values">'.$name.'</span><br>';
					echo '<div><table><tbody><tr><th>№</th><th>Тип</th><th>CW</th><th>Оценка</th></tr>';
					while ( $s ) {
						echo '<tr><td>', $n++, '</td>';
						echo '<td>', $s['draw'] ? 'draw' : 'take', '</td>';
						echo '<td>', $s['clockwise'] ? 'in' : 'out', '</td>';
						echo '<td>', $s['score'], '</td>';
						$s = $res->fetch_array();
					}
				}
				echo '</tr></tbody></table></div></div>';
			}
			echo '<br><br>';

			// загружаем статистику
			$sql = sprintf('SELECT * FROM game_players WHERE id_game=%d GROUP BY id_player', $game);
			$res_pl = $conn->query($sql);
			while( $p = $res_pl->fetch_array()) {
				if( !is_null($p['total']) ) {	// если есть статистика. Смотрим по total
					// имя игрока перед таблицей
					echo '<div class="table_block"><span class="values">'.$player[$p['id_player']].'</span><br>';
					// заголовок таблицы
					echo '<div><table><tr><th></th><th>Out</th><th>In</th><th>Total</th></tr><tr><td>Take</td><td>';
					echo_null($p['out_take']);
					echo '</td><td>';
					echo_null($p['in_take']);
					echo '</td><td>';
					echo_null($p['all_take']);
					echo '</td></tr><td>Draw</td><td>';
					echo_null($p['out_draw']);
					echo '</td><td>';
					echo_null($p['in_draw']);
					echo '</td><td>';
					echo_null($p['all_draw']);
					echo '</td></tr><td>Total</td><td>';
					echo_null($p['all_out']);
					echo '</td><td>';
					echo_null($p['all_in']);
					echo '</td><td>';
					echo_null($p['total']);
					echo '</td></tr></table></div></div>';
				}
			}
			echo '</div>';
		}
		?>
		<br>
		<input type="button" value="Назад" onClick='location.href="index.php"'>
	</form>
</body>
</html>
