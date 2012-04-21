function createCalendar(month, year, day)
{
	var curDate = new Date();
	
	// omzetten naar een int
	month = Number(month);
	year = Number(year);
	day = day == '' ? 0 : Number(day);
	
	var monthArray = new Array();
	monthArray[0] = 'januari';
	monthArray[1] = 'februari';
	monthArray[2] = 'maart';
	monthArray[3] = 'april';
	monthArray[4] = 'mei';
	monthArray[5] = 'juni';
	monthArray[6] = 'juli';
	monthArray[7] = 'augustus';
	monthArray[8] = 'september';
	monthArray[9] = 'oktober';
	monthArray[10] = 'november';
	monthArray[11] = 'december';
	
	month -= 1; // 0 = januari; 11 = december
	
	$('#maand_nav span').html(monthArray[month]+' '+year);
	
	// De maand die is opgegeven
	var huidigeMaand = new Date(year, month, 1);

	// aantal dagen in opgegeven maand
	daysInMonth = new Date(year,month+1,0).getDate();

	// dag in de week van de eerste dag in de opgegeven maand
	firstDay = huidigeMaand.getDay();

	// dag in de week van de laatste dag in de opgegeven maand
	lastDay = new Date(year,month,daysInMonth).getDay();

	var dummieMonth = month;
	var dummieYear = year;

	// Vorige maand berekenen
	if(dummieMonth-1 < 0) // Vorige maand ligt in het vorige jaar
	{
		dummieMonth = 11;
		dummieYear -= 1;
	}
	else // Vorige maand ligt in hetzelfde jaar
		dummieMonth -= 1;
	
	// De vorige maand
	var vorigeMaand = new Date(dummieYear, dummieMonth, 2);
	
	$('#maand_nav a.left').click(function(){
										   
										   createCalendar(vorigeMaand.getMonth()+1, vorigeMaand.getFullYear(), '');
										   return false;
										   
										   });
	
	var returner = '';
	
	returner += '<tr>';
	
	if(firstDay != 1)
	{
		var lastDateVorigeMaand = new Date(year, month, 0).getDate();
		
		firstDay = firstDay == 0 ? 7 : firstDay;
		for(var i=1; i <= firstDay-1; i ++)
		{
			var dag = (lastDateVorigeMaand-(firstDay-1)+i);
			
			var iDate = new Date(year, month-1, dag);
			
			var classMonth = vorigeMaand.getMonth()+1 < 10 ? '0'+(vorigeMaand.getMonth()+1) : vorigeMaand.getMonth()+1;
			var classCurrent = '';
			
			if(iDate.getDate() == curDate.getDate() && iDate.getMonth() == curDate.getMonth())
				classCurrent = 'current_day';
			
			returner += '<td class="last_month month_'+classMonth+' year_'+vorigeMaand.getFullYear()+' '+classCurrent+'">'+dag+'</td>';
		}
	}
	
	// Volgende maand berekenen
	if(dummieMonth+2 > 11) // Volgende maand ligt in het volgende jaar
	{
		dummieMonth = (dummieMonth+1)%11;
		dummieYear += 1;
	}
	else // Volgende maand ligt in hetzelfde jaar
		dummieMonth += 2;
		
	// De volgende maand
	var volgendeMaand = new Date(dummieYear, dummieMonth, 1);
	
	$('#maand_nav a.right').click(function(){
										   
										   createCalendar(volgendeMaand.getMonth()+1, volgendeMaand.getFullYear(), '');
										   return false;
										   
										   });
	
	for(var i=1; i <= daysInMonth; i++)
	{
		//$('#jahoor').append(i+"<br />");
		var iDate = new Date(year, month, i);
		var dayOfWeek = iDate.getDay();
		
		var classMonth = (month+1) < 10 ? '0'+(month+1) : month+1;
		var classCurrent = '';
		var classSelectedDay = '';
		
		if(iDate.getDate() == curDate.getDate() && iDate.getMonth() == curDate.getMonth())
		{
			classCurrent = 'current_day';
			if(day == 0)
				classSelectedDay = 'selected_day';
		}
			
		if(i == day)
			classSelectedDay = 'selected_day';
			
		if(i == 1 && curDate.getMonth() != month && day == 0)
			classSelectedDay = 'selected_day';
		
		returner += '<td class="month_'+classMonth+' '+classCurrent+' '+classSelectedDay+'">'+i+'</td>';
		
		if(dayOfWeek == 0 && i+7 >= daysInMonth)
		{
			returner += '</tr><tr class="last_row">';
		}
		else if(dayOfWeek == 0 && i != daysInMonth)
		{
			returner += '</tr><tr>';
		}
		else if(dayOfWeek == 0 && i == daysInMonth)
		{
			returner += '</tr>';
		}
	}
	
	if(lastDay > 0)
	{
		for(var i=1; i <= 7-lastDay; i ++)
		{
			var iDate = new Date(year, month+1, i);
			
			var classMonth = (volgendeMaand.getMonth()+1) < 10 ? '0'+(volgendeMaand.getMonth()+1) : (volgendeMaand.getMonth()+1);
			var classCurrent = '';
			
			if(iDate.getDate() == curDate.getDate() && iDate.getMonth() == curDate.getMonth())
				classCurrent = 'current_day';
			
			returner += '<td class="next_month month_'+classMonth+' year_'+volgendeMaand.getFullYear()+' '+classCurrent+'">'+i+'</td>';
		}
		returner += '</tr>';
	}
	
	$('#kalender').html(returner);

	// Aanpassen welke dag geselecteerd is na een klik
	$('#agenda table#kalender tr td').click(function()
	{
		$('#agenda table#kalender tr td.selected_day').removeClass('selected_day');
		
		if(!$(this).hasClass('last_month') && !$(this).hasClass('next_month'))
			$(this).addClass('selected_day');
	});
	
	changeTableDimensions();
	
	// Agenda items lijst leegmaken
	$(".items").html('');
	
	// Als de data al is opgehaald deze parsen
	if(xmlData != "")
		parseXml(xmlData, month+1, year);
}

function resetDimensions()
{
	$('.content').css('padding', '71px 0 93px');
}

function changeTableDimensions()
{
	var tableHeight = ($('#agenda').width()/7)*$('#agenda table#kalender tr').length;
	$('#agenda table#kalender').css('height', tableHeight+'px');
	$('#agenda').css('height', (tableHeight+50)+'px');

	var paddingTop = $('#agenda').position().top+$('#agenda table#kalender').height()+65;
	$('.content').css('padding', paddingTop+'px 0 109px');
}

function parseXml(xml, month, year)
{
	$('table#kalender tr td').click(function()
	{
		$(".items").html('');
	});
	
	$(xml).find("rooster events").each(function()
	{
		var events = '';
		var datum = $(this).attr('datum');
		var datum = datum.split('-');
		
		var lokaal, start, eind, summary, description;
		
		$(this).find('event').each(function()
		{	
			lokaal = $(this).find("lokaal").text();
			start = $(this).find("start").text();
			eind = $(this).find("eind").text();
			summary = $(this).find("summary").text();
			description = $(this).find("description").text();
	
			events += '<li>\
						<span class="time">'+start+'</span>\
						<h1>'+summary+'</h1>\
						<h2>'+lokaal+'</h2>\
						<div class="eind none">'+eind+'</div>\
						<div class="description none">'+description+'</div>\
						</li>';
		});
		
		var dag = Number(datum[0]);
		var maand = datum[1];
		var jaar = datum[2];
		
		var huidigeTd = $('td.month_'+maand).filter(function()
		{
			var regExp = new RegExp('^'+dag+'$');
			
			return regExp.test($(this).text());
		});
		
		huidigeTd.addClass('activity');
		
		if(!$(huidigeTd).hasClass('last_month') && !$(huidigeTd).hasClass('next_month'))
		{
			huidigeTd.click(function(){
								   $(".items").html(events);
								   
								   if(($(huidigeTd).hasClass('activity')))
								   {
									   
									   $('.items li').click(function()
										{
											start = $('.time', this).html();
											eind = $('.eind', this).html();
											description = $('.description', this).html();
											roosterKlik(start, eind, description, dag, month, year);
										});
								   }
								   });
		}
	});
	
	$('table#kalender tr td.last_month').each(function()
	{
		$(this).click(function()
		{
			month -= 1;
			if(month < 1)
			{
				month = 12;
				year -= 1;
			}
			createCalendar(month, year, $(this).html());
		});
	});
	
	$('table#kalender tr td.next_month').each(function()
	{
		$(this).click(function()
		{
			month += 1;
			if(month > 12)
			{
				month = 1;
				year += 1;
			}
			createCalendar(month, year, $(this).html());
		});
	});
	
	if($('.selected_day').hasClass('activity'))
	{
		$('.selected_day').trigger('click');
	}
		
}

function roosterKlik(startTime, eind, description, day, month, year)
{
	changePage('roosterdetail');
	$('#mededeling_wrapper .content').html('<div class="mededeling open auto_height">\
						<h1>'+startTime+' - '+eind+'</h1>\
						<p style="font-weight: normal;">'+description+'</p>\
					</div>\
					<a href="#" id="terug_button" onClick="createCalendar('+month+', '+year+', '+day+'); changePage(\'rooster\'); return false;"></a>');
	//location.replace('roosterdetail.html?start='+startTime+'&eind='+eind+'&description='+description);
}