<?php

$folderPath = $path;
echo '
	<div class="row">
	  <hr>
		<div class="col-6">
		  <h4>Create a folder</h4>
			  <div class="form-group">
			    <label for="sub_folder_name">Folder name </label>
			    <input type="text" class="form-control" id="shared_sub_folder_name" name="shared_sub_folder_name">
			    <input type="hidden" class="form-control" id="shared_path" name="shared_path" value="'.$folderPath.'">
			    <br/>
			    <button type="submit" class="btn btn-default" id="new_shared_sub_folder_btn" onclick="new_shared_sub_folder();">Create</button>
			  </div>
		</div>
	  <div class="col-6">
			<h4>Upload</h4>   
			<br />
			<div class="form-group" id="upload_shared_form">
				<input type="file" name="fileInSharedFolder" size="20" id="fileInSharedFolder">
				<br /> <br />
				<input type="submit" value="Upload" class="btn btn-primary" id="uploadFileInSharedFolder_btn" onclick="upload_from_shared_folder();">
			</div>
		</div>
  </div>
	<br />
	<br /> ';
  if(substr_count($folderPath, "/") >1){
  	echo '
			<h4>'.$folderPath.' <img onclick="load_shared_folder('."'".substr($path, 0, strrpos($path, "/"))."'".');" height="25em" width="25em" style="margin-left: 1em;" src="/../images/folder_up40x40.png" alt="folder up icon" title="Previous folder" style="cursor:pointer" /></h4>
  	';
  }else{
  	echo '
			<h4>'.$folderPath.' <img onclick="load_dropbox_table('."'".substr($path, 0, strrpos($path, "/"))."'".');" height="25em" width="25em" style="margin-left: 1em;" src="/../images/folder_up40x40.png" alt="folder up icon" title="Previous folder" style="cursor:pointer" /></h4>
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
						$data['.tag'] = ' <img height="25em" width="25em" src="/../images/folder40x40.png" alt="folder icon" />';
					}else if($data['.tag'] === 'file'){
						$data['.tag'] = '<img height="25em" width="25em" src="/../images/file40x40.png" alt="file icon" />';
					}
					if($data['.tag'] === ' <img height="25em" width="25em" src="/../images/folder40x40.png" alt="folder icon" />')
					{
						echo '
						<tr>
							<td>'.$data['.tag'].'</td>
							<td id="folder_link" onclick="load_shared_sub_folder('."'".$path."'".');" style="cursor:pointer">'.$key->name.'</td>
							<td></td>
							<td></td>
							<td>'.$modified.'</td>
							<td>
							  <img height="25em" width="25em" src="/../images/delete40x40.png" alt="delete icon" title="Click to delete" style="cursor:pointer" onclick="show_modal_in_shared_folder('."'".urlencode($path)."'".');" />
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
							<td><a href="https://test2.testserver.se/index.php/login/download_from_folder?path='.$path.'">
							  <img height="25em" width="25em" src="/../images/download_40x40.png" alt="dowload icon" title="Click to download" />
							</a></td>
							<td>
							  <img height="25em" width="25em" src="/../images/delete40x40.png" alt="delete icon" title="Click to delete" style="cursor:pointer" onclick="show_modal_in_shared_folder('."'".urlencode($path)."'".');" />
							</td>
						</tr>
						';
					}
				}
			}
			echo '
			</tbody>
		</table>
	  <hr>
	  <br />
		';
      $decoded = $response->getDecodedBody();
		  	foreach ($decoded as $key) {
		  		foreach($key as $user){
			  		if(isset($user['user'])){
			  			
				  		$ids = $user['user']['account_id'];
				  		$role = ucfirst($user['access_type']['.tag']);
				  		$sharedAccounts = $this->dropbox->getAccount($ids);
				  		$name = $sharedAccounts->getDisplayName();
				  		$email = $sharedAccounts->getEmail();
				  		$accountId = $sharedAccounts->getAccountId();
				  		$path = $folderPath;
				  		$loggedIn = $account->getAccountId();
							if($ids === $loggedIn){
								$loggedInRole = $role;
							}
							if($user['access_type']['.tag'] === "owner"){
								$ownerId = $user['user']['account_id'];
							}
				  	} 
				  } 
				}
		
				if($loggedIn === $ownerId){
				  
						echo '
							<div class="col-2" id="unshare_folder_form">
			 					<input type="hidden" id="unSharedId" name="unSharedId" value="'.$sharedId.'">
								<button type="submit" class="btn btn-primary" onclick="show_modal_unshare_folder('."'".$path."'".')">Unshare folder</button>
						  </div>
						  <br />
							<div class="col-6">
							  <p style="margin-bottom: 0.5em">'.ucfirst($policy).' can add and remove members of this folder</p>
								<input type="hidden" id="newPolicy" name="newPolicy" value="'.$newPolicy.'">
								<button type="submit" class="btn btn-primary" onclick="change_policy();">Change to '.ucfirst($newPolicy).'</button>
			  			</div>
							<br /><br />
						';
					
				}else {
					echo '
			  		<div class="col-2" id="leave_folder_form">
		 					<input type="hidden" id="unSharedId" name="unSharedId" value="'.$sharedId.'">
				  		<button type="submit" class="btn btn-primary" onclick="show_modal_leave_folder('."'".$path."'".')">Leave folder</button>
			  	  </div>
			  	  <br />
					';
				}
		echo '

</div>
  <div class="row">
	<div class="container">
		<h4>Members</h4>
		<table class="table table-hover">
			<thead>
				<tr>
					<th>Name</th>
					<th>Email</th>
					<th>Role</th>
					<th>New role</th>
					<th>Remove</th>
				</tr>
			</thead>
			<tbody>
			  ';
			  
				$decoded = $response->getDecodedBody();
		  	foreach ($decoded as $key) {
		  		foreach($key as $user){
			  		if(isset($user['user'])){
			  			
				  		$ids = $user['user']['account_id'];
				  		$role = ucfirst($user['access_type']['.tag']);
				  		$sharedAccounts = $this->dropbox->getAccount($ids);
				  		$name = $sharedAccounts->getDisplayName();
				  		$email = $sharedAccounts->getEmail();
				  		$accountId = $sharedAccounts->getAccountId();
				  		$path = $folderPath;
				  		$loggedIn = $account->getAccountId();

				  	if($loggedIn === $ownerId){
				  		if($role === "Owner"){
				  			echo '
					  		<tr>
						  		<td>'.$name.'</td>
						  		<td>'.$email.'</td>
									<td>'.ucfirst($user['access_type']['.tag']).'</td>
					  		</tr>
					  		';
				  		}else if ($role !== "Owner"){
				  			echo '
					  		<tr>
						  		<td>'.$name.'</td>
						  		<td>'.$email.'</td>
									<td>'.$role.'</td>
									<td>
										<input type="hidden" id="update_email" name="update_email" value="'.$email.'">
										<input type="hidden" id="sharedId" name="sharedId" value="'.$sharedId.'">
									  <select class="form-control" id="new_role" name="new_role">
								    	<option selected value="editor">Editor</option>
								    	<option value="viewer">Viewer</option>
								    </select>
								    <button type="submit" class="btn btn-default" title="Click to change the role of member" onclick="update_member('."'".$email."'".');">Change</button>
								  </td>

									<td>
									  <input type="hidden" id="name" name="name" value="'.$name.'">
										<input type="hidden" id="remove_email" name="remove_email" value="'.$email.'">
										<input type="hidden" id="sharedId" name="sharedId" value="'.$sharedId.'">
										<img height="25em" width="25em" src="/../images/delete40x40.png" alt="delete icon" title="Click to remove" onclick="show_modal_remove_member('."'".$name."'".', '."'".$email."'".');" />
								  </td>

								  <td>
									  <input type="hidden" id="newOwnerName" name="newOwnerName" value="'.$name.'">
										<input type="hidden" id="accountId" name="accountId" value="'.$accountId.'">
								    <button type="submit" class="btn btn-default" onclick="show_modal_make_owner('."'".$accountId."'".');">Make owner</button>
								  </td>

					  		</tr>
					  		';
				  		} 
				  	}else if($policy === "editors" && $loggedInRole === "Editor" || $loggedInRole === "Owner"){
				  		if($role === "Owner"){
				  			echo '
					  		<tr>
						  		<td>'.$name.'</td>
						  		<td>'.$email.'</td>
									<td>'.ucfirst($user['access_type']['.tag']).'</td>
					  		</tr>
					  		';
				  		}else if ($role !== "Owner"){
				  			echo '
					  		<tr>
						  		<td>'.$name.'</td>
						  		<td>'.$email.'</td>
									<td>'.$role.'</td>
									<td><input type="hidden" id="update_email" name="update_email" value="'.$email.'">
										<input type="hidden" id="sharedId" name="sharedId" value="'.$sharedId.'">
									  <select class="form-control" id="new_role2" name="new_role2">
								    	<option selected value="editor">Editor</option>
								    	<option value="viewer">Viewer</option>
								    </select>
								    <button type="submit" class="btn btn-default" title="Click to change the role of member" onclick="update_member('."'".$email."'".');">Change</button></td>

									<td><input type="hidden" id="name" name="name" value="'.$name.'">
										<input type="hidden" id="remove_email" name="remove_email" value="'.$email.'">
										<input type="hidden" id="sharedId" name="sharedId" value="'.$sharedId.'">
										<img height="25em" width="25em" src="/../images/delete40x40.png" alt="delete icon" title="Click to remove" onclick="show_modal_remove_member('."'".$name."'".', '."'".$email."'".');" />
								  </td>
					  		</tr>
					  		';
				  		} 
				  	}else if($loggedInRole === "Editor" || $loggedInRole === "Viewer"){
				  		echo '
					  		<tr>
						  		<td>'.$name.'</td>
						  		<td>'.$email.'</td>
									<td>'.ucfirst($user['access_type']['.tag']).'</td>
					  		</tr>
					  		';
				  	  }
				  	}
				  }	
			  }
			  echo '
			</tbody>
		</table>
		<hr>
	</div>
	<div class="container">
  	<h5>Invited</h5>
  	<table class="table table-hover">
			<thead>
		    <tr>
				  <th>Email</th>
					<th>Role</th>
		  		<th>Remove</th>
			  </tr>
		  </thead>
			<tbody>
  ';

	$decoded = $response->getDecodedBody();
		foreach ($decoded as $key) {
		foreach($key as $user){
	
      if(isset($user['invitee'])){
				$email = $user['invitee']['email'];
				$name = $email;
        $role = ucfirst($user['access_type']['.tag']);
				echo '
				  		<tr>
					  		<td>'.$email.'</td>
					  		<td>'.$role.'</td>
								<td><input type="hidden" id="invitee_name" name="invitee_name" value="'.$email.'">
										<input type="hidden" id="invitee_email" name="invitee_email" value="'.$email.'">
										<input type="hidden" id="sharedId" name="sharedId" value="'.$sharedId.'">
										<img height="25em" width="25em" src="/../images/delete40x40.png" alt="delete icon" title="Click to remove" onclick="show_modal_remove_invitee('."'".$email."'".');" />
								</td>
				  		</tr>	
				';
			}
	  }
	} echo '
		  </tbody>
  	</table>
	</div>
</div>
</div>
<br />
';
if($loggedIn === $ownerId){
	echo '
<div class="container">
	<div class="col-6">
		<h4>Add member</h4>		
		
			<div class="form-group">
			  <label for="email">Add </label>
			  <input type="hidden" id="sharedId" name="sharedId" value="'.$sharedId.'">
			  <input type="text" class="form-control" id="add_email" name="add_email" placeholder="name@mail.com">
			  <label for="role">Role </label>
			  <select class="form-control" id="add_role" name="add_role">
			  	<option value="editor">Editor</option>
			   	<option selected value="viewer">Viewer</option>
			  </select>
			</div>
		  <button type="submit" class="btn btn-default" onclick="add_member();">Add</button>

		<br /><br />
	</div>
	
</div>
';
}else if($policy === "editors" && $loggedInRole === "Editor" || $loggedInRole === "Owner"){
echo '
<div class="container">
	<div class="col-6">
		<h4>Add member</h4>		
		<form class="form-horizontal" role="form" method="post" action="https://test2.testserver.se/index.php/login/add_member?path='.$folderPath.'">
			<div class="form-group">
			  <label for="email">Add </label>
			  <input type="hidden" id="sharedId" name="sharedId" value="'.$sharedId.'">
			  <input type="text" class="form-control" id="email" name="email" placeholder="name@mail.com">
			  <label for="role">Role </label>
			  <select class="form-control" id="role" name="role">
			  	<option value="editor">Editor</option>
			   	<option selected value="viewer">Viewer</option>
			  </select>
			</div>
		  <button type="submit" class="btn btn-default">Add</button>
		</form>
		<br /><br />
	</div>
	
</div>
';

};