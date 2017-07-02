
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

$(".eqLogic").delegate(".listCmdInfo", 'click', function () 
{
    var el = $(this).closest('.form-group').find('.eqLogicAttr');
    jeedom.cmd.getSelectModal({cmd: {type: 'info'}}, function (result) 
	{
        if (el.attr('data-concat') == 1) 
		{
            el.atCaret('insert', result.human);
		} 
		else 
		{
            el.value(result.human);
		}
	});
});

$("body").delegate(".listCmdAction", 'click', function () 
{
    var type = $(this).attr('data-type');
    var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
    jeedom.cmd.getSelectModal({cmd: {type: 'action'}}, function (result) 
	{
        el.value(result.human);
        jeedom.cmd.displayActionOption(el.value(), '', function (html) 
		{
            el.closest('.' + type).find('.actionOptions').html(html);
		});
		
	});
});

$('#bt_cronGenerator').on('click',function() {
    jeedom.getCronSelectModal({},function (result) {
        $('.eqLogicAttr[data-l1key=configuration][data-l2key=repeat_commande_cron]').value(result.value);
    });
});

$('.addAction').on('click', function () 
{
    addAction({}, $(this).attr('data-type'));
});

$('.addAsservissement').on('click', function () {
    addAsservissement({});
});

$('.addArretTotal').on('click', function () {
    addArretTotal({});
});

$('.addMarcheForcee').on('click', function () {
    addMarcheForcee({});
});

$('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgChauffage]').on('change', function ()
{
	if ($('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgChauffage]').value() == "disabled")
	{
		$('.cfgChauffage').hide();
	}
	else
	{
		$('.cfgChauffage').show();
	}

    if ($('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgChauffage]').value() == "disabled"
        && $('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgHivernage]').value() == "disabled")
    {
        $('.cfgExterieur').hide();
    }
    else
    {
        $('.cfgExterieur').show();
    }
});

$('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgTraitement]').on('change', function ()
{
    if ($('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgTraitement]').value() == "disabled")
    {
        $('.cfgTraitement').hide();
    }
    else
    {
        $('.cfgTraitement').show();
    }
});

$('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgSurpresseur]').on('change', function () 
{
	if ($('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgSurpresseur]').value() == "disabled")
	{
		$('.cfgSurpresseur').hide();
	}
	else
	{
		$('.cfgSurpresseur').show();
	}
});

$('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgFiltreSable]').on('change', function () 
{
	if ($('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgFiltreSable]').value() == "disabled")
	{
		$('.cfgFiltreSable').hide();
	}
	else
	{
		$('.cfgFiltreSable').show();
	}
});

$('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgHivernage]').on('change', function () 
{
	if ($('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgHivernage]').value() == "disabled")
	{
		$('.cfgHivernage').hide();
	}
	else
    {
        $('.cfgHivernage').show();
    }

    if ($('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgChauffage]').value() == "disabled"
        && $('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgHivernage]').value() == "disabled")
    {
        $('.cfgExterieur').hide();
    }
    else
    {
        $('.cfgExterieur').show();
    }
});

$('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgAsservissementExterne]').on('change', function ()
{
    if ($('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgAsservissementExterne]').value() == "disabled")
    {
        $('.cfgAsservissementExterne').hide();
        $('.cfgAsservissementExterne_hide').show();
    }
    else
    {
        $('.cfgAsservissementExterne').show();
        $('.cfgAsservissementExterne_hide').hide();
    }
});

$('.eqLogicAttr[data-l1key=configuration][data-l2key=sondeLocalTechnique]').on('change', function ()
{
    if ($('.eqLogicAttr[data-l1key=configuration][data-l2key=sondeLocalTechnique]').value() == "0")
    {
        $('.sondeLocalTechnique').hide();
    }
    else
    {
        $('.sondeLocalTechnique').show();
    }
});

$("body").delegate(".listCmdInfoAsservissement", 'click', function () 
{
    var el = $(this).closest('.form-group').find('.expressionAttr[data-l1key=cmd]');
    jeedom.cmd.getSelectModal({cmd: {type: 'info', subtype: 'binary'}}, function (result) 
	{
        el.value(result.human);
	});
});

$("body").delegate(".listCmdInfoArretTotal", 'click', function ()
{
    var el = $(this).closest('.form-group').find('.expressionAttr[data-l1key=cmd]');
    jeedom.cmd.getSelectModal({cmd: {type: 'info', subtype: 'binary'}}, function (result)
    {
        el.value(result.human);
    });
});

$("body").delegate(".listCmdInfoMarcheForcee", 'click', function ()
{
    var el = $(this).closest('.form-group').find('.expressionAttr[data-l1key=cmd]');
    jeedom.cmd.getSelectModal({cmd: {type: 'info', subtype: 'binary'}}, function (result)
    {
        el.value(result.human);
    });
});

$('body').delegate('.cmdAction.expressionAttr[data-l1key=cmd]', 'focusout', function (event) 
{
    var type = $(this).attr('data-type');
    var expression = $(this).closest('.' + type).getValues('.expressionAttr');
    var el = $(this);
    jeedom.cmd.displayActionOption($(this).value(), init(expression[0].options), function (html) 
	{
        el.closest('.' + type).find('.actionOptions').html(html);
	});
	
});

$("body").delegate('.bt_removeAction', 'click', function () 
{
    var type = $(this).attr('data-type');
    $(this).closest('.' + type).remove();
});

function saveEqLogic(_eqLogic) 
{
    if (!isset(_eqLogic.configuration)) 
	{
        _eqLogic.configuration = {};
	}
	
    _eqLogic.configuration.filtrationOn = $('#div_filtrationOn .filtrationOn').getValues('.expressionAttr');
    _eqLogic.configuration.filtrationStop = $('#div_filtrationStop .filtrationStop').getValues('.expressionAttr');

    _eqLogic.configuration.chauffageOn = $('#div_chauffageOn .chauffageOn').getValues('.expressionAttr');
    _eqLogic.configuration.chauffageStop = $('#div_chauffageStop .chauffageStop').getValues('.expressionAttr');

    _eqLogic.configuration.traitementOn = $('#div_traitementOn .traitementOn').getValues('.expressionAttr');
    _eqLogic.configuration.traitementStop = $('#div_traitementStop .traitementStop').getValues('.expressionAttr');
	
    _eqLogic.configuration.surpresseurOn = $('#div_surpresseurOn .surpresseurOn').getValues('.expressionAttr');
    _eqLogic.configuration.surpresseurStop = $('#div_surpresseurStop .surpresseurStop').getValues('.expressionAttr');
	
    _eqLogic.configuration.asservissement = $('#div_asservissement .asservissement').getValues('.expressionAttr');

    _eqLogic.configuration.arretTotal = $('#div_arretTotal .arretTotal').getValues('.expressionAttr');

    _eqLogic.configuration.marcheForcee = $('#div_marcheForcee .marcheForcee').getValues('.expressionAttr');

    $('#div_modes .mode').each(function () 
	{
        var existingMode = $(this).getValues('.modeAttr');
        existingMode = existingMode[0];
        existingMode.actions = $(this).find('.modeAction').getValues('.expressionAttr');
        _eqLogic.configuration.existingMode.push(existingMode);
	});
	
	return _eqLogic;
}

function printEqLogic(_eqLogic) 
{
	
    $('#div_filtrationOn').empty();
    $('#div_filtrationStop').empty();
	
    $('#div_chauffageOn').empty();
    $('#div_chauffageStop').empty();

    $('#div_traitementOn').empty();
    $('#div_traitementStop').empty();

    $('#div_surpresseurOn').empty();
    $('#div_surpresseurStop').empty();
	
    $('#div_modes').empty();
	
    $('#div_asservissement').empty();

    $('#div_arretTotal').empty();

    $('#div_marcheForcee').empty();

	if (isset(_eqLogic.configuration)) 
	{
        if (isset(_eqLogic.configuration.filtrationOn)) 
		{
            for (var i in _eqLogic.configuration.filtrationOn) 
			{
                addAction(_eqLogic.configuration.filtrationOn[i], 'filtrationOn');
			}
		}
        if (isset(_eqLogic.configuration.filtrationStop)) 
		{
            for (var i in _eqLogic.configuration.filtrationStop) 
			{
                addAction(_eqLogic.configuration.filtrationStop[i], 'filtrationStop');
			}
		}
		
        if (isset(_eqLogic.configuration.chauffageOn))
		{
            for (var i in _eqLogic.configuration.chauffageOn)
			{
                addAction(_eqLogic.configuration.chauffageOn[i], 'chauffageOn');
			}
		}
        if (isset(_eqLogic.configuration.chauffageStop))
		{
            for (var i in _eqLogic.configuration.chauffageStop)
			{
                addAction(_eqLogic.configuration.chauffageStop[i], 'chauffageStop');
			}
		}
        if (isset(_eqLogic.configuration.traitementOn))
        {
            for (var i in _eqLogic.configuration.traitementOn)
            {
                addAction(_eqLogic.configuration.traitementOn[i], 'traitementOn');
            }
        }
        if (isset(_eqLogic.configuration.traitementStop))
        {
            for (var i in _eqLogic.configuration.traitementStop)
            {
                addAction(_eqLogic.configuration.traitementStop[i], 'traitementStop');
            }
        }
        if (isset(_eqLogic.configuration.surpresseurOn))
		{
            for (var i in _eqLogic.configuration.surpresseurOn) 
			{
                addAction(_eqLogic.configuration.surpresseurOn[i], 'surpresseurOn');
			}
		}
        if (isset(_eqLogic.configuration.surpresseurStop)) 
		{
            for (var i in _eqLogic.configuration.surpresseurStop) 
			{
                addAction(_eqLogic.configuration.surpresseurStop[i], 'surpresseurStop');
			}
		}
		if (isset(_eqLogic.configuration.asservissement)) 
		{
            for (var i in _eqLogic.configuration.asservissement) 
			{
                addAsservissement(_eqLogic.configuration.asservissement[i]);
            }
        }
        if (isset(_eqLogic.configuration.arretTotal))
        {
            for (var i in _eqLogic.configuration.arretTotal)
            {
                addArretTotal(_eqLogic.configuration.arretTotal[i]);
            }
        }
        if (isset(_eqLogic.configuration.marcheForcee))
        {
            for (var i in _eqLogic.configuration.marcheForcee)
            {
                addMarcheForcee(_eqLogic.configuration.marcheForcee[i]);
            }
        }
    }
}

function addAction(_action, _type) 
{
    var div = '<div class="' + _type + '">';
    div += '<div class="form-group ">';
    div += '<label class="col-sm-1 control-label">{{Action}}</label>';
    div += '<div class="col-sm-1">';
    div += '<a class="btn btn-default btn-sm listCmdAction" data-type="' + _type + '"><i class="fa fa-list-alt"></i></a>';
    div += '</div>';
    div += '<div class="col-sm-3">';
    div += '<input class="expressionAttr form-control input-sm cmdAction" data-l1key="cmd" data-type="' + _type + '" />';
    div += '</div>';
    div += '<div class="col-sm-6 actionOptions">';
    div += jeedom.cmd.displayActionOption(init(_action.cmd, ''), _action.options);
    div += '</div>';
    div += '<div class="col-sm-1">';
    div += '<i class="fa fa-minus-circle pull-right cursor bt_removeAction" data-type="' + _type + '"></i>';
    div += '</div>';
    div += '</div>';
    $('#div_' + _type).append(div);
    $('#div_' + _type + ' .' + _type + ':last').setValues(_action, '.expressionAttr');
}

function addAsservissement(_info) 
{
    var div = '<div class="asservissement">';
    div += '<div class="form-group ">';
    div += '<label class="col-sm-1 control-label">{{Asservissement}}</label>';
    div += '<div class="col-sm-1">';
    div += '<a class="btn btn-default btn-sm listCmdInfoAsservissement"><i class="fa fa-list-alt"></i></a>';
    div += '</div>';
    div += '<div class="col-sm-3">';
    div += '<input class="expressionAttr form-control input-sm cmdInfo" data-l1key="cmd" />';
    div += '</div>';
    div += '<div class="col-sm-6 actionOptions">';
    div += '</div>';
    div += '<div class="col-sm-1">';
    div += '<i class="fa fa-minus-circle pull-right cursor bt_removeAction" data-type="asservissement"></i>';
    div += '</div>';
    div += '</div>';
    $('#div_asservissement').append(div);
    $('#div_asservissement .asservissement:last').setValues(_info, '.expressionAttr');
}

function addArretTotal(_info)
{
    var div = '<div class="arretTotal">';
    div += '<div class="form-group ">';
    div += '<label class="col-sm-1 control-label">{{Arrêt total}}</label>';
    div += '<div class="col-sm-1">';
    div += '<a class="btn btn-default btn-sm listCmdInfoArretTotal"><i class="fa fa-list-alt"></i></a>';
    div += '</div>';
    div += '<div class="col-sm-3">';
    div += '<input class="expressionAttr form-control input-sm cmdInfo" data-l1key="cmd" />';
    div += '</div>';
    div += '<div class="col-sm-6 actionOptions">';
    div += '</div>';
    div += '<div class="col-sm-1">';
    div += '<i class="fa fa-minus-circle pull-right cursor bt_removeAction" data-type="arretTotal"></i>';
    div += '</div>';
    div += '</div>';
    $('#div_arretTotal').append(div);
    $('#div_arretTotal .arretTotal:last').setValues(_info, '.expressionAttr');
}

function addMarcheForcee(_info)
{
    var div = '<div class="marcheForcee">';
    div += '<div class="form-group ">';
    div += '<label class="col-sm-1 control-label">{{Marche forcée}}</label>';
    div += '<div class="col-sm-1">';
    div += '<a class="btn btn-default btn-sm listCmdInfoMarcheForcee"><i class="fa fa-list-alt"></i></a>';
    div += '</div>';
    div += '<div class="col-sm-3">';
    div += '<input class="expressionAttr form-control input-sm cmdInfo" data-l1key="cmd" />';
    div += '</div>';
    div += '<div class="col-sm-6 actionOptions">';
    div += '</div>';
    div += '<div class="col-sm-1">';
    div += '<i class="fa fa-minus-circle pull-right cursor bt_removeAction" data-type="marcheForcee"></i>';
    div += '</div>';
    div += '</div>';
    $('#div_marcheForcee').append(div);
    $('#div_marcheForcee .marcheForcee:last').setValues(_info, '.expressionAttr');
}