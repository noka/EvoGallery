<?php
/*---------------------------------------------------------------------------
* GalleryManagement Class - Contains functions for: viewing, uploading, and
*                           editing product galleries.
*
* Add the following after session_name($site_sessionname); in config.inc.php

	if (isset($_REQUEST[$site_sessionname])) {
		session_id($_REQUEST[$site_sessionname]);
	}

* Some server configurations will require the following inside the .htaccess
* file within the manager directory/

	<IfModule mod_security.c>
	SecFilterEngine Off
	SecFilterScanPOST Off
	</IfModule>

*--------------------------------------------------------------------------*/
class GalleryManagement
{
	var $config;  // Array containing module configuration values
    var $modx;

	/**
	* Class constructor, set configuration parameters
	*/
	function __construct($params,&$modx)
	{
        	$this->modx = $modx;
		$this->config = $params;
		$this->config['urlPath'] = $this->modx->config['base_url'].rtrim($this->config['savePath'],'/');
		$this->config['savePath'] = $this->modx->config['base_path'].rtrim($this->config['savePath'],'/');

		$this->mainTemplate = 'template.html.tpl';
		$this->headerTemplate = 'header.html.tpl';
		$this->listingTemplate = 'gallery_listing.html.tpl';
		$this->uploadTemplate = 'gallery_upload.html.tpl';
		$this->editTemplate = 'image_edit.html.tpl';
		$this->galleryHeaderTemplate = 'gallery_header.html.tpl';

		$this->galleriesTable = 'portfolio_galleries';

		$this->current = (($_SERVER['HTTPS'] == 'on') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $this->modx->config['base_url'] . 'manager/index.php';
		$this->a = $_GET['a'];
		$this->id = $_GET['id'];
		
		$this->loadLanguage();
	}

	/**
	* Determine what action was requested and process request
	*/
	function execute()
	{
		$old_umask = umask(0);

		if (isset($_GET['edit']))
		{
			$tpl = $this->editImage();  // Display single image edit form
		}
		else
		{
			  // View/uplaod galleries and gallery images
			if (isset($_GET['onlygallery']))
				$output = $this->viewGallery();
			else
				$output = $this->viewListing();

			// Get contents of js script and replace necessary action URL
			$tplparams = array(
				'params' => '"id": "' . $this->id . '", "a": "' . $this->a . '", "' . session_name() . '": "' . session_id() . '"',
				'base_path' => $this->modx->config['base_url'] . 'assets/modules/evogallery/',
				'base_url' => $this->modx->config['base_url'],
			);
			$js = $this->processTemplate('js.tpl', $tplparams);

    		$tplparams = array(
                	'base_url' => $this->modx->config['base_url'],
				'content' => $output,
				'js' => $js
			);

			$tpl = $this->processTemplate($this->mainTemplate, $tplparams);
		}


		umask($old_umask);

		return $tpl;
	}

	/**
	* Edit an image's details
	*/
	function editImage()
	{
		$this_page = $this->current . '?a=' . $this->a . '&amp;id=' . $this->id;

		$contentId = isset($_GET['content_id']) ? intval($_GET['content_id']) : $this->config['docId'];
		$url = $this->modx->config['base_url'].$this->config['savePath'];
		$id = isset($_GET['edit']) ? intval($_GET['edit']) : '';

		$result = $this->modx->db->select('id, filename, title, description, keywords', $this->modx->getFullTableName($this->galleriesTable), "id = '" . $id . "'");
		$info = $this->modx->fetchRow($result);

        /* Get keyword tags */
		$sql = "SELECT `keywords` FROM ".$this->modx->getFullTableName($this->galleriesTable);

		$keywords = $this->modx->dbQuery($sql);
		$all_docs = $this->modx->db->makeArray( $keywords );

		$foundTags = array();
		foreach ($all_docs as $theDoc) {
			$theTags = explode(",", $theDoc['keywords']);
			foreach ($theTags as $t) {
				$foundTags[trim($t)]++;
			}
		}

		// Sort the TV values (case insensitively)
		uksort($foundTags, 'strcasecmp');

		$lis = '';
		foreach($foundTags as $t=>$c) {
		    if($t != ''){
    			$lis .= '<li title="'.sprintf($this->lang['used_times'],$c).'">'.htmlentities($t, ENT_QUOTES, $this->modx->config['modx_charset'], false).($display_count?' ('.$c.')':'').'</li>';
		    }
		}

		$keyword_tagList = '<ul class="mmTagList" id="keyword_tagList">'.$lis.'</ul>';

		$tplparams = array(
			'action' => $this_page . '&action=view&content_id=' . $contentId . (isset($_GET['onlygallery'])?'&onlygallery=1':''),
			'id' => $info['id'],
			'filename' => urlencode($info['filename']),
			'image' => $this->config['urlPath'] .'/' .$contentId . '/thumbs/' . rawurlencode($info['filename']),
			'title' => $info['title'],
			'description' => $info['description'],
			'keywords' => $info['keywords'],
			'keyword_tagList' => $keyword_tagList
		);
				
		$tpl = $this->processTemplate($this->editTemplate, $tplparams);

		return $tpl;
	}

	/**
	* Display a searchable/sortable listing of documents
	*/
	function viewListing()
	{
		$this_page = $this->current . '?a=' . $this->a . '&id=' . $this->id;

		$tplparams = array();

		$parentId = isset($_GET['content_id']) ? intval($_GET['content_id']) : $this->config['docId'];

		// Get search filter values
		$filter = '';
		if (isset($_GET['query']))
		{
			$search = $this->modx->db->escape($this->modx->stripTags($_GET['query']));
			$filter .= "WHERE (";
			$filter .= "c.pagetitle LIKE '%" . $search . "%' OR ";
			$filter .= "c.longtitle LIKE '%" . $search . "%' OR ";
			$filter .= "c.description LIKE '%" . $search . "%' OR ";
			$filter .= "c.introtext LIKE '%" . $search . "%' OR ";
			$filter .= "c.content LIKE '%" . $search . "%' OR ";
			$filter .= "c.alias LIKE '%" . $search . "%'";
			$filter .= ")";
			$header = $this->header($this->lang['search_results']);
		}
		else
		{
			$filter = "WHERE c.parent = '" . $parentId . "'";
			$header = $this->header();
		}

		$_GET['orderby'] = isset($_GET['orderby']) ? $_GET['orderby'] : 'c.menuindex';
		$_GET['orderdir'] = isset($_GET['orderdir']) ? $_GET['orderdir'] : 'ASC';

		// Check for number of records per page preferences and define global setting
		if (is_numeric($_GET['pageSize']))
		{
			setcookie("pageSize", $_GET['pageSize'], time() + 3600000);
			$maxPageSize = $_GET['pageSize'];
		}
		else
		{
			if (is_numeric($_COOKIE['pageSize']))
				$maxPageSize = $_COOKIE['pageSize'];
			else
				$maxPageSize = 100;
		}
		define('MAX_DISPLAY_RECORDS_NUM', $maxPageSize);

		$table = new MakeTable();  // Instantiate a new instance of the MakeTable class

		// Get document count
		$query = "SELECT COUNT(c.id) FROM " . $this->modx->getFullTableName('site_content') . " AS c " . $filter;
		$numRecords = $this->modx->db->getValue($query);

		// Execute the main table query with MakeTable sorting and paging features
		$query = "SELECT c.id, c.pagetitle, c.longtitle, c.editedon, c.isfolder, COUNT(g.id) as photos FROM " . $this->modx->getFullTableName('site_content') . " AS c " .
		         "LEFT JOIN " . $this->modx->getFullTableName($this->galleriesTable) . " AS g ON g.content_id = c.id " .
		         $filter . " GROUP BY c.id" . $table->handleSorting() . $table->handlePaging();

		if ($ds = $this->modx->db->query($query))
		{
			// If the query was successful, build our table array from the rows
			while ($row = $this->modx->db->getRow($ds))
			{
				$documents[] = array(
					'pagetitle' => '<a href="' . $this_page . '&action=view&content_id=' . $row['id'] . '" title="'.$this->lang['click_view_photos'].'">' . $row['pagetitle'] . ' (' . $row['id'] . ')</a>',
					'longtitle' => ($row['longtitle'] != '') ? stripslashes($row['longtitle']) : '-',
					'photos' => $row['photos'],
					'editedon' => ($row['editedon'] > 0) ? strftime('%m-%d-%Y', $row['editedon']) : '-',
				);
			}
		}

		if (is_array($documents))  // Ensure data was returned
		{
			// Create the table header definition with each header providing a link to sort by that field
			$documentTableHeader = array(
				'pagetitle' => $table->prepareOrderByLink('c.pagetitle', $this->lang['title']),
				'longtitle' => $table->prepareOrderByLink('c.longtitle', $this->lang['long_title']),
				'photos' => $table->prepareOrderByLink('photos', $this->lang['N_photos']),
				'editedon' => $table->prepareOrderByLink('c.editedon', $this->lang['last_edited']),
			);

			$table->setActionFieldName('id');  // Field passed in link urls

			// Table styling options
			$table->setTableClass('documentsTable');
			$table->setRowHeaderClass('headerRow');
			$table->setRowRegularClass('stdRow');
			$table->setRowAlternateClass('altRow');

			// Generate the paging navigation controls
			if ($numRecords > MAX_DISPLAY_RECORDS_NUM)
				$table->createPagingNavigation($numRecords);

			$table_html = $table->create($documents, $documentTableHeader);  // Generate documents table
			$table_html = str_replace('[~~]?', $this_page . '&action=view&', $table_html);  // Create page target
		}
		elseif (isset($_GET['query']))
		{
			$table_html = '<p>'.$this->lang['no_docs_found'].'</p>';  // No records were found
		}
		else
		{
			$table_html = '<p class="first">'.$this->lang['no_children'].'</p>';
		}

		$tplparams['table'] = $table_html;

		if (isset($_GET['query']))
			$tplparams['gallery'] = '';
		else
			$tplparams['gallery'] = $this->viewGallery();
		
		$tpl = $this->processTemplate($this->listingTemplate, $tplparams);
		return $header . $tpl;
	}

	/**
	* View/manage photos for a particular document
	*/
	function viewGallery()
	{
		$this_page = $this->current . '?a=' . $this->a . '&id=' . $this->id;

		$content_id = isset($_GET['content_id']) ? intval($_GET['content_id']) : $this->config['docId'];  // Get document id

		// Verify session and retrieve document information
		$result = $this->modx->db->select('pagetitle, longtitle, parent', $this->modx->getFullTableName('site_content'), "id = '" . $content_id . "'");
		if ($this->modx->db->getRecordCount($result) > 0)
		{
			$info = $this->modx->fetchRow($result);

			if (!isset($_GET['onlygallery']))
			{
				$tplparams['title'] = $info['pagetitle'];
				if ($info['parent'] > 0)
					$tplparams['back_url'] = htmlentities($this_page . '&action=view&content_id=' . $info['parent']);
				else
					$tplparams['back_url'] = htmlentities($this_page . '&action=view');
				$galleryheader = $this->processTemplate($this->galleryHeaderTemplate, $tplparams);

				$target_dir = $this->config['savePath'] . '/' . $content_id . '/';
			} else
				$galleryheader = '<div id="content">';

			if (isset($_POST['cmdsort']) && isset($_POST['sort']))  // Update image sort order
			{
				$sortnum = 0; 
				foreach ($_POST['sort'] as $key => $id)
				{
					$sortnum++; 
					$id = intval($id);
					$this->modx->db->update("sortorder='" . $sortnum . "'", $this->modx->getFullTableName($this->galleriesTable), "id='" . $id . "'");
				}
			}
			elseif (isset($_GET['delete']))  // Delete requested image
			{
				$id = intval($_GET['delete']);
				$rs = $this->modx->db->select('filename', $this->modx->getFullTableName($this->galleriesTable), "id='" . $id . "'");
                if ($this->modx->db->getRecordCount($result) > 0)
				{
					$filename = $this->modx->db->getValue($rs);

					if (file_exists($target_dir . 'thumbs/' . $filename))
						unlink($target_dir . 'thumbs/' . $filename);
					if (file_exists($target_dir . 'original/' . $filename))
						unlink($target_dir . 'original/' . $filename);
					if (file_exists($target_dir . $filename))
						unlink($target_dir . $filename);

					// Remove record from database
					$this->modx->db->delete($this->modx->getFullTableName($this->galleriesTable), "id='" . $id . "'");
				}
			}
			elseif (isset($_POST['edit']))  // Update image information
			{
				$fields['title'] = isset($_POST['title']) ? addslashes($_POST['title']) : '';
				$fields['description'] = isset($_POST['description']) ? addslashes($_POST['description']) : '';
				$fields['keywords'] = isset($_POST['keywords']) ? addslashes($_POST['keywords']) : '';
				$this->modx->db->update($fields, $this->modx->getFullTableName($this->galleriesTable), "id='" . intval($_POST['edit']) . "'");
			}

			// Get contents of upload script and replace necessary action URL
            /* get modx config*/
            $upload_maxsize = intval($this->modx->config['upload_maxsize'] / 1048576); //B to MB
            $allowImages=array();
            foreach( explode(',',$this->modx->config['upload_images']) as $item){
                if(strpos(trim($item),'.')===false){
                    $allowImages[] = '.'.$item;
                 }else{
                    $allowImages[] = $item;
                 }
            }
            $upload_images = implode(',',$allowImages);

			$tplparams = array(
				'self' => urlencode(html_entity_decode($this_page . '&content_id=' . $content_id)),
				'action' => $this->current,
				'params' => '"id": "' . $this->id . '", "a": "' . $this->a . '", "' . session_name() . '": "' . session_id() . '"',
				'uploadparams' => '"action": "upload", "js": "1", "content_id": "' . $content_id . '"',
				'base_path' => $this->modx->config['base_url'] . 'assets/modules/evogallery/',
				'base_url' => $this->modx->config['base_url'],
				'content_id' => $content_id,
				'thumbs' => $this->config['urlPath'] . '/' . $content_id . '/thumbs/',
				'upload_maxsize' => $upload_maxsize,
				'upload_images' => $upload_images,
			);

			$upload_script = $this->processTemplate('upload.js.tpl', $tplparams);

			$tplparams = array(
				'title' => stripslashes($info['pagetitle']),
				'upload_script' => $upload_script,
				'content_id' => $content_id,
				'id' => $this->id,
			);


			// Read through project files directory and show thumbs
			$thumbs = '';
			$result = $this->modx->db->select('id, filename, title, description, keywords', $this->modx->getFullTableName($this->galleriesTable), 'content_id=' . $content_id, 'sortorder ASC');
			while ($row = $this->modx->fetchRow($result))
			{
				$thumbs .= "<li><div class=\"thbSelect\"><a class=\"select\" href=\"#\">".$this->lang['select']."</a></div><div class=\"thbButtons\"><a href=\"" . $this_page . "&action=edit&content_id=$content_id&edit=" . $row['id'] . (isset($_GET['onlygallery'])?"&onlygallery=1":"") ."\" class=\"edit\">".$this->lang['edit']."</a><a href=\"$this_page&action=view&content_id=$content_id&delete=" . $row['id'] . "\" class=\"delete\">".$this->lang['delete']."</a></div><img src=\"" . $this->config['urlPath'] . '/' . $content_id . '/thumbs/' . rawurlencode($row['filename']) . "\" alt=\"" . htmlentities(stripslashes($row['filename'])) . "\" class=\"thb\" /><input type=\"hidden\" name=\"sort[]\" value=\"" . $row['id'] . "\" /></li>\n";
			}

			$tplparams['gallery_header'] = $galleryheader;
			$tplparams['action'] = $this_page . '&action=view&content_id=' . $content_id . (isset($_GET['onlygallery'])?'&onlygallery=1':'');
			$tplparams['thumbs'] = $thumbs;

			$tpl = $this->processTemplate($this->uploadTemplate, $tplparams);

			return $tpl;
		}
	}

	/**
	* Display management header
	*/
	function header($title = '')
	{
		$this_page = $this->current . '?a=' . $this->a . '&id=' . $this->id;

		$parentId = isset($_GET['content_id']) ? intval($_GET['content_id']) : $this->config['docId'];

		if (isset($_GET['query']))
			$search = '<label for="query">'.$this->lang['search'].':</label> <input type="text" name="query" id="query" value="' . $_GET['query'] . '" />';
		else
			$search = '<label for="query">'.$this->lang['search'].':</label> <input type="text" name="query" id="query" />';

		// Generate breadcrumbs
		$result = $this->modx->db->select('id, pagetitle, parent', $this->modx->getFullTableName('site_content'), 'id=' . $parentId);
		$row = $this->modx->fetchRow($result);
		$breadcrumbs = '<a href="' . $this_page . '&action=view&content_id=' . $row['id'] . '" title="'.$this->lang['click_view_categories'].'">' . stripslashes($row['pagetitle']) . '</a>';
		while ($row['id'] > $this->config['docId'])
		{
			$row = $this->modx->fetchRow($this->modx->db->select('id, pagetitle, parent', $this->modx->getFullTableName('site_content'), 'id=' . $row['parent']));
			$breadcrumbs = '<a href="' . $this_page . '&action=view&content_id=' . $row['id'] . '" title="'.$this->lang['click_view_categories'].'">' . stripslashes($row['pagetitle']) . '</a> &raquo; ' . $breadcrumbs;
		}

		$tplparams = array(
			'breadcrumbs' => $breadcrumbs,
			'search' => $search,
			'action' => $this_page,
			'a' => $this->a,
			'id' => $this->id
		);

		if ($title == '')
			$tplparams['title'] = '';
		else
			$tplparams['title'] = '<h2>' . $title . '</h2>';

		$tpl = $this->processTemplate($this->headerTemplate, $tplparams);

		return $tpl;
	}

	/**
	* Resize a given image
	*/
	function resizeImage($filename, $target, $params)
	{
	
		if (!class_exists('phpthumb'))
		{
			include 'classes/phpthumb/phpthumb.class.php';
			include 'classes/phpthumb/phpThumb.config.php';
		}
		
		$phpthumb = new phpThumb();
			
		if (!empty($PHPTHUMB_CONFIG))
		{
			foreach ($PHPTHUMB_CONFIG as $key => $value)
			{
				$keyname = 'config_'.$key;
				$phpthumb->setParameter($keyname, $value);
			}
		}
		//Set output format as input or jpeg if not supperted
		$ext = strtolower(substr(strrchr($filename, '.'), 1));
		if (in_array($ext,array('jpg','jpeg','png','gif')))
			$phpthumb->setParameter('f',$ext);
		else
			$phpthumb->setParameter('f','jpeg');
		$phpthumb->setParameter('config_document_root', rtrim($this->modx->config['base_path'],'/'));
		foreach($params as $key=>$value)
			$phpthumb->setParameter($key,$value);
		$phpthumb->setSourceFilename($filename);
		// generate & output thumbnail
		if ($phpthumb->GenerateThumbnail())
			$phpthumb->RenderToFile($target);
		unset($phpthumb);
	}		

	/**
	* Determine the number of days in a given month/year
	*/
	function checkGalleryTable()
	{
                $sql = "CREATE TABLE IF NOT EXISTS " . $this->modx->getFullTableName($this->galleriesTable) . " (" .
			"`id` int(11) NOT NULL auto_increment PRIMARY KEY, " .
			"`content_id` int(11) NOT NULL, " .
			"`filename` varchar(255) NOT NULL, " .
			"`title` varchar(255) NOT NULL, " .
			"`description` TEXT NOT NULL, " .
			"`keywords` TEXT NOT NULL, " .
			"`sortorder` smallint(7) NOT NULL default '0'" .
                ")";
                $this->modx->db->query($sql);
    }
		
	/**
	* Load language file
	*/
	function loadLanguage()
	{
		$langpath = $this->config['modulePath'].'lang/';
		//First load english lang by defaule
		$fname = $langpath.'english.inc.php';
		if (file_exists($fname))
		{
			include($fname);
		}
		//And now load current lang file
		$fname = $langpath.$this->modx->config['manager_language'].'.inc.php';
		if (file_exists($fname))
		{
			include($fname);
		}
		$this->lang = $_lang;
		unset($_lang);
	}
    
	/**
	* Replace placeholders in template
	*/
	function processTemplate($tplfile, $params)
	{
		$tpl = file_get_contents($this->config['modulePath'] . 'templates/' . $tplfile);
		//Parse placeholders
		foreach($params as $key=>$value)
		{
			$tpl = str_replace('[+'.$key.'+]', $value, $tpl);
		}
		//Parse lang placeholders
		foreach ($this->lang as $key=>$value)
		{
			$tpl = str_replace('[+lang.'.$key.'+]', $value, $tpl);
		}
		return $tpl;
	}
	
	/**
	* Execute Ajax action
	*/
	function executeAction()
	{
		switch($_REQUEST['action'])
		{
			case 'upload':
				return $this->uploadFile();
				break;
			case 'deleteall':
				$mode = isset($_POST['mode'])?$_POST['mode']:'';
				$ids = isset($_POST['action_ids'])?$this->modx->db->escape($_POST['action_ids']):'';
				if(!is_array($ids)) $ids = explode(',',$ids);
				foreach($ids as $key=>$value)
					$ids[$key] = intval($value);
				return $this->deleteImages($mode,$ids);
				break;
			case 'regenerateall':
				$mode = isset($_POST['mode'])?$_POST['mode']:'';
				$ids = isset($_POST['action_ids'])?$this->modx->db->escape($_POST['action_ids']):'';
				if(!is_array($ids)) $ids = explode(',',$ids);
				foreach($ids as $key=>$value)
					$ids[$key] = intval($value);
				return $this->regenerateImages($mode,$ids);
				break;
			case 'move':
				$mode = isset($_POST['mode'])?$_POST['mode']:'';
				$target = isset($_POST['target'])?intval($_POST['target']):0;
				$ids = isset($_POST['action_ids'])?$this->modx->db->escape($_POST['action_ids']):'';
	        		if(!is_array($ids)) $ids = explode(',',$ids);
				foreach($ids as $key=>$value)
					$ids[$key] = intval($value);
				return $this->moveImages($mode,$ids,$target);
				break;
			case 'getids':
				$field = isset($_GET['field'])?$this->modx->db->escape($_GET['field']):'id';
				$mode = isset($_GET['mode'])?$_GET['mode']:'';
				$ids = isset($_GET['action_ids'])?$this->modx->db->escape($_GET['action_ids']):'';
	        		if(!is_array($ids)) $ids = explode(',',$ids);
				foreach($ids as $key=>$value)
					$ids[$key] = intval($value);
				return $this->getIDs($field, $mode, $ids);
				break;
			case 'fake';
				sleep(1);
				break;
		}
	}
	
	/**
	* Decode PHPThumb configuration
	*/
	function getPhpthumbConfig($params)
	{
		return json_decode(str_replace("'","\"",$params),true);	
	}
	
	/**
	* Check and create folders for images
	*/
	function makeFolders($target_dir) {

		$new_folder_permissions = octdec($this->modx->config['new_folder_permissions']);
		$keepOriginal = $this->config['keepOriginal']=='Yes';

		if (!file_exists($target_dir))
			mkdir($target_dir, $new_folder_permissions);
		if (!file_exists($target_dir . 'thumbs'))
			mkdir($target_dir . 'thumbs', $new_folder_permissions);
		if ($keepOriginal && !file_exists($target_dir . 'original'))
			mkdir($target_dir . 'original', $new_folder_permissions);
	}


	/**
	* Upload file
	*/
	function uploadFile()
	{
		
		if (is_uploaded_file($_FILES['Filedata']['tmp_name'])){
			$content_id = isset($_POST['content_id']) ? intval($_POST['content_id']) : $params['docId'];  // Get document id3_get_frame_long_name(string frameId)
			$target_dir = $this->config['savePath'] . '/' . $content_id . '/';
			$target_fname = $_FILES['Filedata']['name'];
			$keepOriginal = $this->config['keepOriginal']=='Yes';
			
			$path_parts = pathinfo($target_fname);
			
			if ($this->config['randomFilenames']=='Yes') {
				$target_fname = $this->getRandomString(8).'.'.$path_parts['extension'];
			}
			elseif ($this->modx->config['clean_uploaded_filename']) {
				$target_fname = $this->modx->stripAlias($path_parts['filename']).'.'.$path_parts['extension'];
			}

            //重複時は連番付加
            $target_fname = $this->getFileName($target_dir,$target_fname,0);

			$target_file = $target_dir . $target_fname;
			$target_thumb = $target_dir . 'thumbs/' . $target_fname;
			$target_original = $target_dir . 'original/' . $target_fname;
			
			// Check for existence of document/gallery directories
			$this->makeFolders($target_dir);

			$movetofile = $keepOriginal?$target_original:$target_dir.uniqid();
			// Copy uploaded image to final destination
			if (move_uploaded_file($_FILES['Filedata']['tmp_name'], $movetofile))
			{
				
				$this->resizeImage($movetofile, $target_file, $this->getPhpthumbConfig($this->config['phpthumbImage']));  // Create and save main image
				$this->resizeImage($movetofile, $target_thumb, $this->getPhpthumbConfig($this->config['phpthumbThumb']));  // Create and save thumb
				
				$new_file_permissions = octdec($this->modx->config['new_file_permissions']);
				chmod($target_file, $new_file_permissions);
				chmod($target_thumb, $new_file_permissions);
				if ($keepOriginal)
					chmod($target_original, $new_file_permissions);
				else
					unlink($movetofile);
			}

			if (isset($_POST['edit']))
			{
				// Replace mode
				
				// Delete existing image
				$id = intval($_POST['edit']);
				$oldfilename = $this->modx->db->getValue($this->modx->db->select('filename',$this->modx->getFullTableName('portfolio_galleries'),'id='.$id));
				if(!empty($oldfilename) && $oldfilename !== $target_fname){
					if (file_exists($target_dir . 'thumbs/' . $oldfilename))
						unlink($target_dir . 'thumbs/' . $oldfilename);
					if (file_exists($target_dir . 'original/' . $oldfilename))
						unlink($target_dir . 'original/' . $oldfilename);
					if (file_exists($target_dir . $oldfilename))
						unlink($target_dir . $oldfilename);
				}
				
				// Update record in the database
				$fields = array(
					'filename' => $this->modx->db->escape($target_fname)
				);
				$this->modx->db->update($fields, $this->modx->getFullTableName('portfolio_galleries'), "id='".$id."'");
				
			} else
			{
				// Find the last order position
				$rs = $this->modx->db->select('sortorder', $this->modx->getFullTableName('portfolio_galleries'), 'content_id="'.$content_id.'"', 'sortorder DESC', '1');
				if ($this->modx->db->getRecordCount($rs) > 0)
					$pos = $this->modx->db->getValue($rs) + 1;
				else
					$pos = 1; 

				// Create record in the database
				preg_match('/(.*)(?:\.([^.]+$))/',$this->modx->db->escape($target_fname),$tmp_title);
				$fields = array(
					'content_id' => $content_id,
					'filename' => $this->modx->db->escape($target_fname),
					'sortorder' => $pos,
					'title' => $tmp_title[1],
				);
				$this->modx->db->insert($fields, $this->modx->getFullTableName('portfolio_galleries'));
				$id = $this->modx->db->getInsertId();
			}
			
			//return new filename
			return json_encode(array('result'=>'ok','filename'=>$target_fname,'id'=>$id));
		}
		
	}
	
	/**
	* Get SQL Where condition given mode and ids
	*/
	function getWhereClassByMode($mode = 'id', $ids = array())
	{
		$where = '';
		switch ($mode)
		{
			case 'id':
				if (!sizeof($ids))
					return false;
				$where = 'id in ('.implode(',',$ids).')';
				break;
			case 'all':
				$where = '';
				break;
			case 'contentid':
				if (!sizeof($ids))
					return false;
				$where = 'content_id in ('.implode(',',$ids).')';
				break;
			default:
				return false;
		}
		return $where;
	}
		
	/**
	* Delete given images
	*/
	function deleteImages($mode = 'id', $ids = array())
	{
		$where = $this->getWhereClassByMode($mode, $ids);
		if ($where===false)
			return false;
			
		$ds = $this->modx->db->select('id, filename, content_id',$this->modx->getFullTablename($this->galleriesTable),$where);
		while ($row = $this->modx->db->getRow($ds))
		{
			$target_dir = $this->config['savePath'].'/'.$row['content_id'].'/';
			if (file_exists($target_dir . 'thumbs/' . $row['filename']))
				unlink($target_dir . 'thumbs/' . $row['filename']);
			if (file_exists($target_dir . 'original/' . $row['filename']))
				unlink($target_dir . 'original/' . $row['filename']);
			if (file_exists($target_dir . $row['filename']))
				unlink($target_dir . $row['filename']);
		}
		$this->modx->db->delete($this->modx->getFullTablename($this->galleriesTable),$where);
		return true;
	}
	
	/**
	* Regenerate given images from original (if exists)
	*/
	function regenerateImages($mode = 'id', $ids = array())
	{
		$where = $this->getWhereClassByMode($mode, $ids);
		if ($where===false)
			return false;
		$ds = $this->modx->db->select('id, filename, content_id',$this->modx->getFullTablename($this->galleriesTable),$where);
		while ($row = $this->modx->db->getRow($ds))
		{
			$target_dir = $this->config['savePath'].'/'.$row['content_id'].'/';
			$orininal_file = $target_dir . 'original/' . $row['filename']; 
			if (file_exists($orininal_file))
			{
				$this->resizeImage($orininal_file, $target_dir . $row['filename'], $this->getPhpthumbConfig($this->config['phpthumbImage']));  // Create and save main image
				$this->resizeImage($orininal_file, $target_dir . 'thumbs/' . $row['filename'], $this->getPhpthumbConfig($this->config['phpthumbThumb']));  // Create and save thumb
			}	
		}
		return true;
	}
	
	/**
	* Move images to target doc
	*/
	function moveImages($mode = 'id', $ids = array(), $target = 0)
	{
		global $modx;
		if ($target==0)
			return false;
		$where = $this->getWhereClassByMode($mode, $ids);
		if ($where===false)
			return false;
		$target_dir = $this->config['savePath'].'/'.$target.'/';
		$this->makeFolders($target_dir);
		
		$ds = $this->modx->db->select('id, filename, content_id',$this->modx->getFullTablename($this->galleriesTable),$where);
		while ($row = $this->modx->db->getRow($ds))
		{
			//Move files
			$source_dir = $this->config['savePath'].'/'.$row['content_id'].'/';
			if (file_exists($source_dir.$row['filename']))
				if (!rename($source_dir.$row['filename'], $target_dir.$row['filename']))
					return false;
			if (file_exists($source_dir.'thumbs/'.$row['filename']))
				if (!rename($source_dir.'thumbs/'.$row['filename'], $target_dir.'thumbs/'.$row['filename']))
					return false;
			if (file_exists($source_dir.'original/'.$row['filename']))
				if (!rename($source_dir.'original/'.$row['filename'], $target_dir.'original/'.$row['filename']))
					return false;
		}
		$this->modx->db->update(array('content_id' => $target), $this->modx->getFullTablename($this->galleriesTable), $where);
		return true;
	}

	/**
	* Get Ids of $field (id or content_id)
	*/
	function getIDs($field, $mode, $ids)
	{
		global $modx;
		$result_ids = array();
		$where = $this->getWhereClassByMode($mode, $ids);
		if ($where===false)
			return false;
		if (!empty($where))
			$where=' WHERE '.$where;
		$ds = $this->modx->db->query('SELECT DISTINCT '.$field.' FROM '.$this->modx->getFullTablename($this->galleriesTable).$where);
		while ($row = $this->modx->db->getRow($ds))
		{
			$result_ids[] = $row[$field];
		}	
		return json_encode(array('result'=>'ok','ids'=>$result_ids));
	}

	/**
	* Generate random strings, copied from MaxiGallery
	*/
	function getRandomString($length){
		$str = "";
		$salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		srand((double)microtime()*1000000);
		$i = 0;
		while ($i <= $length) {
			$num = rand(0,61);
			$tmp = substr($salt, $num, 1);
			$str = $str . $tmp;
			$i++;
		}
		return $str;
	}


    function getFileName($d,$f,$i=0){
        if($i>0){
            //再帰
            $info = pathinfo($d.$f);
            $r = $info['filename'] . '-' . $i . '.' . $info['extension'];
        } else {
            //初回ループ
            $f =str_replace("\s",'',ltrim($f,'.')); //空白NG
            $r = $f;
        }

        if(file_exists($d.$r)){
            $i++;
            return $this->getFileName($d,$f,$i);
        }

        return $r;
    }


}
?>
