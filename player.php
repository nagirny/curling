<?php
//
// Вывод статистики по игроку
//

/*ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);*/

require 'functions.php'; 

if( isset($_POST['player'])) {
	$player = $_POST['player'];
}
if( isset($_GET['player'])) {
	$player = $_GET['player'];
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
<script src="https://code.highcharts.com/highcharts.js"></script>
<head>
  <link rel="stylesheet" href="curling.css">
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
  <title>Статистика по игрокам</title>
</head>
<body>
	<form id="save_form" name="save-form" action="" method="post">
		<h3 style="font-weight: normal; color: blueviolet;">Статистика по игрокам</h3>
			<?php //echo'<input style="width: 60px;" name="player" value="'.$player.'" type="number">'; ?>
		<?php
		if( isset($player) ){
			foreach( $player as &$pl ) {
				// информация по игрокам
				$sql = 'select * from players where id_player='.$pl;
				$res = $conn->query($sql);
				$p_name = $res->fetch_array();
				
				// создаем блок для вывода данных датчика
/*				echo '<div id="current-player" class="container">';
				echo '<h3>Динамика</h3>';
				echo '</div>';*/
				echo '<div style="height:60%;" id="chart-' . $pl .'" class="container"></div>';
				// динамика результатов
				$sql = 'select game_players.*, date from game_players, game where game_players.id_game=game.id_game and id_player='.$pl.' order by date';
				$res_pl = $conn->query($sql);
				$total = array();
				$draw = array();
				$take = array();
				while( $p = $res_pl->fetch_array()) {
					$d = strtotime( $p['date'] )*1000;
					if( !is_null( $p['total'] ))
						$total[] = array( $d, $p['total'] );
					if( !is_null( $p['all_draw'] ))
						$draw[] = array( $d, $p['all_draw'] ); 
					if( !is_null( $p['all_take'] ))
						$take[] = array( $d, $p['all_take'] ); 
				}
				$draw = json_encode($draw, JSON_NUMERIC_CHECK);
				$take = json_encode($take, JSON_NUMERIC_CHECK);
				$total = json_encode($total, JSON_NUMERIC_CHECK);
			?>
			<script>
				var total = <?php echo $total; ?>;
				var draw = <?php echo $draw; ?>;
				var take = <?php echo $take; ?>;
				
				// рисуем график
				Highcharts.setOptions({
					time: {
						timezoneOffset: -7 * 60		// UTC +7
						}
					});
				var chartT = new Highcharts.Chart({
					chart:{ renderTo : <?php echo '"chart-'.$pl. '"'; ?> },
					title: { text: <?php echo "'".$pl.': '.$p_name['name']."'"; ?> },
					series: [{ 
						showInLegend: true,
						data: total,
						color: '#9e058a',
						name: "total",
						lineWidth: 3,
						type: 'spline' },
						{
						showInLegend: true,
						data: draw,
						color: '#059e8a',
						name: "draw",
						lineWidth: 2,
						type: 'spline' },
						{
						showInLegend: true,
						data: take,
						color: '#8a9e05',
						name: "take",
						lineWidth: 2,
						type: 'spline'
						}],
					plotOptions: {
						spline: { animation: false, dataLabels: { enabled: false }},
						},
					xAxis: {
						type: 'datetime',
						dateTimeLabelFormats: {
							week: '%e.%m',
							month: '%e.%m',
							year: '%e.%m'
							}
						},
					yAxis: {
						title: { text: '%' }
						},
					credits: { enabled: false }
					});
			</script>
			<?php
				// загружаем итоговую статистику
				$sql = sprintf('select avg(in_draw) as in_draw, avg(in_take) as in_take, avg(out_draw) as out_draw, avg(out_take) as out_take,
					avg(all_draw) as all_draw, avg(all_take) as all_take, avg(all_in) as all_in, avg(all_out) as all_out, avg(total) as total, id_player
					from game_players where id_player=%d group by id_player;', $pl);
				$res_pl = $conn->query($sql);
				while( $p = $res_pl->fetch_array()) {
					if( !is_null($p['total']) ) {	// если есть статистика. Смотрим по total
						// имя игрока перед таблицей
						echo '<div class="table_block">';
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
		}
		?>
		<br>
		<input type="button" value="Назад" onClick='location.href="index.php"'>
	</form>
</body>
</html>
