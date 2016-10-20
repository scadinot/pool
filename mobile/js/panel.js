/* This file is part of Jeedom.
	*
	* Jeedom is free software: you can redistribute it and/or modify
	* it under the terms of the GNU General Public License as published by
	* the Free Software Foundation, either version 3 of the License, or
	* (at your option) any later version.
	*
	* Jeedom is distributed in the hope that it will be useful,
	* but WITHOUT ANY WARRANTY; without even the implied warranty of
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	* GNU General Public License for more details.
	*
	* You should have received a copy of the GNU General Public License
	* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

function initPoolPanel(_object_id) 
{
    jeedom.object.all(
	{
        error: function (error) 
		{
            $('#div_alert').showAlert({message: error.message, level: 'danger'});
		},
        success: function (objects) 
		{
            var li = ' <ul data-role="listview">';
            for (var i in objects) 
			{
                if (objects[i].isVisible == 1) 
				{
                    var icon = '';
                    if (isset(objects[i].display) && isset(objects[i].display.icon)) 
					{
                        icon = objects[i].display.icon;
					}
                    li += '<li></span><a href="#" class="link" data-page="panel" data-plugin="pool" data-title="' + icon.replace(/\"/g, "\'") + ' ' + objects[i].name + '" data-option="' + objects[i].id + '"><span>' + icon + '</span> ' + objects[i].name + '</a></li>';
				}
			}
            li += '</ul>';
            panel(li);
		}
	});
    displayPool(_object_id);
	
    $('#bt_valideDate').on('click', function () 
	{
        jeedom.history.chart = [];
        $('#div_displayEquipement').packery('destroy');
        displayPool(_object_id, $('#in_dateStart').val(), $('#in_dateEnd').val());
	});
	
    $(window).on("orientationchange", function (event) 
	{
        setTileSize('.eqLogic');
        $('#div_displayEquipement').packery();
	});
}

function displayPool(_object_id, _dateStart, _dateEnd) 
{
    $.showLoading();
    $.ajax(
	{
        type: 'POST',
        url: 'plugins/pool/core/ajax/pool.ajax.php',
        data: 
		{
            action: 'getPool',
            object_id: _object_id,
            version: 'mobile',
            dateStart: init(_dateStart),
            dateEnd: init(_dateEnd),
		},
        dataType: 'json',
        error: function (request, status, error) 
		{
            handleAjaxError(request, status, error);
		},
        success: function (data) 
		{
            if (data.state != 'ok') 
			{
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
			}
            var icon = '';
            if (isset(data.result.object.display) && isset(data.result.object.display.icon)) 
			{
                icon = data.result.object.display.icon;
			}
            $('#div_object').empty().append('<legend>' + icon + ' ' + data.result.object.name + '</legend>');
            $('#in_dateStart').value(data.result.date.start);
            $('#in_dateEnd').value(data.result.date.end);
            $('#div_displayEquipement').empty();
            $('#div_charts').empty();
            $('#div_chartRuntime').empty();
            var series = []
            for (var i in data.result.eqLogics) 
			{
                $('#div_displayEquipement').append(data.result.eqLogics[i].html).trigger('create');
                var div_graph = '<legend>' + data.result.eqLogics[i].eqLogic.name + '</legend>'
                div_graph += '<div class="chartContainer" id="div_graph' + data.result.eqLogics[i].eqLogic.id + '"></div>';
                $('#div_charts').append(div_graph);
                series.push(
				{
                    name: data.result.eqLogics[i].eqLogic.name,
                    data: data.result.eqLogics[i].runtimeByDay,
                    type: 'column',
                    tooltip: 
					{
                        valueDecimals: 1
					},
				});
                graphPool(data.result.eqLogics[i].eqLogic.id);
			}
            drawSimpleGraph('div_chartRuntime', series, 'column');
            setTileSize('.eqLogic');
            $('#div_displayEquipement').packery();
            $.hideLoading();
		}
	});
}

function graphPool(_eqLogic_id) 
{
    jeedom.eqLogic.getCmd(
	{
        id: _eqLogic_id,
        error: function (error) 
		{
            $('#div_alert').showAlert({message: error.message, level: 'danger'});
		},
        success: function (cmds) 
		{
            for (var i  in cmds) 
			{
		        if (cmds[i].logicalId == 'temperature') 
				{
                    jeedom.history.drawChart(
					{
                        cmd_id: cmds[i].id,
                        el: 'div_graph' + _eqLogic_id,
                        start: $('#in_startDate').value(),
                        end: $('#in_endDate').value(),
                        option: 
						{
                            graphColor: '#f39c12'
						}
					});
				}
				
				if (cmds[i].logicalId == 'temperature_outdoor' && cmds[i].isVisible == '1') 
				{
                    jeedom.history.drawChart(
					{
                        cmd_id: cmds[i].id,
                        el: 'div_graph' + _eqLogic_id,
                        start: $('#in_startDate').value(),
                        end: $('#in_endDate').value(),
                        option: 
						{
                            graphColor: '#f34c12'
						}
					});
				}
				
				if (cmds[i].logicalId == 'filtration') 
				{				
					jeedom.history.drawChart(
					{
						cmd_id: cmds[i].id,
						el: 'div_graph' + _eqLogic_id,
						start: $('#in_startDate').value(),
						end: $('#in_endDate').value(),
						option: 
						{
							graphStep: 1,
							graphColor: '#2c3e50',
							graphScale : 1,
							graphType : 'area',
							derive : 0
						}
					});
				}

			}
		}
	});
}

function drawSimpleGraph(_el, _serie) 
{
    var legend = 
	{
        enabled: true,
        borderColor: 'black',
        borderWidth: 2,
        shadow: true
	};
	
    new Highcharts.StockChart(
	{
        chart: 
		{
            zoomType: 'x',
            renderTo: _el,
            height: 350,
            spacingTop: 0,
            spacingLeft: 0,
            spacingRight: 0,
            spacingBottom: 0
		},
        credits: 
		{
            text: 'Copyright Jeedom',
            href: 'http://jeedom.fr',
		},
        navigator: 
		{
            enabled: false
		},
        rangeSelector: 
		{
            buttons: [
			{
                type: 'minute',
                count: 30,
                text: '30m'
				}, 
			{
                type: 'hour',
                count: 1,
                text: 'H'
				}, 
			{
                type: 'day',
                count: 1,
                text: 'J'
				}, 
			{
                type: 'week',
                count: 1,
                text: 'S'
				}, 
			{
                type: 'month',
                count: 1,
                text: 'M'
				}, 
			{
                type: 'year',
                count: 1,
                text: 'A'
				}, 
			{
                type: 'all',
                count: 1,
                text: 'Tous'
			}],
            selected: 6,
            inputEnabled: false
		},
        legend: legend,
        tooltip: 
		{
            pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y} {{heure(s)}}</b><br/>',
            valueDecimals: 2,
		},
        yAxis: 
		{
            format: '{value}',
            showEmpty: false,
            showLastLabel: true,
            min: 0,
            labels: 
			{
                align: 'right',
                x: -5
			}
		},
        scrollbar: 
		{
            barBackgroundColor: 'gray',
            barBorderRadius: 7,
            barBorderWidth: 0,
            buttonBackgroundColor: 'gray',
            buttonBorderWidth: 0,
            buttonBorderRadius: 7,
            trackBackgroundColor: 'none', trackBorderWidth: 1,
            trackBorderRadius: 8,
            trackBorderColor: '#CCC'
		},
        series: _serie
	});
}