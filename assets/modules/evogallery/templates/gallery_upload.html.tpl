[+upload_script+]
[+gallery_header+]
<!-- upload -->
<form action="[+action+]" method="post" enctype="multipart/form-data"  class="dropzone" id="dropzone">
        <p>[+lang.tip_multiple_files+]</p>
        <input type="hidden" name="action" value="upload">
        <input type="hidden" name="content_id" value="[+content_id+]">
        <input type="hidden" name="id" value="[+id+]">
		<div class="fallback">
		<input name="Filedata" type="file" multiple>
		</div>
</form>
<a id="excute">アップロード実行</a>


<form action="[+action+]" method="post" enctype="multipart/form-data">
<p id="sortdesc">[+lang.sort_description+]</p>
<div id="uploadFiles"><ul id="uploadList">[+thumbs+]</ul></div>
<div id="selectallcontrols">
	<a id="selectall" href="#">[+lang.selectall+]</a> | <a id="unselectall" href="#">[+lang.unselectall+]</a>
</div>

<div id="sortcontrols" class="submit">
	<input type="submit" id="cmdsort" name="cmdsort" value="[+lang.save_order+]" title="[+lang.save_order_description+]" />
    <p>[+lang.sort_text+]</p>
</div>
</form>
