[+upload_script+]
[+gallery_header+]
<!-- upload -->
<h4>[+lang.upload_files+]</h4>
<form action="[+action+]" method="post" enctype="multipart/form-data"  class="dropzone" id="dropzone">
        <p>[+lang.tip_multiple_files+]</p>
        <input type="hidden" name="action" value="upload">
        <input type="hidden" name="content_id" value="[+content_id+]">
        <input type="hidden" name="id" value="[+id+]">
		<div class="fallback">
		<input name="Filedata" type="file" multiple>
		</div>
</form>
<nav id="dropzone-actions">
<button class="dropzone-actions-excute awesome" type="submit">[+lang.upload_file+]</button>
<button class="dropzone-actions-cancel awesome" type="reset">[+lang.clear_queue+]</button>
</nav>

<hr>
<!-- view & sort -->
<h4>[+lang.in_this_doc+]</h4>
<p>[+lang.sort_description+]</p>
<form action="[+action+]" method="post" enctype="multipart/form-data">
<div id="uploadFiles"><ul id="uploadList">[+thumbs+]</ul></div>
<div id="selectallcontrols">
	<a id="selectall" href="#">[+lang.selectall+]</a> | <a id="unselectall" href="#">[+lang.unselectall+]</a>
</div>

<div id="sortcontrols" class="submit">
	<input type="submit" id="cmdsort" name="cmdsort" value="[+lang.save_order+]" title="[+lang.save_order_description+]"  class="awesome">
    <button id="cmdCntDel"  class="awesome">[+lang.delete_images+]</button>
    <!--button id="cmdCntMoveTo"  class="awesome">[+lang.move_to+]</button-->
</div>
</form>

<!-- 別リソースへ移動するポップアップ -->
<div class="popupclose" id="moveto-popup"> 
	<p id="movetarget_doc">[+lang.select_document+]</p>
	<input id="movetarget_id" type="hidden" value="0">
	<input id="moveto" type="button" value="[+lang.start+]" class="awesome" name="cmdmoveto">
</div>
