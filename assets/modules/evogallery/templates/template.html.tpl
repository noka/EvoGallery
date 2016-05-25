<!DOCTYPE>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>Gallery Management Module</title>
	<link rel="stylesheet" type="text/css" media="screen" href="[+base_url+]assets/modules/evogallery/templates/screen.css">
	<link rel="stylesheet" type="text/css" media="screen" href="[+base_url+]assets/modules/evogallery/js/dropzone/dropzone.css">
	<link rel="stylesheet" type="text/css" media="screen" href="[+base_url+]assets/modules/evogallery/js/overlay/overlay-minimal.css" >
	<link rel="stylesheet" type="text/css" media="screen" href="[+base_url+]assets/modules/evogallery/js/tags/tags.css">
	<script type="text/javascript" src="[+base_url+]assets/modules/evogallery/js/jquery-1.8.3.min.js"></script>
	<script type="text/javascript" src="[+base_url+]assets/modules/evogallery/js/jquery-ui-1.11.4.custom.min.js"></script>
	<script type="text/javascript" src="[+base_url+]assets/modules/evogallery/js/tools.overlay.1.1.2.min.js"></script>
	<script type="text/javascript" src="[+base_url+]assets/modules/evogallery/js/dropzone/min/dropzone.min.js"></script>
	<script type="text/javascript" src="[+base_url+]assets/modules/evogallery/js/tags/tags.js"></script>
	[+js+]
</head>
<body>
<div id="actions-popup">
	<div id="galcontrols">
		<h4>[+lang.in_all_gallery+]:</h4>
        <ul><li><a id="cmdAllDel" href="#">[+lang.delete_all+]</a></li><li><a id="cmdAllRegenerate" href="#">[+lang.regenerate_all+]</a></li></ul>
	</div>

	<div id="doccontrols">
		<h4>[+lang.in_this_doc+]:</h4>
        <ul><li><a id="cmdCntDel" href="#">[+lang.delete_images+]</a></li><li><a id="cmdCntRegenerate" href="#">[+lang.regenerate_images+]</a></li><li><a id="cmdCntMoveTo" href="#">[+lang.move_to+]</a></li></ul>
	</div>
</div>
<div id="actions-menu" class="awesome">[+lang.actions+]</div>

[+content+]
</div>
<div class="overlay" id="overlay"> 
	  <div class="contentWrap"></div>  
</div>

</body>
</html>
