jQuery( document ).ready( function($){
	var Input_Val_Prices = [];
	var scriptValue = $('#donateScript').val();
	if (scriptValue)  {
		var amounts = $(scriptValue).attr('pp-amounts');
		var profile = $(scriptValue).attr('pp-avatar');
		var username = $(scriptValue).attr('pp-userName');
		var desc = $(scriptValue).attr('pp-description');
    	if (amounts) {
	 		$("#price_switch").click();
      		var amountArray = amounts.split(',');
      		var amountsContainer = $('.pp-select-amount');
      		$.each(amountArray, function(index, value) {
        		var amount = $.trim(value);
				if (index === 0) {
					$("#amount0").val(value);
				} else {
					$('<div class="PriceInput"><input id="amount'+index+'" type="number" placeholder="مبلغ پیشنهادی خود را وارد کنید" class="amountInput" value="'+value+'"><span class="removeInput">X</span><span class="errorTxt"></span></div>').appendTo( "div#prices" );
				}
        		var divHtml = '<div class="select-amount" id="' + amount + '">' + amount + ' تومان</div>';
        		amountsContainer.append(divHtml);
      		});
		}
		if (username) {
			$("#userName").val(username);
		}
		if (profile) {
			$('#pp-donate-logo').css('background-image', 'url(' + profile + ')');
		}
		if (desc) {
			$("#ppDescription").text(desc);
			$("#description").val(desc);
		}
	}
function updateScript(){
	var userName = $("input#userName").val();
    var Profile = $( "div#pp-donate-logo" ).css('background-image');
        Profile = Profile.replace('url(','').replace(')','').replace(/\"/gi, "");
    var ppDescription = $( "textarea#description" ).val();
	Input_Val_Prices = [];
	$('.amountInput').each(function(index, element) {
    	var inputValue = $(element).val();
    	Input_Val_Prices.push(inputValue);
  	});
	var TextAreaValue = '<script src="https://cdn.payping.ir/statics/donate.min.js" type="text/javascript" charset="utf-8" pp-userName="'+userName+'" pp-description="'+ppDescription+'" pp-avatar="'+Profile+'" pp-amounts="'+Input_Val_Prices+'"></script>';
        $("textarea#donateScript").val( TextAreaValue );
	
}
    $("button.button").on( 'click', function(){
        var ValTokenInput = $( this ).val();
        $("input#TokenCode").val( ValTokenInput );
        $("input#TokenCode").trigger("keyup");
    });
    var ajaxRequest;
    $('#TokenCode').on( 'keyup', function(){
	$("#loader .spinner").addClass('active');
    if( $( this ).val().length ){
        var TokenCodeI = $( this ).val();
    }else{
        var TokenCodeI = 'NONE';  
    }
    clearTimeout( ajaxRequest );
    ajaxRequest = setTimeout( function( sn ){
                $.ajax({
                    url: donate_ajax_obj.ajaxurl, // or example_ajax_obj.ajaxurl if using on frontend
                    data: {
                        'action': 'Donate_PayPing_ajax_request',
                        'TokenCode': TokenCodeI,
						'donate_payping_nonce': $('input[name="donate_payping_nonce"]').val(),
                    },
                    success: function( data ){
                        var json_result = JSON.parse( data );  //parsing here
                        userName = json_result['username'];
                        Profile = json_result['profilepicture'];
                        $("input#userName").val( userName );
                        $('div#pp-donate-logo').css("background-image", "url("+Profile+")");
                        $("tr#other_prices").keyup();
						$("#loader .spinner").removeClass('active');
						updateScript();
                    },
                    error: function( errorThrown ){
                        console.log( errorThrown );
                    }
                });
            }, 200, TokenCodeI );
    });
    
$("input#userName").on( 'keyup', function(){
    $("tr#other_prices").keyup();
});

$("textarea#description").on( 'keyup', function(){
    $("p#ppDescription").html( $( this ).val() );
    $("tr#other_prices").keyup();
});
    
var Input_Price_Count = $(".PriceInput").length; 
	
$("button#add_price").on( 'click', function(){
    if( Input_Price_Count < 3 ){
        
        $('<div class="PriceInput"><input id="amount'+Input_Price_Count+'" type="number" placeholder="مبلغ پیشنهادی خود را وارد کنید" class="amountInput"><span class="removeInput">X</span><span class="errorTxt"></span></div>').appendTo( "div#prices" );
        Input_Price_Count++;
    }
});
$(document).on('click', '.removeInput', function() {
	Input_Price_Count--;
	var input = $(this).siblings('.amountInput').val();
	
  	$(this).parent().remove();
	$("#"+input).remove();
	$('.amountInput').each(function(index) {
    	$(this).removeAttr('id');
		var newId = 'amount' + index;
    	$(this).attr('id', newId);
  	});
	updateScript();
	
});
$(document).on('focusout', '.amountInput', function() {
	var inputValue = parseInt($(this).val());
	var $nearestErrorTxt = $(this).siblings('.errorTxt');
	if (inputValue < 1000 || inputValue > 50000000) {
      
      $nearestErrorTxt.text("لطفا مبلغ را درست وارد کنید. مبلغ باید بیشتر از ۱۰۰۰ تومان و کمتر از ۵۰ میلیون تومان باشد ");
    } else {
        $nearestErrorTxt.text("");
    }
	updateScript();
});


$("tr#other_prices").on( 'keyup', function(){
    $( ".pp-select-amount" ).html('');
    var Count_Input_Number = $('input.amountInput').length;
    var i = 0;
    
    for(i = 0; i < Count_Input_Number; i++){
        var Input_Val_Price = $( "input#amount"+i ).val();
        if( Input_Val_Price.length ){ 
            $( ".pp-select-amount" ).prepend( '<div class="select-amount" id="'+Input_Val_Price+'">'+Input_Val_Price+' تومان</div>' );
        }
    }
        
});

});
