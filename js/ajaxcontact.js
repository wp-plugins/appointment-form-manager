/* START 29-06-15 */

function ajaxformsendmail(fname,lname,phone,email,location,visit,comment,userAnswer,num1,operand,num2)

//function ajaxformsendmail(fname,lname,phone,email,location,visit,comment,captcha)

/* END 29-06-15 */

		{ 

		jQuery.ajax({

		type: 'POST',

		url: ajaxcontactajax.ajaxurl,

		data: {

		action: 'ajaxcontact_send_mail',

		acffname: fname,

		acflname: lname,

		acfphone: phone,

		acfemail: email,

		acflocation: location,

		acfvisit: visit,

		acfcomment: comment,

		/* START 29-06-15 */

		/*acfcaptcha: captcha,*/



		userAnswer : userAnswer,

		num1 : num1,

		operand : operand,

		num2 : num2

		/* END 29-06-15 */

		},

		 

		success:function(data, textStatus, XMLHttpRequest){

		var id = '#ajaxcontact-response';

		if(data.success == true){ // if true (1)
		      setTimeout(function(){// wait for 5 secs(2)
			   location.reload(); // then reload the page.(3)
		      }, 5000); 
		}

		jQuery(id).html('');

		jQuery(id).append(data);

		},

		 

		error: function(MLHttpRequest, textStatus, errorThrown){

		alert(errorThrown);

		}

		 

		});

		//}
}
