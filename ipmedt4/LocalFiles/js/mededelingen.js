function parseXmlMededelingen(xml)
{
	$("#mededelingen_wrapper .content").html("");
	
	$(xml).find("course").each(function()
	{
		var courseTitel = $(this).find("courseTitel").text();
		var aantalMededelingen = $(this).find("aantalMededelingen").text();
		var klasse = '';

		if(aantalMededelingen > 0)
			klasse = ' class="toggle"';

		var mededeling = '<div class="mededeling">\
							<h1><a href="#"'+klasse+'>' + courseTitel + ' <span>(' + aantalMededelingen + ')</span></a></h1>';
							
		if(aantalMededelingen > 0)
		{
			mededeling += '<ul>';

			$(this).find('mededeling').each(function()
			{
				var titel = escape($(this).find("titel").text());
				var bericht = escape($(this).find("bericht").text());
					
				mededeling += '<li><a href="" onclick="mededelingKlik(\''+titel+'\', \''+bericht+'\'); return false;">' + unescape(titel) + '</a></li>';						
			});
			
			mededeling += '</ul>';
		}
		
		mededeling += '</div>';

		$("#mededelingen_wrapper .content").append(mededeling);
	});
	
	$('#mededelingen_wrapper .content').append('<a href="" id="terug_button" onclick="changePage(\'home\'); return false;"></a>');
	
	$('#mededelingen_wrapper .mededeling h1 a.toggle').click(function(e, handler){
																	   $(this).parent().parent().toggleClass('open').toggleClass('auto_height', 500);
																	   return false;
																});
}

function mededelingKlik(titel, bericht)
{
	changePage('mededeling');
	$('#mededeling_wrapper .content').html('<div class="mededeling open auto_height">\
							<h1>'+unescape(titel)+'</h1>\
							<p>'+unescape(bericht)+'</p>\
						</div>\
						<a href="#" id="terug_button" onclick="changePage(\'mededelingen\'); return false;"></a>');
}