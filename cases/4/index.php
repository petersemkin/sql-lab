<!DOCTYPE html>
<html lang="ru">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title>Задание №4 / Лабораторная работа №1. SQL-инъекции</title>
		<meta name="description" content="SQL Injection Lab">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<link href="../../css/bootstrap.min.css" rel="stylesheet">

		<style>
			h4 {
				margin-bottom: 30px;
			}

			.nav-inner {
				margin-left: 10px;
			}
		</style>

		<!--[if lt IE 9]>
			<script src="../../js/lib/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>
		<!--[if lt IE 7]>
			<p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
		<![endif]-->

		<div class="container">
			<div class="page-header">
				<h1 class="text-center">Лабораторная работа №1 <small>&laquo;SQL-инъекции&raquo;</small></h1>
			</div>

			<div class="row">
				<div class="col-md-3">
					<ul class="nav nav-pills nav-stacked">
						<li><a href="../../">Главная</a></li>
						<li><a href="../../documentation.html">Методическое пособие</a></li>
						<li>
							<a href="#">Рабочее задание</a>
							<ul class="nav nav-pills nav-stacked nav-inner">
								<li><a href="../1/">Задание №1</a></li>
								<li><a href="../2/">Задание №2</a></li>
								<li><a href="../3/">Задание №3</a></li>
								<li class="active"><a href=".">Задание №4</a></li>
							</ul>
						</li>
						<li><a href="https://github.com/toogle/sql-lab" target="_blank">Исходный код</a></li>
					</ul>
				</div>

				<div class="col-md-9">
					<div class="well well-lg">
						<h4>Журнал транзакций</h4>
						<table class="table">
							<tr>
								<th>Идентификатор</td>
								<th>Отправитель</td>
								<th>Получатель</td>
								<th>Сумма</td>
								<th>Время</td>
							</tr>
							<?php
							date_default_timezone_set('Europe/Moscow');

							// NOTE: The following code intended for demonstration purposes only.
							//       It is EXTREMELY DANGER to use it for real applications.
							$conn = @mysql_connect('localhost', 'sql-lab', 'sql-lab');
							@mysql_select_db('sql-lab', $conn);

							$month = isset($_GET['month']) ? $_GET['month'] : date('n');
							$limit = isset($_GET['limit']) ? $_GET['limit'] : 20;

							$sql  = "SELECT id, sender, recipient, amount, time";
							$sql .= "  FROM transactions";
							$sql .= "  WHERE sender = 1234567890123456";
							$sql .= "    AND EXTRACT(MONTH FROM time) = ${month}";
							$sql .= "  ORDER BY time ASC";
							$sql .= "  LIMIT ${limit}";

							if (preg_match('/INSERT|UPDATE|DELETE|CREATE|ALTER|DROP/i', $sql)) {
								die('Запрос не может быть выполнен: обнаружен недопустимый оператор!');
							}

							$res = mysql_query($sql, $conn);
							if ($res) {
								if (mysql_num_rows($res) > 0) {
									while ($row = mysql_fetch_array($res)) {
										$html  = "<tr>";
										$html .= "  <td>${row['id']}</td>";
										$html .= "  <td>${row['sender']}</td>";
										$html .= "  <td>${row['recipient']}</td>";
										$html .= "  <td>${row['amount']}</td>";
										$html .= "  <td>${row['time']}</td>";
										$html .= "</tr>";
	
										echo $html;
									}
								} else {
									$html  = "<tr>";
									$html .= "  <td colspan=\"5\">Ничего не найдено.</td>";
									$html .= "</tr>";
									
									echo $html;
								}
							}
							?>
						</table>

						<form class="form-inline" method="GET">
							<div class="form-group">
								<select class="form-control" id="month-select" name="month">
									<option value="<?php echo date('n'); ?>">Выберите месяц</option>
									<option value="1">Январь</option>
									<option value="2">Февраль</option>
									<option value="3">Март</option>
									<option value="4">Апрель</option>
									<option value="5">Май</option>
									<option value="6">Июнь</option>
									<option value="7">Июль</option>
									<option value="8">Август</option>
									<option value="9">Сентябрь</option>
									<option value="10">Октябрь</option>
									<option value="11">Ноябрь</option>
									<option value="12">Декабрь</option>
								</select>
							</div>
							<button type="submit" class="btn btn-default">Фильтровать</button>
						</form>
					</div>

					<div class="panel panel-default">
						<div class="panel-heading">
							Подсказка
							<a href="#" class="pull-right" data-toggle="collapse" data-target="#hint">показать</a>
						</div>
						<div id="hint" class="panel-body collapse">
							<p>
								С помощью адресной строки можно попробовать вытащить не только данные таблицы 
								для этой страницы, но и данные из каких-нибудь других таблиц в этой БД. Таким 
								образом можно, например, узнать логины и пароли пользователей, которые
								используются на этом же сайте на странице авторизации. 								
							</p>
							
							<p>
								Для того, чтобы это сделать, можно воспользоваться SQL-оператором UNION, который
								позволяет объединять результаты нескольких запросов, в том числе в разные таблицы.
								Однако необязательно сразу пытаться угадывать и подбирать возможные имена таких 
								таблиц и их колонок. В БД существуют виртуальные таблицы, содержащие ее метаданные. 
								В mySQL они находятся в схеме <i>information_schema</i>.
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>

		<script src="../../js/lib/jquery-1.11.1.min.js"></script>
		<script src="../../js/lib/bootstrap.min.js"></script>
		<script>
			$('#hint').on('show.bs.collapse', function() {
				$('a[data-target="#hint"]').html('скрыть');
			});

			$('#hint').on('hide.bs.collapse', function() {
				$('a[data-target="#hint"]').html('показать');
			});
		</script>
	</body>
</html>
