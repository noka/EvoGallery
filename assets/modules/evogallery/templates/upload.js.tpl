<script>
/*setup for dropzone.js*/
Dropzone.autoDiscover = false;

$(function(){

    /*Dropzone*/
    var imageDropzone = new Dropzone("#dropzone",{
          paramName: "Filedata",  // The name that will be used to transfer the file
          url:'[+base_path+]action.php',
          maxFilesize:  [+upload_maxsize+], // MB
          parallelUploads: 20,
          /* accept rule*/
          acceptedFiles:"[+upload_images+]",
          /*notuse autoupload*/
          autoProcessQueue:false,
          addRemoveLinks:true,
          /**/
          dictDefaultMessage:"",
          dictInvalidFileType:"[+lang.invalidfiletype+]",
          dictFileTooBig:"[+lang.toobig+]",
          dictRemoveFile:"[+lang.delete+]",
          dictCancelUpload:"[+lang.cancel+]", 
        }
    );
    imageDropzone.on("success", function(file) {
      imageDropzone.removeFile(file);
    });

    $('.dropzone-actions-excute').click(function(){
        imageDropzone.processQueue() ;
        imageDropzone.on("queuecomplete",function(file){window.location.reload();});
    });
    
    $('.dropzone-actions-cancel').click(function(){
        imageDropzone.removeAllFiles(true);
    });

    /* ----TODO  .live は .onに置き換えること ---------------*/
    $('#cmdCntDel').live("click", function(event){
        var mode = $.getMode([+content_id+]);
        if(mode['mode'] !='contentid'){
            if(confirm('[+lang.delete_indoc_confirm+]')){
							$.post("[+base_path+]action.php",{[+params+], 'action': 'deleteall', 'mode': 'id', 'action_ids':  mode['action_ids']},
                                         function (data,status){
                                             $("#uploadList li.selected").each(function(i,e){$(e).remove();
                                          });
						});
            }
        }
        return false;
    });


	$.urlParam = function(name, link){
		var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(link);
		return (results && results[1]) || 0;
	}

	if($('#uploadList').length > 0){
        $(".thbButtons").hide();
        $(".thbSelect").hide();
        $("#uploadList li").live("mouseover", function(){
                $(this).find(".thbButtons").show();
                var sel = $(this).find(".thbSelect");
				if (!sel.hasClass('selected'));
					sel.show();
        });
        $("#uploadList li").live("mouseout", function(){
                $(this).find(".thbButtons").hide();
                if (!$(this).hasClass('selected'))
					$(this).find(".thbSelect").hide();
        });
        $(".thbButtons .delete").live("click", function(event){
            if(confirm('[+lang.delete_confirm+]')){
                $.get($(this).attr('href'));
                $(this).parent().parent('li').remove();
				if (!$("#uploadList li").length) {
					$("#selectallcontrols").hide();
					$("#sortcontrols").hide();
				}	
            }
            return false;
        });
        $(".edit").live("click", function(event){
            var link = $(this).attr("href");
            var overlay = $(this).overlay({
                api: 'true',
                target: '#overlay',
                oneInstance: true,
                onBeforeLoad: function(){
                    $("#overlay .contentWrap").load(link, function(){
                        var keyword_tags = new TagCompleter("keywords", "keyword_tagList", ",");
                    });
                },
                onClose: function(){
                    if($('.newimage').length > 0){
                        window.location.reload();
                    }
                },
                onLoad: function(){
                    $("#cmdsave").click(function(){
                        overlay.close();
                    });
                }
            });
            overlay.load();
            return false;
        });
        $(".thbSelect .select").live("click", function(event){
			$(this).closest('li').toggleClass('selected');
            return false;
        });
		$("#selectall").click( function() {
			$("#uploadList li").addClass('selected');
			$("#uploadList li .thbSelect").show();
		});
		$("#unselectall").click( function() {
			$("#uploadList li").removeClass('selected');
			$("#uploadList li .thbSelect").hide();
		});

        $("#uploadList").sortable();

		$.getMode = function(content_id) {
			var ids = [];
			$("#uploadList li.selected").each(function(){
				ids.push($(this).find('input').val());
			});
			if (ids.length>0)
				return {'mode': 'id','action_ids': ids};
			else
				return {'mode': 'contentid', 'action_ids': content_id};
			
		}


		$('#cmdCntMoveTo').click(function(){
			var overlay = $(this).overlay({
				api: 'true',
				target: '#moveto-popup',
				oneInstance: true,
				closeOnEsc: true,
				closeOnClick: false,
				onLoad: function() {
					top.tree.ca = 'move';
				},
				onClose: function() {
					top.tree.ca = '';
					window.location.reload();
				}
			});
			overlay.load();
			$('#moveto').click( function(){
				var target = $("#movetarget_id").val();
				if (target!=0) {
					var mode = $.getMode([+content_id+]);
					$.post("[+base_path+]action.php", 
						{[+params+], 'action': 'move', 'target': target, 'mode': mode['mode'], 'action_ids': mode['action_ids'].toString()},
						function() {
							overlay.close();
						}
					);
				}
				return false;
			});
		});
	}

});

$(window).load(function() {
	if (!$("#uploadList li").length) {
		$("#selectallcontrols").hide();
		$("#sortcontrols").hide();
	}	
});

top.main.setMoveValue = function(pId, pName) {
	if (pId!=0) {
		$("#movetarget_id").val(pId);
		$('#movetarget_doc').html("Document: <strong>" + pId + "</strong> (" + pName + ")");
	}
}


-->
</script>
