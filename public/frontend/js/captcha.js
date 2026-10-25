var total;

function getRandom(){return Math.ceil(Math.random()* 20);}
function createSum(){
		var randomNum1 = getRandom(),
			randomNum2 = getRandom();
	total =randomNum1 + randomNum2;
  $( "#question" ).text( randomNum1 + " + " + randomNum2 + "=" );  
  $("#ans").val('');
  checkInput();
}

function checkInput(){
		var input = $("#ans").val(), 
    	slideSpeed = 200,
      hasInput = !!input, 
      valid = hasInput && input == total;
	  
	  var cls = valid ? 'msgok' : 'msgerr';
	  var msgtxt = valid ? 'Validation complete' : 'Validation failed';
    //$('#valmessage').toggle(!hasInput);
	if(hasInput){
		$('#valmessage').attr("class", cls);
		$('#valmessage').html(msgtxt);
		//$('#valmessage').toggle(!hasInput);
	} else {
		$('#valmessage').attr("class", 'msgerr');
		$('#valmessage').html("Please Verify.");
	}
    
    $('button[type=submit]').prop('disabled', !valid);  
    // $('#success').toggle(valid);
    // $('#fail').toggle(hasInput && !valid);
}
 
 
$(document).ready(function(){
	//create initial sum
	createSum();
	// On "reset button" click, generate new random sum
	$('button[type=reset]').click(createSum);
	// On user input, check value
	$( "#ans" ).keyup(checkInput);
});