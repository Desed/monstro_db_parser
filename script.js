$(document).ready(function() {
    // Заполняем таблицу данными из list_party
    for (const group in list_party) {
        if (list_party.hasOwnProperty(group)) {
            $('#groups-table tbody').append(
                `<tr>
                    <td>${group}</td>
                    <td>${list_party[group]}</td>
                    <td><input type="checkbox" name="group" value="${group}"></td>
                </tr>`
            );
        }
    }

    var minFilter, maxFilter;

    // Используем встроенный функционал DataTables поиска
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            var domainCount = parseInt(data[1], 10) || 0; // Используйте правильный индекс столбца
            if (
                (isNaN(minFilter) && isNaN(maxFilter)) ||
                (isNaN(minFilter) && domainCount <= maxFilter) ||
                (minFilter <= domainCount && isNaN(maxFilter)) ||
                (minFilter <= domainCount && domainCount <= maxFilter)
            ) {
                return true;
            }
            return false;
        }
    );

    // Инициализируем DataTable с настройками для показа определенного числа записей и функцией drawCallback
    table = $('#profiles-table').DataTable({
        lengthMenu: [[500, 1000, 10000, 20000, -1], [500, 1000, 10000, 20000, "All"]],
        drawCallback: function() {
            var ids = this.api().rows({ filter: 'applied' }).data().toArray().map(function(row) {
                return row[0]; // Идентификатор профиля в первом столбце
            });

            $('#filter_ids').val(ids.join(', ')); // Отображаем все идентификаторы профилей через запятую
        }
    });
	
	 // Добавляем обработчик события поиска
    $('#profiles-table').on('search.dt', function() {
        var ids = table.rows({ search: 'applied' }).data().toArray().map(function(row) {
            return row[0]; // Идентификатор профиля в первом столбце
        });
        $('#filter_ids').val(ids.join(', ')); // Отображаем все идентификаторы профилей через запятую
    });
	
    // Обрабатываем отправку формы
    $('#group-form').on('submit', function(e) {
        e.preventDefault();
        var group = $("input[name='group']:checked").val();

        // Показываем сообщение о загрузке
        $('#loading-message').show();

        $.ajax({
            url: siteUrl + '/lib/ajax.php',
            type: 'POST',
            data: {action: 'domain_count_parse', group: group},
            dataType: 'json',
            success: function(result) {
                var counts = result.counts;
                var profile_days = result.profile_days;
                var profiles_list = result.profiles_list;

                // Заполняем раздел counts
                $('#counts').empty();
                for (var key in counts) {
                    if (counts.hasOwnProperty(key)) {
                        $('#counts').append(`<li class="list-group-item list-group-item-dark d-flex justify-content-between align-items-center">${key} <span class="badge badge-primary badge-pill">${counts[key]}</span></li>`);
                    }
                }

                // Заполняем раздел profile_days
                $('#profile-days').empty();
                for (var key in profile_days) {
                    if (profile_days.hasOwnProperty(key)) {
                        $('#profile-days').append(`<li class="list-group-item list-group-item-dark d-flex justify-content-between align-items-center">${key} <span class="badge badge-primary badge-pill">${profile_days[key]}</span></li>`);
                    }
                }

                // Очищаем таблицу и заполняем ее новыми данными
                table.clear();
                for (var key in profiles_list) {
                    if (profiles_list.hasOwnProperty(key)) {
                        table.row.add([
                            key,
                            profiles_list[key].count_domains,
                            profiles_list[key].create_date,
                            profiles_list[key].profile_days
                        ]);
                    }
                }
                table.draw();
                
                // Отобразить таблицу и скрыть сообщение о загрузке
                $('#results').show();
                $('#loading-message').hide();
            }
        });
    });

    // Обработка события изменения фильтра
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        var direction = $("input[name='direction']:checked").val();
        var value = parseInt($("#filter-value").val(), 10);

        if (direction == ">") {
            minFilter = value;
            maxFilter = NaN;
        } else {
            minFilter = NaN;
            maxFilter = value;
        }
        table.draw();

        // Заполнение текстового поля номерами профилей через запятую
        var filteredData = table.rows({ filter: 'applied' }).data();
        var profileIds = $.map(filteredData, function(value, index) {
            return value[0]; // Используйте правильный индекс столбца
        });
        $('#filter-ids').val(profileIds.join(', '));
    });
});
