
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

function printMacLan(orbivo,_myIp){
    
    
	$.ajax({// fonction permettant de faire de l'ajax
            type: "POST", // methode de transmission des données au fichier php
            url: "plugins/ssaOrvibo/core/ajax/ssaOrvibo.ajax.php", // url du fichier php
            data: {
                action: "macfinder",  
            	ip : _myIp,
                logicalId: orbivo,
            },
            dataType: 'json',
			async: true,
			global : false,
            error: function (request, status, error) {
            	handleAjaxError(request, status, error);
            },
	   success: function(data) 
           { 
            
            if (data.state != 'ok') {
            	$('#div_alert').showAlert({message: data.result, level: 'danger'});
            	return;
            }
               
            $('#ssaOrviboAddrMacc').val(data.result);
            
            }
    });
}

function learnIr(orbivo,element,flag,rounded){
    
    
	$.ajax({// fonction permettant de faire de l'ajax
            type: "POST", // methode de transmission des données au fichier php
            url: "plugins/ssaOrvibo/core/ajax/ssaOrvibo.ajax.php", // url du fichier php
            data: {
                action: "learnIr",  
                logicalId: orbivo,
            	
            },
            dataType: 'json',
			async: true,
			global : false,
            error: function (request, status, error) {
            	handleAjaxError(request, status, error);
                flag.val("");
                rounded.prop('checked', false);
            },
	   success: function(data) 
           { 
            
            if (data.state != 'ok') {
            	$('#div_alert').showAlert({message: data.result, level: 'danger'});
            	return;
            }
             
             element.val(data.result);
             flag.val("checked");
             
             rounded.prop('checked', true);
            
            }
    });
}


function testIr(orbivo,cmd){
    
       
	$.ajax({// fonction permettant de faire de l'ajax
            type: "POST", // methode de transmission des données au fichier php
            url: "plugins/ssaOrvibo/core/ajax/ssaOrvibo.ajax.php", // url du fichier php
            data: {
                action: "sendIr",  
                logicalId: orbivo,
            	cmdId: cmd,
            },
            dataType: 'json',
			async: true,
			global : false,
            error: function (request, status, error) {
            	handleAjaxError(request, status, error);
            },
	   success: function(data) 
           { 
            
                if (data.state != 'ok') {
                    $('#div_alert').showAlert({message: data.result, level: 'danger'});
                    return;
                }
             
            
            
            }
    });
}




$("#table_cmd").delegate('.bt_ssaOrviboLearn', 'click', function () {
    
    var el = $(this);
    codeIr= el.closest('.ssaOrviboIr').find('.ssaOrviboCodeIr');
    flag= el.closest('.ssaOrviboIr').find('.ssaOrviboLearnFlag');
    rounded= el.closest('.ssaOrviboIr').find('.ssaOrviboLearnRounded');
   
    orbivo=$("#ssaOrviboId").val();
    learnIr(orbivo,codeIr,flag,rounded);
});


$("#table_cmd").delegate('.bt_ssaOrviboTest', 'click', function () {
    
    var el = $(this);
    codeIr= el.closest('.ssaOrviboIr').find('.ssaOrviboCodeIr');
    orbivo=$("#ssaOrviboId").val();
    
    cmd=$(this).closest("tr").find("input.ssaOrviboCmdLogicalId");
    
    testIr(orbivo,cmd.val());
});

 
$("#table_cmd").delegate('.ssaOrviboCmdName', 'change', function () {
    
    var el = $(this);
    logical= el.closest('.ssaOrviboCmd').find('.ssaOrviboCmdLogicalId');
    logical.val(el.val());
    
});

$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
/*
 * Fonction pour l'ajout de commande, appellé automatiquement par plugin.template
 */
$("#bt_addmyOrviboAction").on('click', function(event) {
    var _cmd = {type: 'action'};
    addCmdToTable(_cmd);
});

function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
    var random = Math.floor((Math.random() * 1000000) + 1);
    
    
    
     if (init(_cmd.type) == 'action') {
        var tr = '';
        tr += '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
        tr += '<td>';
        tr += '     <span class="cmdAttr" data-l1key="id"></span>';
        tr += '</td>';
        tr += '<td>';
        tr += '     <input class="cmdAttr form-control type input-sm" data-l1key="type" value="info" disabled style=" display : none; margin-bottom : 5px;" />';
        tr += '     <span class="subType" subType="' + init(_cmd.subType) + '" style=" display : none; "></span>';
        tr += '     <div class="ssaOrviboCmd">';
        tr += '         <input class="ssaOrviboCmdName cmdAttr form-control input-sm" data-l1key="name" >';
        tr += '         <input class="ssaOrviboCmdLogicalId cmdAttr form-control input-sm" data-l1key="logicalId" style=" display : none; ">';
        tr += '     </div>';
        tr += '</td>';
        tr += '<td>';
        tr += '     <div class="ssaOrviboIr">';
    
        tr += '         <textarea id="codeIr_'+ random +'"  style="height : 95px;" class="ssaOrviboCodeIr  expertModeVisible cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="codeIr" placeholder="{{CodeIr}}"></textarea>';
       
        tr += '         <input id="learn_'+ random +'" class="ssaOrviboLearnFlag cmdAttr form-control input-sm" data-l1key="configuration"  data-l2key="apprentissage" style=" display : none; ">';        

        
        tr += '          <span class="input-group-btn">';
        
        tr += '           <button type="button" class="btn btn-default bt_ssaOrviboLearn" data-value="learn" data-target="codeIr__'+ random +'" data-toggle="spinner">';
        
        tr += '             <span class="roundedOne">';
        tr += '                 <input class="ssaOrviboLearnRounded" type="checkbox" value="None" id="roundedOne_'+ random +'" name="check" '+_cmd.configuration.apprentissage+'/>';
        tr += '                 <label for="roundedOne_'+ random +'"></label>';
        tr += '             </span>';
                
        tr += '            <span class="">&nbsp;Apprentissage</span>';
        tr += '           </button>';
       
        tr += '           <button type="button" class="btn btn-default bt_ssaOrviboTest" data-value="test" data-target="codeIr__'+ random +'" data-toggle="spinner">';
        tr += '             <span class="glyphicon glyphicon-play-circle">&nbsp;Test</span>';
        tr += '           </button>';
        tr += '         </span>';
        
        
        tr += '     </div>';
       
        tr += ' <td>';
    tr += '     <i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
    tr += ' </td>';
   
        tr += '</td>';
        
        
        $('#table_cmd tbody').append(tr);
        
        
        $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    }
    
}


$('#ssaOrviboMacSearch').on('click', function () {
    
   printMacLan($("#ssaOrviboId").val(),$("#ssaOrviboAddrIp").val());
});