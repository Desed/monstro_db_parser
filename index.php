<?php
	require_once 'config.php';
	require_once 'lib/Monstro_parser.php';
 
?>

<!DOCTYPE html>
<html>
<head>
    <title>Анализатор базы Monstro</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.18/datatables.min.css"/>
	<link rel="stylesheet" type="text/css" href="style.css"/>
</head>
<body>

<script>
var list_party = <?php echo json_encode(list_party()); ?>;
var siteUrl = '<?php echo $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME'] . $dir_name; ?>';
</script>



    <div class="container mt-5">
	
				<div class="jumbotron">
					<div class="container">
					<h1 class="title">Парсинг и анализ профилей Monstro</h1>
					<hr class="my">
					Данный модуль сделан для анализа профилей Monstro в базе данных PostGres<br><br>
					<b>В чем смысл?</b><br>
					- Скрипт парсит cookies каждого профиля и собирает количество доменов с отметкой яндекс метрики, что дает 100% точность как прокачался ваш профиль. <br>
					- Скрипт показывает возраст и дату создания профиля не по дате создания профиля в монстро а на момент получения первой cookie яндекса <br>
					- Есть также таблица и фильтр, с ее помощью вы сможете менять группы профилям которые ещё не смогли в прокачку до конца. И отбирать только хорошие профили с очень высокой точностью. <br><br>
					<a href="https://t.me/yandex_bots" target="_blank">Сделано @Desed / Быдлокодер API</a>
					<br><br>
					<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModalLong">SQL шаблоны</button>
					
					</div>
				</div>
			
			
        <form id="group-form">
            <table class="table table-striped table-dark table-sm" id="groups-table">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">Группа</th>
                        <th scope="col">Количество профилей</th>
                        <th scope="col">Выбрать группу</th>
                    </tr>
                </thead>
                <tbody>
                <!-- Заполняется с помощью JavaScript -->
                </tbody>
            </table>
            <button type="submit" class="btn btn-primary">Получить подробности по группе</button>
        </form>
		<br>
		<div class="alert alert-warning" id="loading-message">
			<p>Данные обрабатываются, пожалуйста, подождите... Если профилей много, это может занять время.</p>
		</div>
        <div id="results">
		
		
		<div id="accordion">
			<div class="card">
				<div class="card-header" id="headingOne">
				<h5 class="mb-0">
					<button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
					Подсчет группировки кол-во сайтов / кол-во профилей
					</button>
				</h5>
				</div>
			
				<div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
				<div class="card-body">
						<h3>кол-во сайтов / кол-во профилей</h3>
						<ul class="list-group col-3"><div id="counts"></div></ul>				</div>
				</div>
			</div>
			<div class="card">
				<div class="card-header" id="headingTwo">
				<h5 class="mb-0">
					<button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
					Подсчет группировки кол-во дней профилю / кол-во профилей
					</button>
				</h5>
				</div>
				<div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
				<div class="card-body">
					<h3>Дни / Профили</h3>
					<ul class="list-group col-3"><div id="profile-days"></div></ul>				</div>
				</div>
			</div>
		</div>
		
      
            <h3>Список профилей</h3>
				<form id="filter-form" class="mt-5">
					<div class="form-group">
						<label for="filter-value">Отфильтровать по количеству доменов:</label>
						<input type="text" id="filter-value" class="form-control" placeholder="Value">
					</div>
					<div class="form-check">
						<input type="radio" name="direction" value=">" id="gt" class="form-check-input" checked>
						<label class="form-check-label" for="gt">Больше</label>
					</div>
					<div class="form-check">
						<input type="radio" name="direction" value="<" id="lt" class="form-check-input">
						<label class="form-check-label" for="lt">Меньше</label>
					</div>
					<button type="submit" class="btn btn-primary">Фильтр</button>
				</form>

			<textarea class="form-control" id="filter-ids" rows="3"></textarea>
			
            <table class="table table-striped table-dark table-sm" id="profiles-table">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">Ид профиля</th>
                        <th scope="col">Доменов</th>
                        <th scope="col">Дата создания</th>
                        <th scope="col">Возраст</th>
                    </tr>
                </thead>
                <tbody>
                <!-- Заполняется с помощью JavaScript -->
                </tbody>
            </table>
        </div>
    </div>


			<!-- Modal -->
			<div class="modal fade" id="exampleModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLongTitle">SQL шаблоны</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					В поле textarea после нажатия кнопки Фильтр вам отображаются иды профилей которые вам показаны по фильтру, эти профиля можно отправлять в другие группы и.т.д <br>
					
					Перенос профилей в другую группу 1,2,3,4 это номера профилей<br>
					<div class="alert alert-primary" role="alert">
								UPDATE "profiles" SET party = "имя новой группы" where pid in (1,2,3,4,5);
					</div>


					Удаление профилей из базы<br>
					<div class="alert alert-primary" role="alert">
								DELETE FROM "profiles" where pid in (1,2,3,4,5);
					</div>
			
					Если вы не знаете куда их заливать и.т.д см.Google.<br>
					
					Если у вас зависает на моменте получения профилей то в Ngnix нужно увеличить параметр<br>
					<div class="alert alert-primary" role="alert">
								client_max_body_size 100M;
					</div>
					
					
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
				</div>
				</div>
			</div>
			</div>

    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
	<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.18/datatables.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
