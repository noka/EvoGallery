<!DOCTYPE>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>Gallery Management Module</title>
	<link rel="stylesheet" type="text/css" media="screen" href="[+base_url+]assets/modules/evogallery/templates/screen.css">
	<link rel="stylesheet" type="text/css" media="screen" href="[+base_url+]assets/modules/evogallery/js/dropzone/dropzone.evogallery.css">
	<link rel="stylesheet" type="text/css" media="screen" href="[+base_url+]assets/modules/evogallery/js/overlay/overlay-minimal.css" >
	<link rel="stylesheet" type="text/css" media="screen" href="[+base_url+]assets/modules/evogallery/js/tags/tags.css">
	<script type="text/javascript" src="[+base_url+]assets/modules/evogallery/js/jquery-1.8.3.min.js"></script>
	<script type="text/javascript" src="[+base_url+]assets/modules/evogallery/js/jquery-ui-1.11.4.custom.min.js"></script>
  <script type="text/javascript" src="[+base_url+]assets/modules/evogallery/js/jquery.ui.touch-punch.min.js"></script>
	<script type="text/javascript" src="[+base_url+]assets/modules/evogallery/js/tools.overlay.1.1.2.min.js"></script>
	<script type="text/javascript" src="[+base_url+]assets/modules/evogallery/js/dropzone/min/dropzone.min.js"></script>
	<script type="text/javascript" src="[+base_url+]assets/modules/evogallery/js/tags/tags.js"></script>
	[+js+]
</head>
<body>
[+content+]
</div>
<div class="overlay" id="overlay"> 
	  <div class="contentWrap"></div>  
</div>

<div class="popup" id="operation-popup"> 
    <div class="status">[+lang.please_wait+]</div>
		<div class="progress"></div>
		<div class="close">		
			<input type="button" value="[+lang.close+]" class="awesome" name="close" />
		</div>
</div>
</body>
</html>
