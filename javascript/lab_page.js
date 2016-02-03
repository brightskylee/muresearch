$(function(){
	var pic2div=function($pic){
		var url = $pic.css("background-image");
		url=url.slice(5,url.length-2);
		$pic.css("background-image","none");
		$pic.css("border","2px dotted #337ab7")
		$pic.css("cursor","text");
		$pic.css("outline","none");
		$pic.attr("contenteditable","true");
		$pic.html(url);
	}
	var div2pic=function(url,$pic_div){
		$pic_div.css("background-image","url("+url+")");
		$pic_div.css("border","none")
		$pic_div.css("cursor","pointer");
		$pic_div.attr("contenteditable","false");
		$pic_div.html("");
	}
	$("#projects").on('click','#add-project-btn',function(event){
		event.preventDefault();
		var template = $("<div style='display:none;' class='project add-new'><div class='project-img' contenteditable='true'>Picture URL</div><div class='project-description' contenteditable='true'>New description &nbsp;&nbsp;&nbsp;</div><div><button class='btn btn-default removeButton delete-project-btn white-btn' aria-label='delete project'><span class='glyphicon glyphicon-trash' aria-hidden='true'></span></button></div></div>");

		$("#projects .content").prepend(template);
		template.slideDown("1000");
	})
	.on('click','.delete-project-btn',function(event){
		event.preventDefault();
		$parent = $(this).parent().parent();
		$parent.slideUp("1000");
		setTimeout(function (){
    		$parent.remove();
  		}, 1000);			
	})
	.on('click','.project-img',function(){
		if($(this).css("background-image")=="none"){
			//do nothing
		}
		else{
			/*var url = $(this).css("background-image");
			url=url.slice(5,url.length-2);
			$(this).css("background-image","none");
			$(this).css("border","2px dotted #337ab7")
			$(this).css("cursor","text");
			$(this).css("outline","none");
			$(this).attr("contenteditable","true");
			$(this).html(url);*/
			pic2div($(this));
			$(this).parent().append("<div style='width:24%;text-align:center;'><button type='button' class='white-btn small-btn upload-project-pic'>Upload</button> <button class='white-btn small-btn cancel-upload-project-pic'>Cancel</button></div>");
		}
	})
	.on('click','.upload-project-pic',function(){
		$pic_div=$(this).parent().parent().children(".project-img");
		var new_url=$pic_div.html();
		div2pic(new_url,$pic_div);
		$(this).parent().remove();
	})
	.on('click','.cancel-upload-project-pic',function(){
		$pic_div=$(this).parent().parent().children(".project-img");
		var old_url=$pic_div.parent().find('input[name="old_project_img_url"]').val();
		div2pic(old_url,$pic_div);
		$(this).parent().remove();
	})
	.on('click','#submit-projects',function(){
		event.preventDefault();
		//var lab_id=$(this).parent().children('input[name="lab_id"]').val();
		var new_projects='{"new_projects":{"new_project":[';
		$(this).parent().parent().find('.project').each(function(i,n){
			var obj = $(n);
			var id=obj.find('input[name="project_id"]').val();
			if(obj.find('.project-img').css('background-image')=='none')
				var new_img=obj.find('.project-img').html();
			else
				var new_img=obj.find('.project-img').css('background-image');
				new_img=new_img.slice(5,new_img.length-2);
			var new_description=obj.find('.project-description').html();
			new_projects+='{"id":'+id+',"img":"'+new_img+'","description":"'+new_description+'"},';
		});
		new_projects=new_projects.substring(0,new_projects.length-1);
		new_projects+=']}}';

		$(this).parent().parent().prepend(new_projects);
		//Ajax
		var url = "lab_ajax.php";
		$.ajax({
			type: "post",
			url: url,
			dataType: "text",
			data: new_projects,//{"new_projects":{"id":"1234","img":"1234","description":"431"}},//
			success: function (msg) {
				alert('Success in ajax'+msg);
			},
			error: function(x,e) {
				if (x.status==0) {
					alert(x.responseText);
				} else if(x.status==404) {
					alert('Requested URL not found.');
				} else if(x.status==500) {
					alert('Internel Server Error.');
				} else if(e=='parsererror') {
					alert('Error. Parsing JSON Request failed.');
				} else if(e=='timeout'){
					alert('Request Time out.');
				} else {
					alert('Unknow Error: '+x.responseText);
				}
  			}
		});
	});
	
	$("#members").on('click','#add-member-btn',function(event){
		event.preventDefault();
		var template = $("<div style='display:none;' class='member add-new'><div class='member-img' contenteditable='true'>Picture URL</div><div class='member-info'><div class='member-name' contenteditable='true'>Member Name&nbsp;&nbsp;&nbsp;</div><div class='member-description' contenteditable='true'>Member Description</div><div><button class='btn btn-default removeButton delete-member-btn white-btn' aria-label='delete member'><span class='glyphicon glyphicon-trash' aria-hidden='true'></span></button></div></div>");

		$("#members .content").prepend(template);
		template.slideDown("1000");
	})
	.on('click','.delete-member-btn',function(event){
		event.preventDefault();
		$parent = $(this).parent().parent().parent();
		$parent.slideUp("1000");
		setTimeout(function (){
    		$parent.remove();
  		}, 1000);
	});

	$("#lab-description").on('click','#submit-lab-description-btn',function(){
		this.form.lab_description.value=$("#lab_description").html();
		$form = this.closest('form');
		var params = jQuery($form).find(":input").serialize();
		//alert(params);

		//Ajax
		var url = "lab_ajax.php";
		$.ajax({
			type: "post",
			url: url,
			dataType: "json",
			data: params,
			success: function (msg) {
				
			},
			error: function () {
				alert('Error in ajax');
			}
		});
	});
});