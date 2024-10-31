var errorCallback = function(data) {
    console.log(data);
};

var tokenRequest = function(e) {
  if( $('#cardNumber').val()==""){
    
    $("#cardError").html("Card Number  is required");
    return false;
   }
 
  
  if( $('#expMonth').val()=="00"){
    
    $("#cardError").html("Month is required");
    return false;
  }
 
  if( $('#expYear').val()=="0000"){
    
    $("#cardError").html("Year is required");
    return false;
  }
  if( $('#securityCode').val()==""){
    
    $("#cardError").html("CVV is required");
    return false;
  }
  
  let result = CardTokenize("cardNumber", "expMonth", "expYear", "securityCode", "cardError", "onepayResult");
  if(result){
    
    var jsonReceivedData = document.getElementById('onepayResult').value;
    const myArr  = JSON.parse(jsonReceivedData);
    const tokenForNextUse = myArr.token;
    var checkout_form = $( 'form.woocommerce-checkout' );
    checkout_form.append('<input type="hidden" id="ecard_token" name="ecard_token" value="'+tokenForNextUse+'">');
  }else{
    
    return false;
  }
  
};

jQuery(function($){
	var checkout_form = $( 'form.woocommerce-checkout' );
	checkout_form.on( 'checkout_place_order', tokenRequest );
  
});


