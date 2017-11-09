<?php

echo '
	<div class="row">
	  <hr>
		<div class="col-6">
			<h4>Create a folder</h4>
			  <div class="form-group">
			    <label for="folder_name">Folder name </label>
			    <input type="text" class="form-control" id="folder_name" name="folder_name">
			    <br/>
			    <button type="submit" class="btn btn-default" id="new_folder_btn" onclick="new_folder();">Create</button>
			  </div>
			<br />
			<br />
			<h4>Upload</h4>   
			<br />
			<div class="form-group" id="upload_form">
				<input type="file" name="userfile" size="20" id="userfile">
				<br /> <br />
				<input type="submit" value="Upload" class="btn btn-primary" id="uploadFile_btn" onclick="upload_file();">
			</div>
		</div>

		<div class="col-6" id="create_shared_form">
			<h4>Create and share a folder</h4>
			  <div class="form-group">
			    <label for="shared_folder_name">Folder name </label>
			    <input type="text" class="form-control" id="shared_folder_name" name="shared_folder_name">
			    <label for="ACL">Access <p class="text-muted col-form-label-sm" style="margin-bottom:0">Who can add, remove or change the privileges of members</p></label>
			    <select class="form-control" id="ACL" name="ACL">
			    	<option selected value="owner">Owner</option>
			    	<option value="editors">Editors</option>
			    </select>
			    <label for="shared_email">Share with </label>
			    <input type="text" class="form-control" id="shared_email" name="shared_email" placeholder="name@mail.com">
			    <label for="role">Role </label>
			    <select class="form-control" id="role" name="role">
			    	<option value="editor">Editor</option>
			    	<option selected value="viewer">Viewer</option>
			    </select>
			  </div>
			  <button type="submit" class="btn btn-default" onclick="new_shared_folder();">Create</button>
		</div>
	</div>
	<br />
	<br />

	<h4>Your Dropbox</h4>
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

					if(is_object($key->getSharingInfo())){
						$data['.tag'] = ' <img height="25em" width="25em" src="/../images/shared_folder40x40.png" alt="shared folder icon" />';
					}else if($data['.tag'] === 'folder'){
						$data['.tag'] = ' <img height="25em" width="25em" src="/../images/folder40x40.png" alt="folder icon" />';
					}else if($data['.tag'] === 'file'){
						$data['.tag'] = '<img height="25em" width="25em" src="/../images/file40x40.png" alt="file icon" />';
					}
					if($data['.tag'] === ' <img height="25em" width="25em" src="/../images/folder40x40.png" alt="folder icon" />')
					{
						echo '
						<tr>
							<td>'.$data['.tag'].'</td>
							<td id="folder_link" onclick="load_folder('."'".$path."'".');" style="cursor:pointer">'.$key->name.'</td>
							<td></td>
							<td></td>
							<td>'.$modified.'</td>
							<td>
							  <img height="25em" width="25em" src="/../images/delete40x40.png" alt="delete icon" title="Click to delete" style="cursor:pointer" onclick="show_modal('."'".urlencode($path)."'".');" />
							</td>
						</tr>
						';
					}else if($data['.tag'] ===  ' <img height="25em" width="25em" src="/../images/shared_folder40x40.png" alt="shared folder icon" />')
					{
						echo '
						<tr>
							<td>'.$data['.tag'].'</td>
							<td onclick="load_shared_folder('."'".$path."'".');" style="cursor:pointer">'.$key->name.'</td>
							<td></td>
							<td></td>
							<td>'.$modified.'</td>
							<td>
							  <img height="25em" width="25em" src="/../images/delete40x40.png" alt="delete icon" title="Click to delete" style="cursor:pointer" onclick="show_modal_shared_folder_root('."'".urlencode($path)."'".');" />
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
							<td><a href="https://test2.testserver.se/index.php/login/download_file?path='.$path.'">
							  <img height="25em" width="25em" src="/../images/download_40x40.png" alt="dowload icon" title="Click to download" />
							</a></td>
							<td>
							  <img height="25em" width="25em" src="/../images/delete40x40.png" alt="delete icon" title="Click to delete" style="cursor:pointer" onclick="show_modal('."'".urlencode($path)."'".');" />
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



