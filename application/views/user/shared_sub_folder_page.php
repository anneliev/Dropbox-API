<?php

$folderPath = $path;
//var_dump($folderPath);
  echo '
		<div class="row">
	  <hr>
		<div class="col-6">
			<h4>Create a folder</h4>
			  <div class="form-group">
			    <label for="sub_folder_name">Folder name </label>
			    <input type="text" class="form-control" id="shared_sub_sub_folder_name" name="shared_sub_sub_folder_name">
			    <input type="hidden" class="form-control" id="shared_sub_path" name="shared_sub_path" value="'.$folderPath.'">
			    <br/>
			    <button type="submit" class="btn btn-default" id="new_sub_folder_btn" onclick="new_shared_sub_sub_folder();">Create</button>
			  </div>
		</div>

		<div class="col-6">
			<h4>Upload</h4>   
			<br />
			<div class="form-group" id="upload_form">
				<input type="file" name="fileInSharedSubFolder" size="20" id="fileInSharedSubFolder">
				<br /> <br />
				<input type="submit" value="Upload" class="btn btn-primary" id="uploadFileInSharedSubFolder_btn" onclick="upload_from_shared_sub_folder();">
			</div>
		</div>
	</div>
	<br />
	<br />';
  if(substr_count($folderPath, "/") >2){
  	echo '
			<h4>'.$folderPath.' <img onclick="load_shared_sub_folder('."'".substr($path, 0, strrpos($path, "/"))."'".');" height="25em" width="25em" style="margin-left: 1em;" src="../../images/folder_up40x40.png" alt="folder up icon" title="Previous folder" style="cursor:pointer" /></h4>
  	';
  }
  else if(substr_count($folderPath, "/") >1){
  	echo '
			<h4>'.$folderPath.' <img onclick="load_shared_folder('."'".substr($path, 0, strrpos($path, "/"))."'".');" height="25em" width="25em" style="margin-left: 1em;" src="../../images/folder_up40x40.png" alt="folder up icon" title="Previous folder" style="cursor:pointer" /></h4>
  	';
  }else{
  	echo '
			<h4>'.$folderPath.' <img onclick="load_dropbox_table('."'".substr($path, 0, strrpos($path, "/"))."'".');" height="25em" width="25em" style="margin-left: 1em;" src="../../images/folder_up40x40.png" alt="folder up icon" title="Previous folder" style="cursor:pointer" /></h4>
  	';
  }
		echo '
		<table class="table table-hover">
			<thead>
				<tr>
					<th>Type</th>
					<th>Name</th>
					<th>Size kB</th>
					<th>Modified</th>
					<th>Download</th>
					<th>Delete</th>
				</tr>
			</thead>
			<tbody>
			';
			
		if(empty($filesList)){
			echo '
				<tr>
			  	<td>This folder is empty</td>
				</tr>
			';
		}else{
			foreach($filesList as $key)
			{
				
				$data = $key->getData();
				$path = $key->getPathLower();
				$path_display = $key->getPathDisplay();
				$path = str_replace('%20', ' ', $path);
				$modified = $key->client_modified;
				$modified = str_replace('T', ' ', $modified);
				$modified = str_replace('Z', ' ', $modified);

				if($data['.tag'] === 'folder'){
					$data['.tag'] = ' <img height="25em" width="25em" src="../../images/folder40x40.png" alt="folder icon" />';
				}else if($data['.tag'] === 'file'){
					$data['.tag'] = '<img height="25em" width="25em" src="../../images/file40x40.png" alt="file icon" />';
				}

				if($data['.tag'] === ' <img height="25em" width="25em" src="../../images/folder40x40.png" alt="folder icon" />')
				{
					echo '
					<tr>
						<td>'.$data['.tag'].'</td>
						<td id="folder_link" onclick="load_shared_sub_folder('."'".$path."'".');" style="cursor:pointer">'.$key->name.'</td>
						<td></td>
						<td></td>
						<td>'.$modified.'</td>
						<td>
						  <img height="25em" width="25em" src="../../images/delete40x40.png" alt="delete icon" title="Click to delete" style="cursor:pointer" onclick="show_modal_in_shared_sub_folder('."'".urlencode($path)."'".');"/>
						</td>
					</tr>
					';
				}else
				{
					echo '
					<tr>
						<td>'.$data['.tag'].'</td>
						<td> '.$key->name.'</td>
						<td> '.number_format($key->size/1000,1,'.','').'</td>
						<td>'.$modified.'</td>
						<td><a href="http://localhost:8888/Dropbox-API/index.php/login/download_from_folder?path='.$path.'">
						  <img height="25em" width="25em" src="../../images/download_40x40.png" alt="dowload icon" title="Click to download" />
						</a></td>
						<td>
						  <img height="25em" width="25em" src="../../images/delete40x40.png" alt="delete icon" title="Click to delete" style="cursor:pointer" onclick="show_modal_in_shared_sub_folder('."'".urlencode($path)."'".');"/>
						</td>
					</tr>
					';
				}
			}
		}
			echo'
			</tbody>
		</table>
		<hr>
   ';