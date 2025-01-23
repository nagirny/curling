<?php
// включение вывода ошибок при необходимости
//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);

require 'functions.php'; 

class statistic {
	public $perc, $cnt, $result;
	
	public function __construct(float $perc = 0.0, float $cnt = 0.0) {
		$this->perc = $perc;
        $this->cnt = $cnt;
		if( $cnt>0 )
			$this->result = round($perc/$cnt/3*100, 0);
	}		
}

if( isset($_POST['game'])) {
	$game = $_POST['game'];
}
if( isset($_GET['game'])) {
	$game = $_GET['game'];
}
if( isset($_GET['msg'])) {
	echo "<p style='color: darkgreen;'>".$_GET['msg']."</p>";
}
// читаем данные для соединения с БД
$config = require 'db_conn.php';

// Создаем соединение
$conn = new mysqli($config['host'], $config['user'], $config['password'], $config['database']);

// Проверяем соединение
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
//var_dump($_POST);

if ($_POST) {
	// сохранение броска
	if(isset($_POST['save_stat'])) {
		if( isset($_POST['draw'], $_POST['cw'], $_POST['score']) ) {
			$sql='INSERT INTO stat (draw, clockwise, score, id_player, id_game)
				VALUES ('.$_POST['draw'].', '.$_POST['cw'].', '.$_POST['score'].', '.$_POST['player'].', '.$game.')';
			if( $conn->query($sql) ) {
				echo "<p style='color: darkgreen;'>Данные успешно сохранены</p>";
				unset($_POST);
				header('location: input.php?game='.$game);
			}
		}
	}
	// отмена последнего броска
	elseif( isset( $_POST['cancel'] )) {
		$sql = 'DELETE FROM stat WHERE id_stat=(SELECT MAX(id_stat) FROM stat WHERE id_game='.$game.') AND id_game='.$game;
		if( $conn->query($sql) ) {
			echo "<p style='color: darkred;'>Данные успешно удалены</p>";
			unset($_POST);
			header('location: input.php?game='.$game);
		}
	}
	// создание новой игры
	elseif( isset( $_POST['new_game'] )) {
		// Создаем новую игру
		$sql='INSERT INTO game (date, descr) values ( NOW(), "'.$_POST['descr'].'")';
		$conn->query($sql);
		// получаем её id
		$sql = 'SELECT id_game FROM game ORDER BY id_game DESC LIMIT 1';
		$res_game = $conn->query($sql);
		$g = $res_game->fetch_object();
		$game = $g->id_game;
		// Записываем игроков в игру
		$sql='INSERT INTO game_players (id_player, id_game) values (';
		foreach($_POST['player'] as &$pl) {
			$sql = $sql.$pl.', '.$game.'), (';
		}
		$sql = substr($sql,0,-3);
		prn($sql);
		if(!$conn->query($sql)) {
			echo '<p style="color: darkred;">Ошибка добавления игроков</p>';
		}
		unset($_POST);
	}
	// очистка статистики
	elseif(isset($_POST['clear'])) {
		$sql='DELETE FROM stat WHERE id_game='.$game;
		if( $conn->query($sql) ) {
			echo "<p style='color: darkgreen;'>Данные успешно удалены</p>";
			unset($_POST);
		}
	}
	elseif( isset( $_POST['go_to'] )) {
		$game = $_POST['num'];
		unset($_POST);
	}
}
?>
<script>
// проверка на выбор игрока - разблокировка кнопки
function EnableButton() {
	with (document.forms.save_form) {
		if (name.value!='') {
			save_stat.disabled=false;
		}
		else {
			save_stat.disabled=true;
		}
	}
}
</script>

<!DOCTYPE html>
<html>
<meta name="viewport" content="width=device-width, initial-scale=1">
<head>
  <link rel="stylesheet" href="curling.css">
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
  <title>Статистика бросков</title>
</head>
<body>
	<form id="save_form" name="save-form" action="" method="post">
		<h3 style="font-weight: normal; color: blueviolet;">Ввод оценок бросков
			<?php echo'<input style="width: 60px;" name="game" value="'.$game.'" type="number">'; ?>
			</h3>
		<?php
		if(isset($game)){
			// загружаем список игроков
			$sql = 'SELECT players.id_player, name FROM game_players, players WHERE game_players.id_player = players.id_player AND id_game='.$game;
			$res_pl = $conn->query($sql);
			while ($pl = $res_pl->fetch_object()) {
				echo '<div class="form_radio_btn"><input id="radio0'.$pl->id_player.'" type="radio" name="player" onchange="EnableButton()" value="'.$pl->id_player.'">';
				echo '<label for="radio0'.$pl->id_player.'"> '.$pl->name.'</label></div>';
				$player[] = $pl;
			}
		}
		?>
		</div><br>
		<div class="form_radio_btn">
			<input id="radio1" type="radio" name="draw" value="0" checked>
			<label for="radio1"> Take</label>
		</div>
		<div class="form_radio_btn">
			<input id="radio2" type="radio" name="draw" value="1">
			<label for="radio2"> Draw</label>
		</div><br>
		<div class="form_radio_btn">
			<input id="r3" type="radio" name="cw" value="1" checked>
			<label for="r3"> In</label>
		</div>
		<div class="form_radio_btn">
			<input id="r4" type="radio" name="cw" value="0">
			<label for="r4"> Out</label>		
		</div><br>
		<div class="form_radio_btn">
			<input id="r5" type="radio" name="score" value="0">
			<label style="width: 40px;" for="r5"> 0</label>
		</div>
		<div class="form_radio_btn">
			<input id="r6" type="radio" name="score" value="1" checked>
			<label style="width: 40px;" for="r6"> 1</label>		
		</div>
		<div class="form_radio_btn">
			<input id="r7" type="radio" name="score" value="2">
			<label style="width: 40px;" for="r7"> 2</label>
		</div>
		<div class="form_radio_btn">
			<input id="r8" type="radio" name="score" value="3">
			<label style="width: 40px;" for="r8"> 3</label>		
		</div>
		<br />
		<input style="background: pink;" type="submit" name="cancel" id="cancel" value="Отменить" />
		<input disabled="disabled" style="background: aquamarine;" type="submit" name="save_stat" id="save_stat" value="Сохранить" />
		<br />
<?php
$stmt = $conn->prepare('UPDATE game_players SET in_draw=?, in_take=?, out_draw=?, out_take=?, all_draw=?, all_take=?, all_in=?, all_out=?, total=? 
	WHERE id_game=? AND id_player=?');
//цикл по игрокам
echo '<div>';
foreach($player as &$p) {
	// сброс статистики
	$st = array(array());
	$stout = array(array());
	unset( $cw, $ccw, $dr, $tk );
	// сбор статистики
	$sql = 'SELECT clockwise, draw, SUM(score) as s, count(*) as c FROM stat WHERE id_game='.$game.' AND id_player='.$p->id_player.' GROUP BY clockwise, draw';
	$res_stat = $conn->query($sql);
	while ($stat = $res_stat->fetch_object()) {
		$st[$stat->draw][$stat->clockwise] = new statistic($stat->s, $stat->c);
	}
	for( $i=0; $i<2; $i++ )
		for( $j=0; $j<2; $j++ ) {
			$stout[$j][$i] = isset($st[$j][$i]->result) ? $st[$j][$i]->result : NULL;
		}
	// in
	$sum = $st[1][1]->cnt + $st[0][1]->cnt;
	if( isset( $sum ) and $sum > 0 )
		$stout[2][1] = round(($st[1][1]->perc + $st[0][1]->perc)/$sum/3.0*100.0, 0);
	// out
	$sum = $st[1][0]->cnt + $st[0][0]->cnt;
	if( isset( $sum ) and $sum > 0 )
		$stout[2][0] = round(($st[1][0]->perc + $st[0][0]->perc)/$sum/3.0*100.0, 0);
	// draw
	$sum = $st[1][1]->cnt + $st[1][0]->cnt;
	if( isset( $sum ) and $sum > 0 )
		$stout[1][2] = round(($st[1][1]->perc + $st[1][0]->perc)/$sum/3.0*100.0, 0);
	// take
	$sum = $st[0][0]->cnt + $st[0][1]->cnt;
	if( isset( $sum ) and $sum > 0 )
		$stout[0][2] = round(($st[0][0]->perc + $st[0][1]->perc)/$sum/3.0*100.0, 0);
	$s = 0;
	$c = 0;
	foreach($st as $tp) {
		foreach($tp as $rot) {
			$c += $rot->cnt;
			$s += $rot->perc;
		}
	}
	if( $c>0 )
		$stout[2][2] = round( $s/$c/3.0*100.0, 0 );

	// имя игрока перед таблицей
	echo '<div style="display: inline-block;"><span class="values">'.$p->name.'</span><br>';
	// вывод статистики в таблицу
	// заголовок таблицы
	echo '<div class="table_block"><table><tr><th></th><th>Out</th><th>In</th><th>Total</th></tr><tr><td>Take</td>';
	for( $i=0; $i<=2; $i++ ) {
		if( $i==1 )
			echo '</tr><tr><td>Draw</td>';
		if( $i==2 )
			echo '</tr><tr><td>Total</td>';
		for( $j=0; $j<=2; $j++ ) {
			echo '<td>';
//				prn($i.', '.$j.', '.$stout[$i][$j]);
			echo_null( $stout[$i][$j] );
			echo '</td>';
		}
	}
	echo '</tr></table></div></div>';
	// сохраним статистику в базу по нажатию на кнопку
	if(isset($_POST['save_game'])) { 
		$stmt->bind_param('iiiiiiiiiii', $stout[1][1], $stout[0][1], $stout[1][0], $stout[0][0], $stout[1][2], $stout[0][2], $stout[2][1], $stout[2][0], 
			$stout[2][2], $game, $p->id_player);
		$stmt->execute();
	}
}
echo '</div>';
//<input style="background: pink;" type="submit" name="clear" value="Очистить" />
?>
		<br>
		<input type="button" value="Назад" onClick='location.href="index.php"'>
		<input style="background: aquamarine;" type="submit" value="Сохранить игру" name="save_game">
	</form>
</body>
</html>
