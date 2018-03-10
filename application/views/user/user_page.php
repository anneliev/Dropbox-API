<?php

echo '
  
	<div class="container" id="main_content">
		<hr>
		<h2>Signed in as '.$account->getDisplayName().'</h2>
		<div class="row">
			<div class="container">
		  <hr>
		  <br />
		  	<div id="loading_div" style="position: fixed; height: 50%; width: calc( 50% + 100px); z-index: 999;  display:table-cell; vertical-align:middle; text-align:center">
					<img id="loading_gif" src="../../images/blue-spinner.gif" />
				</div>
			  <div id="dropbox_table">
			    
			  </div>
			  <div id="modal_div">

			  </div>
		  </div>
		</div>
	</div>
'; 
?>

<script type="text/javascript">
/*-------------------------DISPLAY FUNCTIONS-------------------------*/

$('#loading_div').hide();

$(function() {
	load_dropbox_table('');
});

function load_dropbox_table(url) {
	$('#loading_div').show();
	$('#dropbox_table').load('/Dropbox-API/index.php/login/files_display', {
			url:url
	},function() {
		$('#loading_div').hide();
	});
}
function load_folder(url) {
	$('#loading_div').show();
	$('#dropbox_table').load('/Dropbox-API/index.php/login/folder_display', {
			url:url
	},function() {
		$('#loading_div').hide();
	});
}

function load_shared_folder(url) {
	$('#loading_div').show();
	$('#dropbox_table').load('/Dropbox-API/index.php/login/shared_folder_display', {
			url:url
	},function() {
	  $('#loading_div').hide();
	});
}

function load_shared_sub_folder(url) {
	$('#loading_div').show();
	$('#dropbox_table').load('/Dropbox-API/index.php/login/shared_sub_folder_display', {
			url:url
	},function() {
		$('#loading_div').hide();
	});
}
/*-------------------------ROOT FUNCTIONS-----------------------*/
function new_folder(){
	$('#loading_div').show();
	var folder_name = $('input#folder_name').val();
	if(folder_name === ''){
		alert("Please enter a folder name");
		return false;
	}
	$.ajax({
		url: '/Dropbox-API/index.php/login/create_new_folder',
		type: 'POST',
		data: {folder_name: folder_name},
		success: function(result){
			if(result.includes('path/conflict/folder/')){
	    	alert('Folder name already use. Please choose a uniqe name');
	    }
			load_dropbox_table();
			$('#loading_div').hide();
		},
		error: (error) => {
			alert("Folder not created");
			load_dropbox_table();
			$('#loading_div').hide();
		}
	});
}

function new_shared_folder(){
	$('#loading_div').show();
	var folder_name = $('input#shared_folder_name').val();
	var shared_email = $('input#shared_email').val();
  var role = $('select#role').val();
  var ACL = $('select#ACL').val();

  function validateEmail(email) { 
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(shared_email);
  }
  var tested = validateEmail(shared_email);
  
	if(folder_name === ''){
		alert("Please enter a folder name");
		return false;
	}if(shared_email === ''){
		alert('Please enter an email adress');
		$('#loading_div').hide();
		return false;
	}if(tested === false){
		alert("Invalid email adress");
		$('#loading_div').hide();
		return false;
	}
	$.ajax({
		url: '/Dropbox-API/index.php/login/create_new_shared_folder',
		type: 'POST',
		data: {folder_name: folder_name, shared_email: shared_email, role: role, ACL: ACL},
		success: function(result){
			if(result.includes('path/conflict/folder/')){
	    	alert('Folder name already use. Please choose a uniqe name');
	    }
			load_dropbox_table();
			$('#loading_div').hide();
		},
		error: (error) => {
			alert("Folder not created");
			load_dropbox_table();
			$('#loading_div').hide();
		}
	});
}

function upload_file(){
	$('#loading_div').show();
	var file_data = $('#userfile').prop('files')[0];
  var form_data = new FormData();
  form_data.append('userfile', file_data);
 	var url = $('input#path').val();
  $.ajax({
    url: '/Dropbox-API/index.php/login/do_upload', 
    type: "POST",
    data : form_data,
    processData: false,
    contentType: false,
    success: function(data){ 
      load_dropbox_table(url);
      $('#loading_div').hide();
    },
    error: function(error) {
      alert("File couldn't be uploaded"); 
      load_dropbox_table(url);
      $('#loading_div').hide();
    }
  });
}
function show_modal(url){	
	$('#loading_div').show();
	$('#modal_div').load('/Dropbox-API/index.php/login/show_modal?path=' + url, {
			url:url
	}, function() {
		$('#loading_div').hide(),
		$('#delete_modal').modal('show');
	});
}
function delete_file(url){
	$('#loading_div').show();
	$('#dropbox_table').load('/Dropbox-API/index.php/login/delete_file?path=' + url, {
			url:url
	}, function() {
		$('#dropbox_table').load('/Dropbox-API/index.php/login/files_display'),
		$('#loading_div').hide(),
	  $('#delete_modal').modal('hide');
	});
}
function show_modal_shared_folder_root(url){	
	$('#loading_div').show();
	$('#modal_div').load('/Dropbox-API/index.php/login/show_modal_shared_folder_root?path=' +url, {
			url:url
	}, function() {
		$('#loading_div').hide(),
		$('#delete_shared_from_root_modal').modal('show');
	});
}
function delete_shared_from_root(url){
	$('#loading_div').show();
	$('#dropbox_table').load('/Dropbox-API/index.php/login/delete_file?path=' + url, {
			url:url
	}, function() {
		$('#dropbox_table').load('/Dropbox-API/index.php/login/files_display'),
		$('#loading_div').hide(),
	  $('#delete_shared_from_root_modal').modal('hide');
	});
}
/*-------------------------FOLDER FUNTIONS------------------------*/
function new_sub_folder(){
	$('#loading_div').show();
	var folder_name = $('input#sub_folder_name').val();
	var url = $('input#path').val();
		encodeURI(url);
	var path = url + "/" + folder_name;
	if(folder_name === ''){
		alert("Please enter a folder name");
		return false;
	}
	$.ajax({
		url: '/Dropbox-API/index.php/login/create_new_sub_folder',
		type: 'POST',
		data: {folder_name: folder_name, url: url},
		success: function(result){
			if(result.includes('path/conflict/folder/')){
	    	alert('Folder name already use. Please choose a uniqe name');
	    }
			load_folder(url);
			$('#loading_div').hide();
		},
		error: (error) => {
			alert("Folder not created");
			console.log(error);
			load_folder(url);
			$('#loading_div').hide();
		}
	});
}
function upload_from_folder(){
	$('#loading_div').show();
	var file_data = $('#fileInFolder').prop('files')[0];
  var form_data = new FormData();
  form_data.append('userfile', file_data);
 	var url = $('input#path').val();
  $.ajax({
    url: '/Dropbox-API/index.php/login/upload_from_folder?path=' + url, 
    type: "POST",
    data : form_data,
    processData: false,
    contentType: false,
    success: function(data){ 
      load_folder(url);
			console.log(data);
      $('#loading_div').hide();
    },
    error: function(error) {
			console.log(error);
      alert("File couldn't be uploaded"); 
      load_folder(url);
      $('#loading_div').hide();
    }
  });
}
function show_modal_in_folder(url){	
	$('#loading_div').show();
	$('#modal_div').load('/Dropbox-API/index.php/login/show_modal_folder?path=' + url, {
			url:url
	}, function() {
		$('#loading_div').hide(),
		$('#delete_from_folder_modal').modal('show');
	});
}
function delete_from_folder(url){
	$('#loading_div').show();
	var path = $('input#path').val();
	$('#dropbox_table').load('/Dropbox-API/index.php/login/delete_from_folder?path=' + url, {
			url:url
	}, function() {
		$('#dropbox_table').load('/Dropbox-API/index.php/login/folder_display', {
			url: path
		}),
		$('#loading_div').hide(),
	  $('#delete_from_folder_modal').modal('hide');
	});
}

function share_a_folder(){
	$('#loading_div').show();
	var folder_name = $('input#share_a_folder_name').val();
	var shared_email = $('input#share_a_folder_email').val();
  var role = $('select#share_a_folder_role').val();
  var ACL = $('select#share_a_folder_ACL').val();
console.log(folder_name);
  function validateEmail(email) { 
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(shared_email);
  }
  var tested = validateEmail(shared_email);
  
	if(shared_email === ''){
		alert('Please enter an email adress');
		$('#loading_div').hide();
		return false;
	}if(tested === false){
		alert("Invalid email adress");
		$('#loading_div').hide();
		return false;
	}
	$.ajax({
		url: '/Dropbox-API/index.php/login/share_a_folder',
		type: 'POST',
		data: {folder_name: folder_name, shared_email: shared_email, role: role, ACL: ACL},
		success: function(result){
			if(result.includes('Exception')){
	    	alert(result);
	    }
			load_shared_folder(folder_name);
			$('#loading_div').hide();
		},
		error: (error) => {
			alert("Folder not shared");
			load_shared_folder(folder_name);
			$('#loading_div').hide();
		}
	});
}
/*-------------------------SHARED FOLDER FUNCTIONS-------------------------*/
function new_shared_sub_folder(){
	$('#loading_div').show();
	var folder_name = $('input#shared_sub_folder_name').val();
	var url = $('input#shared_path').val();
	var path = url + "/" + folder_name;
	if(folder_name === ''){
		alert("Please enter a folder name");
		return false;
	}
	$.ajax({
		url: '/Dropbox-API/index.php/login/create_new_shared_sub_folder',
		type: 'POST',
		data: {folder_name: folder_name, path: path},
		success: function(result){		
			if(result.includes('path/conflict/folder/')){
	    	alert('Folder name already use. Please choose a uniqe name');
	    }
			load_shared_folder(url);
			$('#loading_div').hide();
		},
		error: (error) => {
			alert("Folder not created");
			load_shared_folder(url);
			$('#loading_div').hide();
		}
	});
}

function upload_from_shared_folder(){
	$('#loading_div').show();
	var file_data = $('#fileInSharedFolder').prop('files')[0];
  var form_data = new FormData();
  form_data.append('userfile', file_data);
 	var url = $('input#shared_path').val();
  $.ajax({
    url: '/Dropbox-API/index.php/login/upload_from_shared_folder?path=' + url, 
    type: "POST",
    data: form_data,
    processData: false,
    contentType: false,
    success: function(data){ 
      load_shared_folder(url);
      $('#loading_div').hide();
    },
    error: function(error) {
      alert("File couldn't be uploaded"); 
      load_shared_folder(url);
      $('#loading_div').hide();
    }
  });
}
function show_modal_in_shared_folder(url){	
	$('#loading_div').show();
	$('#modal_div').load('/Dropbox-API/index.php/login/show_modal_shared_folder?path=' + url, {
			url: url
	}, function() {
		$('#loading_div').hide(),
		$('#delete_from_shared_folder_modal').modal('show');
	});
}
function delete_from_shared_folder(url){
	$('#loading_div').show();
	var path = $('input#shared_path').val();
	$('#dropbox_table').load('/Dropbox-API/index.php/login/delete_from_folder?path=' + url, {
			url:url
	}, function() {
		$('#dropbox_table').load('/Dropbox-API/index.php/login/shared_folder_display', {
			url: path
		}),
		$('#loading_div').hide(),
	  $('#delete_from_shared_folder_modal').modal('hide');
	});
}

function show_modal_unshare_folder(url){	
	$('#loading_div').show();
	var path = $('input#shared_path').val();
	$('#modal_div').load('/Dropbox-API/index.php/login/show_modal_unshare_folder', {
			path: path
	}, function() {
		$('#loading_div').hide(),
		$('#unshare_folder_modal').modal('show');
	});
}
function unshare_folder(url){
	$('#loading_div').show();
	var path = $('input#shared_path').val();
	var sharedId = $('input#unSharedId').val();
	var copy = false;
	if($('#unshare_folder_check').is(':checked')){
		copy = true;
	}
	$('#dropbox_table').load('/Dropbox-API/index.php/login/unshare_folder', {
			sharedId: sharedId,
			copy: copy
	}, function() {
		$('#dropbox_table').load('/Dropbox-API/index.php/login/folder_display', {
			url: path
		}),
		$('#loading_div').hide(),
	  $('#unshare_folder_modal').modal('hide');
	});
}

function show_modal_leave_folder(url){	
	$('#loading_div').show();
	$('#modal_div').load('/Dropbox-API/index.php/login/show_modal_leave_folder?path=' + url, {
			path:url
	}, function() {
		$('#loading_div').hide(),
		$('#leave_folder_modal').modal('show');
	});
}
function leave_folder(){
	$('#loading_div').show();
	var path = $('input#shared_path').val();
	var sharedId = $('input#unSharedId').val();
	var copy = false;
	if($('#leave_folder_check').is(':checked')){
		copy = true;
	}
	$('#dropbox_table').load('/Dropbox-API/index.php/login/leave_folder', {
			sharedId: sharedId,
			copy :copy
	}, function() {
		$('#dropbox_table').load('/Dropbox-API/index.php/login/files_display'),
		$('#loading_div').hide(),
	  $('#leave_folder_modal').modal('hide');
	});
}

function change_policy(){
	$('#loading_div').show();
	var path = $('input#shared_path').val();
	var sharedId = $('input#unSharedId').val();
	var newPolicy = $('input#newPolicy').val();
	$('#dropbox_table').load('/Dropbox-API/index.php/login/change_acl_policy', {
			sharedId: sharedId,
			newPolicy: newPolicy
	}, function() {
		$('#dropbox_table').load('/Dropbox-API/index.php/login/shared_folder_display', {
			url: path
		}),
		$('#loading_div').hide();
	});
}

function show_modal_make_owner(id){	
	$('#loading_div').show();
	var path = $('input#shared_path').val();
	var name = $('input#newOwnerName').val();
	$('#modal_div').load('/Dropbox-API/index.php/login/show_modal_new_owner', {
			path: path,
			name: name,
			accountId: id
	}, function() {
		$('#loading_div').hide(),
		$('#make_owner_modal').modal('show');
	});
}

function make_owner(id){
	$('#loading_div').show();
	var path = $('input#shared_path').val();
	var sharedId =$('input#unSharedId').val();

	$('#dropbox_table').load('/Dropbox-API/index.php/login/make_owner', {
			path: path,
			sharedId: sharedId,
			accountId: id
	}, function() {
		$('#dropbox_table').load('/Dropbox-API/index.php/login/shared_folder_display', {
			url: path
		}),
		$('#loading_div').hide(),
	  $('#make_owner_modal').modal('hide');
	});
}

function update_member(mail){
	$('#loading_div').show();
	var path = $('input#shared_path').val();
	var email = mail;
	var sharedId = $('input#sharedId').val();
	var new_role = $('select#new_role').val();
	$('#dropbox_table').load('/Dropbox-API/index.php/login/update_member', {
			path: path,
			sharedId: sharedId,
			email : email,
			new_role : new_role
	}, function() {
		$('#dropbox_table').load('/Dropbox-API/index.php/login/shared_folder_display', {
			url: path
		}),
		$('#loading_div').hide();
	});
}

function show_modal_remove_member(name, mail){	
	$('#loading_div').show();
	var path = $('input#shared_path').val();
	var name = name;
	var email = mail;
	$('#modal_div').load('/Dropbox-API/index.php/login/show_modal_remove_member', {
			path:path,
			name:name,
			email: email
	}, function() {
		$('#loading_div').hide(),
		$('#remove_member_modal').modal('show');
	});
}
function remove_member(mail){
	$('#loading_div').show();
	var path = $('input#shared_path').val();
	var email = mail;
	var sharedId = $('input#sharedId').val();
	var copy = false;
	if($('#remove_member_check').is(':checked')){
		copy = true;
	}
	$('#dropbox_table').load('/Dropbox-API/index.php/login/remove_member', {
			path: path,
			sharedId: sharedId,
			email : email,
			copy: copy
	}, function() {
		$('#dropbox_table').load('/Dropbox-API/index.php/login/shared_folder_display', {
			url: path
		}),
		$('#loading_div').hide(),
		$('#remove_member_modal').modal('hide');
	});
}

function show_modal_remove_invitee(mail){	
	$('#loading_div').show();
	var path = $('input#shared_path').val();
	var name = mail;
	$('#modal_div').load('/Dropbox-API/index.php/login/show_modal_remove_invitee', {
			path:path,
			name:name
	}, function() {
		$('#loading_div').hide(),
		$('#remove_invitee_modal').modal('show');
	});
}
function remove_invitee(mail){
	$('#loading_div').show();
	var path = $('input#shared_path').val();
	var email = mail;
	var sharedId = $('input#sharedId').val();
	var copy = false;
	if($('#remove_invitee_check').is(':checked')){
		copy = true;
	}
	$('#dropbox_table').load('/Dropbox-API/index.php/login/remove_invitee', {
			path: path,
			sharedId: sharedId,
			email : email,
			copy: copy
	}, function() {
		$('#dropbox_table').load('/Dropbox-API/index.php/login/shared_folder_display', {
			url: path }),
		$('#loading_div').hide(),
		$('#remove_invitee_modal').modal('hide');
	});
}

function add_member(){
	$('#loading_div').show();
	var sharedId = $('input#sharedId').val();
	var email = $('input#add_email').val();
  var role = $('select#add_role').val();
  var path = $('input#shared_path').val(); 

  function validateEmail(email) { 
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
  }
  var tested = validateEmail(email);
  
	if(email === ''){
		alert('Please enter an email adress');
		$('#loading_div').hide();
		return false;
	}if(tested === false){
		alert("Invalid email adress");
		$('#loading_div').hide();
		return false;
	}
	$.ajax({
		url: '/Dropbox-API/index.php/login/add_member',
		type: 'POST',
		data: {sharedId: sharedId, email: email, role: role},
		success: function(result){
			if(result.includes('Exception')){
	    	alert(result);
	    }
			load_shared_folder(path);
			$('#loading_div').hide();
		},
		error: (error) => {
			alert("Member not added");
			load_shared_folder(path);
			$('#loading_div').hide();
		}
	});
}
/*-------------------------SHARED SUB FOLDER FUNCTIONS-------------------------*/
function new_shared_sub_sub_folder(){
	$('#loading_div').show();
	var folder_name = $('input#shared_sub_sub_folder_name').val();
	var url = $('input#shared_sub_path').val();
	var path = url + "/" + folder_name;
	if(folder_name === ''){
		alert("Please enter a folder name");
		return false;
	}
	$.ajax({
		url: '/Dropbox-API/index.php/login/create_new_shared_sub_folder',
		type: 'POST',
		data: {folder_name: folder_name, path: path},
		success: function(result){
			if(result.includes('path/conflict/folder/')){
	    	alert('Folder name already use. Please choose a uniqe name');
	    }
			load_shared_sub_folder(url);
			$('#loading_div').hide();
		},
		error: (error) => {
			alert("Folder not created");
			load_shared_sub_folder(url);
			$('#loading_div').hide();
		}
	});
}
function upload_from_shared_sub_folder(){
	$('#loading_div').show();
	var file_data = $('#fileInSharedSubFolder').prop('files')[0];
  var form_data = new FormData();
  form_data.append('userfile', file_data);
 	var url = $('input#shared_sub_path').val();
  $.ajax({
    url: '/Dropbox-API/index.php/login/upload_from_shared_sub_folder?path=' + url, 
    type: "POST",
    data : form_data,
    processData: false,
    contentType: false,
    success: function(data){ 
      load_shared_sub_folder(url);
      $('#loading_div').hide();
    },
    error: function(error) {
      alert("File couldn't be uploaded"); 
      load_shared_sub_folder(url);
      $('#loading_div').hide();
    }
  });
}

function show_modal_in_shared_sub_folder(url){	
	$('#loading_div').show();
	$('#modal_div').load('/Dropbox-API/index.php/login/show_modal_shared_sub_folder?path=' + url, {
			url: url
	}, function() {
		$('#loading_div').hide(),
		$('#delete_from_shared_sub_folder_modal').modal('show');
	});
}
function delete_from_shared_sub_folder(url){
	$('#loading_div').show();
	var path = $('input#shared_sub_path').val();
	$('#dropbox_table').load('/Dropbox-API	/index.php/login/delete_from_shared_folder?path=' + url, {
			url:url
	}, function() {
		$('#dropbox_table').load('/index.php/login/shared_sub_folder_display', {
			url: path
		}),
		$('#loading_div').hide(),
	  $('#delete_from_shared_sub_folder_modal').modal('hide');
	});
}

</script>
