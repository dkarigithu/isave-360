
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta content="text/html; charset=UTF-8" http-equiv="Content-Type"/>
<title>Live Search Using Jquery,Mysql and PHP</title>
<link href="css/style.css" type="text/css" rel="stylesheet"/>
<link href="css/reset.css" type="text/css" rel="stylesheet"/>

<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript">
 
 
$(document).ready(function() {
	$("#total_users").keydown(function(event) {
        // Allow: backspace, delete, tab and escape
        if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 || 
             // Allow: Ctrl+A
            (event.keyCode == 65 && event.ctrlKey === true) || 
             // Allow: home, end, left, right
            (event.keyCode >= 35 && event.keyCode <= 39)) {
                 // let it happen, don't do anything
                 return;
        }
        else {
            // Ensure that it is a number and stop the keypress
            if ((event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 )) {
                event.preventDefault(); 
            }   
        }
	});
	
	
	$('#sms').bind('keyup', function() {
		var smslength = 160;
		var cnt = $(this).val().length;
		if (cnt > smslength) smslength = 153;	//if a message has to be split in two or more, then the message lenght should be 153, other characters for meta data
		var remainder = cnt % smslength;
		var quotient = Math.floor(( cnt - remainder ) / smslength);
		if (remainder > 0) quotient++;//add an extra message
		$('#counter').html(cnt);
		$('#message_count').html(quotient);
	});	
	
	$('#send_sms').mouseup(function() {
		sendsms();
	});	
	
	
	
	
	$("#search_input").mouseup(function()
	{
			$("#paginator").remove();
			$("#content").load("ajax.php?action=get_rows"+ "&" + search_string());
			
			var old_search = search_string() + " ";
			$.get("ajax.php?action=row_count" + "&" + search_string(), function(data){
				if(data == '') data = 0;
				$("#total_users").val(data);
				$("#total_users_limit").val(data);
				$("#page_count").val(Math.ceil(data / 10));
				if(data > 0){
					 $("#smsform").show('slow','swing');
				}else{
					$("#smsform").hide('slow','swing');///xxxx change to hide
				}
				generateRows(1);
				$("#search_str").val(search_string());
			}
			
			
			
			
			);//end of get function

		//}	
	});
	
	
	
	
	
	
});





function search_string(){
	var search_input = 'sendads=1&agemin=' + $(agemin).val() + '&agemax=' + $(agemax).val() + '&sexmale=' + $(sex_male).is(':checked') + '&sexfemale=' + $(sex_female).is(':checked') + '&location=' + $(mylocation).val() + '&limit=' + $(total_users).val() + '&discount=' + ($(discount).is(':checked') ? 1 : 0) + '&ref_opt=' + 
	$('input[name=referrer_opt]:checked').val() + '&ref=' + $(referrer).val();
	var male_part = (!$(sex_male).is(':checked') && !$(sex_female).is(':checked')) ? "true" : $(sex_male).is(':checked');
	var female_part = (!$(sex_male).is(':checked') && !$(sex_female).is(':checked')) ? "true" : $(sex_female).is(':checked');
	var discount_part = ' Discount: '+($(discount).is(':checked') ? 1 : 0);
	
	var referer_opt_part = ' referrer:' + $('input[name=referrer_opt]:checked').val();
	var referer_part = ' referrer:' + $(referrer).val();	
	
	var search_input_readable = 'Minimum age: ' + $(agemin).val() + ' Maximum age: ' + $(agemax).val() + ' Males: ' + male_part + ' Females: ' + female_part + ' Location: ' +
	 (($(mylocation).val() == '')? 'anywhere' : $(mylocation).val()) + 
	 ' total users:' + $(total_users).val() + discount_part + referer_part+referer_opt_part;
	var dataString =  search_input;
	$("#search_performed").html(search_input_readable);
	return(search_input);	
}

function generateRows(selected) {
	var pages = $("#page_count").val();
	
	if (pages <= 5) {
		$("#content").after("<div id='paginator'><a href='#' class='pagor selected'>1</a><a href='#' class='pagor'>2</a><a href='#' class='pagor'>3</a><a href='#' class='pagor'>4</a><a href='#' class='pagor'>5</a><div style='clear:both;'></div></div>");
		$(".pagor").click(function() {
			var index = $(".pagor").index(this);
			$("#content").load("ajax.php?action=get_rows&start=" + index + "&"+search_string());
			$(".pagor").removeClass("selected");
			$(this).addClass("selected");
		});		
	} else {
		if (selected < 5) {
			// Draw the first 5 then have ... link to last
			var pagers = "<div id='paginator'>";
			for (i = 1; i <= 5; i++) {
				if (i == selected) {
					pagers += "<a href='#' class='pagor selected'>" + i + "</a>";
				} else {
					pagers += "<a href='#' class='pagor'>" + i + "</a>";
				}				
			}
			pagers += "<div style='float:left;padding-left:6px;padding-right:6px;'>...</div><a href='#' class='pagor'>" + Number(pages) + "</a><div style='clear:both;'></div></div>";
			
			$("#paginator").remove();
			$("#content").after(pagers);
			$(".pagor").click(function() {
				updatePage(this);
			});
		} else if (selected > (Number(pages) - 4)) {
			// Draw ... link to first then have the last 5
			var pagers = "<div id='paginator'><a href='#' class='pagor'>1</a><div style='float:left;padding-left:6px;padding-right:6px;'>...</div>";
			for (i = (Number(pages) - 4); i <= Number(pages); i++) {
				if (i == selected) {
					pagers += "<a href='#' class='pagor selected'>" + i + "</a>";
				} else {
					pagers += "<a href='#' class='pagor'>" + i + "</a>";
				}				
			}			
			pagers += "<div style='clear:both;'></div></div>";
			
			$("#paginator").remove();
			$("#content").after(pagers);
			$(".pagor").click(function() {
				updatePage(this);
			});		
		} else {
			// Draw the number 1 element, then draw ... 2 before and two after and ... link to last
			var pagers = "<div id='paginator'><a href='#' class='pagor'>1</a><div style='float:left;padding-left:6px;padding-right:6px;'>...</div>";
			for (i = (Number(selected) - 2); i <= (Number(selected) + 2); i++) {
				if (i == selected) {
					pagers += "<a href='#' class='pagor selected'>" + i + "</a>";
				} else {
					pagers += "<a href='#' class='pagor'>" + i + "</a>";
				}
			}
			pagers += "<div style='float:left;padding-left:6px;padding-right:6px;'>...</div><a href='#' class='pagor'>" + pages + "</a><div style='clear:both;'></div></div>";
			
			$("#paginator").remove();
			$("#content").after(pagers);
			$(".pagor").click(function() {
				updatePage(this);
			});			
		}
	}
}

function updatePage(elem) {
	// Retrieve the number stored and position elements based on that number
	var selected = $(elem).text();

	// First update content
	$("#content").load("ajax.php?action=get_rows&start=" + (selected - 1) + "&"+search_string());
	
	// Then update links
	generateRows(selected);
}

function sendsms(){
	if($("#total_users").val() > $("#total_users_limit").val()){
		alert("The maximum number of users available to message to is: "+$("#total_users_limit").val()+". Either change your criteria or update correct the number of users you want to message");
		return(null);
	}
	
	if($("#total_users").val() <=0 || $("#total_users").val()==""){
		alert("Please enter a valid number of users to message to");
		return(null);
	}
	
	
	
	var textmessage = $('#sms').val();
	if(textmessage == ""){
		alert("Please enter a message to send");
		return(null);
	}
	$("#sendresults").load("ajax.php?action=send_sms&smstext" + textmessage  + "&"+search_string());
}

</script>
<style type="text/css">
/*This css contains code for the statis loading image in the right of the textbox */
body.faq .faqsearch .faqsearchinputbox input {
	font-size:16px;
	color:#6e6e6e;
	padding:10px;
	border:none;
	background:url(img/loading_static.gif) no-repeat right 50%;
	width:510px;
}
/*The css class below contains the animated loading image .this will be added on the dom later with Jquery*/
body.faq .faqsearch .faqsearchinputbox input.loading {
	background:url(img/loading_animate.gif) no-repeat right 50%;
}
</style>
</head>
<body id="nitropage" class="page_page">

    Search for users who fit your criteria (age, sex, location) - send an advert to them if necessary
    <div id="search_performed"></div>
                <table width="370" border="1" class="center">
                  <tr>
                    <td colspan="3">Enter search parameters</td>
                  </tr>
                  <tr>
                    <td width="63">Age</td>
                    <td width="16" colspan="2">Between: <input  name="agemin"  type="text" id="agemin" value="0" size="1" maxlength="3" />
                     and <input  name="agemax" type="text" id="agemax" value="100" size="1" maxlength="3" /> years</td>
                  </tr>
                  <tr>
                    <td>Sex</td>
                    <td>Male: <input name="sex_male" type="checkbox" id ="sex_male"   title="Male"/>
                		 Female:<input type="checkbox" name="sex_female" id="sex_female"   title="Female" />
                	</td>
                    <td>&nbsp;</td>
                  </tr>
                  <tr>
                    <td>Location</td>
                    <td><input  name="mylocation" type="text" id="mylocation"  size="20" maxlength="20" /></td>
                    <td>&nbsp;</td>
                  </tr>
                  <tr  style="background-color:#FFFFE8">
                    <td>Referrer</td>
                    <td><input  name="referrer" type="text" id="referrer"  size="20" maxlength="20" /><br>
						<INPUT TYPE=RADIO NAME="referrer_opt" VALUE="0" id="ig" checked ="checked"><label for = "ig">Ignore (all results returned)</label><BR>
                        <INPUT TYPE=RADIO NAME="referrer_opt" VALUE="1" id="in"><label for = "in">Include (for discounts) - These are customers I got myself so I pay less for them</label><BR>
                        <INPUT TYPE=RADIO NAME="referrer_opt" VALUE="2" id="ex"><label for = "ex">Exclude  (for additional adverts) to customers I didn't get</label>
					</td>
                    <td></td>
                  </tr>
                  <tr>
                  	<td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td><input name="query" id="search_input" type="button" value="Search" /></td>
                  </tr>
</table>
<div id="smsform" style="display:none;">
    <label for="sms">SMS</label>
    <textarea name="sms" id="sms" cols="45" rows="5"></textarea>
    <br>

  Characters<div id="counter"></div> Messages:<div id="message_count"></div>
Total users to send message to:
    <input  name="total_users" type="text" id="total_users"  size="1" maxlength="7" />  <input name="send" id="send_sms" type="button" value="Send SMS" />
    
    <input type="checkbox" name="discount" id="discount"   title="Apply Discount" /> Apply discounted rate (usually if for referrers wanting to send messages)
</div>
<div id="searchresultdata" class="faq-articles"> </div>


<div id="sendresults"></div>

<div id="content"></div>
	<input type="hidden" name="page_count" id="page_count" />
    <input type="hidden" name="total_users_limit" id="total_users_limit">
	<input type="hidden" name="search_str" id="search_str" />    
</body>

</html>