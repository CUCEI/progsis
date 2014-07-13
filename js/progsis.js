$(document).ready(function () {
	$(':button').click(function(){
		var formData = new FormData($('form')[0]);
		$.ajax({
		    url: 'ajax/compila.php',  
		    type: 'POST',
		    xhr: function() {  
		        var myXhr = $.ajaxSettings.xhr();
		        if(myXhr.upload){ 
		            myXhr.upload.addEventListener('progress',progreso, false); 
		        }
		        return myXhr;
		    },
		    
		    success: function(data){
		    	$(".compilado").html(data);
		    },
		    error: function(jqXHR, textStatus, errorThrown){
		    	console.log(jqXHR);
		    	console.log(textStatus);
		    	console.log(errorThrown);
		    },
		    
		    data: formData,
		    
		    cache: false,
		    contentType: false,
		    processData: false
		});
	});
	function progreso(e){
    if(e.lengthComputable){
        $('progress').attr({value:e.loaded,max:e.total});
    }
}
});