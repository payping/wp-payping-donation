jQuery( document ).ready( function($){
//    var UserName;
//    var Avatar;
    $("button.button").on( 'click', function(){
        var ValTokenInput = $( this ).val();
        $("input#TokenCode").val( ValTokenInput );
        $("input#TokenCode").trigger("keyup");
    });
    var ajaxRequest;
    $('#TokenCode').on( 'keyup', function(){
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
                        'TokenCode': TokenCodeI
                    },
                    success: function( data ){
                        var json_result = JSON.parse( data );  //parsing here
                        userName = json_result['username'];
                        Profile = json_result['profilepicture'];
                        $("input#userName").val( userName );
                        $('div#pp-donate-logo').css("background-image", "url("+Profile+")");
                        $("tr#other_prices").keyup();
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
    
var Input_Price_Count = 0;  
$("button#add_price").on( 'click', function(){
    if( Input_Price_Count < 2 ){
        Input_Price_Count++;
        $('<input id="amount'+Input_Price_Count+'" type="text" placeholder="مبلغ '+Input_Price_Count+' پیشنهادی خود را وارد کنید" class="amountInput">').prependTo( "div#prices" );
        
        var price = $("#amount"+Input_Price_Count).val();
        var validatePrice = function( price ) {
        return /^(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d+)?$/.test(price);
        }
        if( price > 50000000 || price < 100 ){
            alert('مبلغ باید بیشتر از ۱۰۰ تومان و کمتر از ۵۰ میلیون تومان باشد');
        } 
    }
});
  
$("#amount0").blur(function() {
    var price = $("#amount0").val();
    var validatePrice = function( price ) {
      return /^(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d+)?$/.test(price);
    }
    if( price > 50000000 || price < 100 ){
        alert('مبلغ باید بیشتر از ۱۰۰ تومان و کمتر از ۵۰ میلیون تومان باشد');
    } 
});
    
$("tr#other_prices").on( 'keyup', function(){
    $( ".pp-select-amount" ).html('');
    var Count_Input_Number = $('input.amountInput').length;
    var i = 0;
    var Input_Val_Prices = [];
    for(i = 0; i < Count_Input_Number; i++){
        var Input_Val_Price = $( "input#amount"+i ).val();
        if( Input_Val_Price.length ){ 
            $( ".pp-select-amount" ).prepend( '<div class="select-amount" id="'+Input_Val_Price+'">'+Input_Val_Price+' تومان</div>' );
        }
        Input_Val_Prices.push( Input_Val_Price );
    }
        var userName = $("input#userName").val();
        var Profile = $( "div#pp-donate-logo" ).css('background-image');
            Profile = Profile.replace('url(','').replace(')','').replace(/\"/gi, "");
        var ppDescription = $( "textarea#description" ).val();
        var TextAreaValue = '<script src="https://cdn.payping.ir/statics/donate.min.js" type="text/javascript" charset="utf-8" pp-userName="'+userName+'" pp-description="'+ppDescription+'" pp-avatar="'+Profile+'" pp-amounts="'+Input_Val_Prices+'"></script>';
        $("textarea#donateScript").val( TextAreaValue );
});
    
});
