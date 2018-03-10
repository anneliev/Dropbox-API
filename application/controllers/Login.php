<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require './vendor/autoload.php';

use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;

class Login extends CI_Controller {

	public $authHelper = '';
	public $dropbox = '';
	public $callbackUrl = '';
	public $account = '';
	
  public function __construct()
  {
    parent::__construct();
    $this->load->helper(array('url_helper', 'url', 'form', 'file', 'directory'));
    $this->load->library('session');
    $this->load->helper('download');
  }  

/*----------Setting up Dropbox connection----------*/
  public function index()
  {
		$this->load->library('session');

		$this->session->set_userdata('client_id', '**');
		$this->session->set_userdata('client_secret', '**');
    
		$app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
		$this->dropbox = new Dropbox($app);

		$this->authHelper = $this->dropbox->getAuthHelper();
		$this->callbackUrl = "http://localhost:8888/Dropbox-API/index.php/login/login_callback";
		$authUrl = $this->authHelper->getAuthUrl($this->callbackUrl);

    $this->load->view('pages/login_start', array(
    	'authUrl' => $authUrl,
    	'dropbox' => $this->dropbox,
    	'authHelper' => $this->authHelper,
    	'callbackUrl' => $this->callbackUrl
    ));
  }
  
/*----------After OAuth2, getting dropbox account----------*/
  public function login_callback()
  {
  	$this->index();
		$this->load->library('session');


		if (isset($_GET['code']) && isset($_GET['state'])) 
		{    
	    $code = $_GET['code'];
			$state = $_GET['state'];

	    try {
				$this->callbackUrl = "http://localhost:8888/Dropbox-API/index.php/login/login_callback";
				$accessToken = $this->authHelper->getAccessToken($code, $state, $this->callbackUrl);
				$access_token = $accessToken->getToken();
				/*
				echo '<pre>';
				var_dump($this->callbackUrl);
				var_dump($accessToken);
				var_dump($access_token);
				echo '</pre>';
				*/
	    	
				
	  	}catch (\Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
	  		$msg = ($e->getMessage());
	  		
	      if(strpos($msg, 'code has already') !== false){
		    	echo "<script type='text/javascript'>alert('Error. Please sign in again');</script>";
			  }else if(strpos($msg, 'expired') !== false){
          echo "<script type='text/javascript'>alert('Your session has expired. Please sign in again');</script>";
        }else{
					echo "<script type='text/javascript'>alert(".json_encode($msg).");</script>";
					/*
					echo '<pre>';
					var_dump($msg);
					var_dump($e);
					echo '</pre>';
					*/
			  }
				redirect('', 'refresh');
			}
			$this->dropbox->setAccessToken($access_token);
			$this->account = $this->dropbox->getCurrentAccount();
	
			$this->session->set_userdata('accessToken', $access_token);
			$this->session->set_userdata('account', $this->account);
			$this->session->set_userdata('code', $code);
			$this->session->set_userdata('state', $state);
	
		}
		
/*
		echo '<pre>';
		var_dump($this->dropbox);
		echo '</pre>';
		*/
		$this->user_display();
  }

/*----------Empty the downloads/uploads folder----------*/
  public function deleteDir($dirPath)
	{
	  if (!is_dir($dirPath)) {
	    if (file_exists($dirPath) !== false) {
	      unlink($dirPath);
	    }
	    return;
	  }
    if ($dirPath[strlen($dirPath) - 1] != '/') {
      $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
      if (is_dir($file)) {
        $this->deleteDir($file);
      } else {
        unlink($file);
      }
    }
    rmdir($dirPath);
	}  

/*----------Sign out----------*/
  public function sign_out()
  {
  	$app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
		$this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));
    
    try{
    	$this->dropbox->postToAPI('/auth/token/revoke');
    }catch(\Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
			$msg = $e->getMessage();
  	}
  	redirect('https://www.dropbox.com/logout', 'refresh');
  }	
/*-------------------------ROOT FUNCTIONS-----------------------*/

/*----------Displaying users dropbox content----------*/
  public function user_display()
  {
  	$listFolderContents = $this->dropbox->listFolder("/");
		$items = $listFolderContents->getItems();
		$filesList = $items->all();
		
		$this->load->view('user/user_page', array(
		 	'account' => $account = $this->session->userdata('account'),
		  'filesList' => $filesList,
		));
  }

  /*----------Same as user_display but with new Dropbox app----------*/
  public function files_display()
  {
  	$app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
		$this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));

  	$listFolderContents = $this->dropbox->listFolder("/");
		$items = $listFolderContents->getItems();
		$filesList = $items->all();

		$this->load->view('user/root_page', array(
		 	'account' => $account = $this->session->userdata('account'),
		  'filesList' => $filesList
		));
  }
/*----------Download file----------*/
  public function download_file()
  {
  	$app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
		$this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));

  	if (isset($_GET['path'])) 
		{    
	    $path = $_GET['path'];
	  }
	  $path = str_replace('%20', ' ', $path);
		$dirPath = realpath(__DIR__ . '/../..') . '\download';
  	if(is_dir($dirPath)){
  		$this->deleteDir($dirPath);
  	}
  	mkdir($dirPath, 0777, TRUE);
  
  	$file = $this->dropbox->download($path);
  	$contents = $file->getContents();
  	file_put_contents($dirPath .$path, $contents);
  	$metaData = $file->getMetaData();
  	$metaData->getName();

  	force_download($dirPath . $path, NULL);
  }
/*----------Upload file in root----------*/
  public function do_upload() 
  {
  	$app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
		$this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));
    
	  $dirPath = realpath(__DIR__ . '/../..') . '\upload';
  	if(is_dir($dirPath)){
  		$this->deleteDir($dirPath);
  	}
		mkdir($dirPath, 0777, TRUE);

		$config['upload_path'] = './upload/';
  	$config['allowed_types'] = 'gif|jpg|jpeg|png|pdf|doc|docx|ppt|pptx|pps|ppsx|odt|txt|xls|xlsx|key|zip|mp3|mp4|ogg|wav|m4v';
  	$config['max_size'] = 20000;
  	$config['remove_spaces'] = FALSE;

  	$this->load->library('upload', $config);
  
  	if (!$this->upload->do_upload('userfile'))
  	{
			$error = array('error' => $this->upload->display_errors());
			$error = array_shift($error);
			var_dump($error);
  	}
  	else
  	{
  		$data = array('upload_data' => $this->upload->data());
  		$data = array_shift($data);
	
  		$pathToLocalFile = $data['full_path'];
  		$fileName = $data['file_name'];
  		$fileName = "/".$fileName;
  		
  		$mode = DropboxFile::MODE_READ;
  		$dropboxFile = DropboxFile::createByPath($pathToLocalFile, $mode);
  		round($fileSize = $dropboxFile->getSize());
  		
  		if($fileSize >= 100000000)
  		{
  			round($chunkSize = intval($fileSize / 4));
					$file = $this->dropbox->uploadChunked($dropboxFile, substr_replace($fileName, '/', 0, 0), $fileSize, $chunkSize, ['autorename' => true]);
  		}
  		else
  		{
  			$file = $this->dropbox->simpleUpload($dropboxFile, $fileName, ['autorename' => true]);
  		}
  	}
  } 

/*----------Delete file----------*/
  public function delete_file()
  {
  	$app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
		$this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));

  	if (isset($_GET['path'])) 
		{    
	    $path = $_GET['path'];
	  }
	  urldecode($path);
  	$path = str_replace('%20', ' ', $path);

  	$deletedFile = $this->dropbox->delete($path);
  }

/*----------Modal check when deleting file----------*/
  public function show_modal()
  {
  	if(isset($_GET['path']))
  	{
  		$path = $_GET['path'];
  	}
  	urldecode($path);
  	echo '
			<div class="modal fade" id="delete_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			  <div class="modal-dialog" role="document">
			    <div class="modal-content">
			      <div class="modal-header">
			        <h5 class="modal-title" id="exampleModalLabel">Delete</h5>
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			          <span aria-hidden="true">&times;</span>
			        </button>
			      </div>
			      <div class="modal-body">
			        <p class="text-muted">'.substr($path, strrpos($path, "/") +1).'</p>
			        <p style="color:black">Are you sure you want to delete this file/folder?</p>
			      </div>
			      <div class="modal-footer">
			        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			        <button type="button" class="btn btn-primary" onclick="delete_file('."'".urlencode($path)."'".');">Delete</button>
			      </div>
			    </div>
			  </div>
			</div>
  	';
  }

/*----------Modal check when deleting shared folder in root----------*/
  public function show_modal_shared_folder_root()
  {
  	if(isset($_GET['path']))
  	{
  		$path = $_GET['path'];
  	}
    urldecode($path);
  	echo '
			<div class="modal fade" id="delete_shared_from_root_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			  <div class="modal-dialog" role="document">
			    <div class="modal-content">
			      <div class="modal-header">
			        <h5 class="modal-title">Delete shared folder</h5>
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			          <span aria-hidden="true">&times;</span>
			        </button>
			      </div>
			      <div class="modal-body">
			        <p class="text-muted">'.substr($path, strrpos($path, "/") +1).'</p>
			        <p style="color:black">Are you sure you want to remove this shared file/folder from your Dropbox? This shared file/folder will stay shared with other members and you can re-add it later.</p>
			      </div>
			      <div class="modal-footer">
			        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			        <button type="button" class="btn btn-primary" onclick="delete_shared_from_root('."'".urlencode($path)."'".');">Delete</button>
			      </div>
			    </div>
			  </div>
			</div>
  	';
  }

/*----------Creates new folder in root----------*/
  public function create_new_folder()
  {
    $app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
		$this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));

  	$folder_name = $this->input->post('folder_name');
  	$folder_name = substr_replace($folder_name, '/', 0, 0);

  	try {
  		$folder = $this->dropbox->createFolder($folder_name);
  	}catch (\Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
			$msg = $e->getMessage();
			var_dump($msg);
      echo "<script type='text/javascript'>alert(".json_encode($msg).");</script>";
  	}
  }

/*----------If form not empty, creates new shared folder----------*/
  public function create_new_shared_folder()
  {
  	$this->load->helper('url');
    $app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
		$this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));

    $path = $this->input->post('url');
    $path = str_replace('%20', ' ', $path);
  	$folder_name = url_title($this->input->post('folder_name'), 'dash', TRUE);
  	$folder_name = substr_replace($folder_name, '/', 0, 0);
  	$shared_email = $this->input->post('shared_email');
  	$role = url_title($this->input->post('role'));
  	$ACL = url_title($this->input->post('ACL'));

  	try {
  		$folder = $this->dropbox->createFolder($folder_name);
  		$response = $this->dropbox->postToAPI('/sharing/share_folder', [
  			'path' => $folder_name, 
  			'acl_update_policy' => $ACL
  		]);
  		$data = $response->getDecodedBody();
  		$folderId = $data['shared_folder_id'];

  		$response = $this->dropbox->postToAPI('/sharing/add_folder_member', [
  			"shared_folder_id" => $folderId,
  			"members" => [
  				[
  					"member" => [
  					  ".tag" => "email",
  					  "email" => $shared_email
  				  ],
  				  "access_level" => $role
  			  ]
  			]
  		]);

  	}catch (\Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
			$msg = $e->getMessage();
			var_dump($msg);
      echo "<script type='text/javascript'>alert(".json_encode($msg).");</script>";
  	}
  }

/*-------------------------FOLDER FUNTIONS------------------------*/

/*----------View contents of specific folder----------*/
  public function folder_display()
  {
		$app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
		$this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));

  	$path = $this->input->post('url');

  	$listFolderContents = $this->dropbox->listFolder($path);
		$items = $listFolderContents->getItems();
		$filesList = $items->all();

		$this->load->view('user/folder_page', array(
		 	'account' => $account = $this->session->userdata('account'),
		  'filesList' => $filesList,
		  'path' => $path
		));
  }

/*----------download file inside folder----------*/
  public function download_from_folder()
  {
		$app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
		$this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));

  	if (isset($_GET['path'])) 
		{    
	    $path = $_GET['path'];
	  }
	  $path = str_replace('%20', ' ', $path);
	  $sub_path = substr($path, 0, strrpos($path, "/"));

  	$dirPath = realpath(__DIR__ . '/../..') . '\download';
  	if(is_dir($dirPath)){
  		$this->deleteDir($dirPath);
  	}
  	mkdir($dirPath, 0777, TRUE);
  	if(!is_dir($dirPath .$sub_path. "/")){
  		mkdir($dirPath .$sub_path. "/", 0777, TRUE);
  	}
  
  	$file = $this->dropbox->download($path);
  	$contents = $file->getContents();
  	file_put_contents($dirPath .$path, $contents);
  	$metaData = $file->getMetaData();
  	$metaData->getName();

  	force_download($dirPath.$path, NULL);
  }

/*----------Upload file from inside folder----------*/
  public function upload_from_folder() 
  {
		$app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
		$this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));
  
  	if (isset($_GET['path'])) 
		{    
	    $path = $_GET['path'];
	  }

	  $dirPath = realpath(__DIR__ . '/../..') . '\upload';
  	if(is_dir($dirPath)){
  		$this->deleteDir($dirPath);
  	}
  	mkdir($dirPath, 0777, TRUE);
	  if(!is_dir($dirPath .$path. "/")){
  		mkdir($dirPath .$path. "/", 0777, TRUE);
  	}

  	
  	$config['upload_path'] = './upload/'.$path;
  	$config['allowed_types'] = 'gif|jpg|jpeg|png|pdf|doc|docx|ppt|pptx|pps|ppsx|odt|txt|xls|xlsx|key|zip|mp3|mp4|ogg|wav|m4v';
  	$config['max_size'] = 20000;
  	$config['remove_spaces'] = FALSE;

  	$this->load->library('upload', $config);

  	if (!$this->upload->do_upload('userfile'))
  	{
  		
			$error = array('error' => $this->upload->display_errors());
			$error = array_shift($error);
      var_dump($error);
			//echo "<script type='text/javascript'>alert('.$error.');</script>";
  	}
  	else
  	{
  		
  		$data = array('upload_data' => $this->upload->data());
  		$data = array_shift($data);
	 		
  		$pathToLocalFile = $data['full_path'];
  		$fileName = $data['file_name'];
  		$fileName = substr_replace($fileName, $path."/", 0, 0);
  		
  		$mode = DropboxFile::MODE_READ;
  		$dropboxFile = DropboxFile::createByPath($pathToLocalFile, $mode);
  		round($fileSize = $dropboxFile->getSize());
  		
  		if($fileSize >= 100000000)
  		{
  			round($chunkSize = intval($fileSize / 4));
			  $file = $this->dropbox->uploadChunked($dropboxFile, $fileName, $fileSize, $chunkSize, ['autorename' => true]);
  		}
  		else
  		{
  			$file = $this->dropbox->simpleUpload($dropboxFile, $fileName, ['autorename' => true]);
  		}
  	}
  } 

  /*----------Delete file in folder----------*/
  public function delete_from_folder()
  {
		$app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
		$this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));

  	if (isset($_GET['path'])) 
		{    
	    $path = $_GET['path'];
	  }
    urldecode($path);
  	$path = str_replace('%20', ' ', $path);
  	$sub_path = substr($path, 0, strrpos($path, "/"));
 
  	$deletedFile = $this->dropbox->delete($path);
  }
	
/*----------Modal check when deleting from folder----------*/
  public function show_modal_folder()
  {
  	if(isset($_GET['path']))
  	{
  		$path = $_GET['path'];
  	}
    urldecode($path);
    echo '
      <div class="modal fade" id="delete_from_folder_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">Delete</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <p class="text-muted">'.substr($path, strrpos($path, "/") +1).'</p>
              <p style="color:black">Are you sure you want to delete this file/folder?</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary" onclick="delete_from_folder('."'".urlencode($path)."'".');">Delete</button>
            </div>
          </div>
        </div>
      </div>
    ';
  } 

/*----------Creates new folder in current subfolder----------*/
  public function create_new_sub_folder()
  {
    
  	$this->load->helper('url');
		$app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
		$this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));

    $path = $this->input->post('url');
    $path = str_replace('%20', ' ', $path);
  	$folder_name = $this->input->post('folder_name');
    $folder_name_with = substr_replace($folder_name, '/', 0, 0);
		$new_folder = $path . $folder_name_with;

  	try {
  		$this->dropbox->createFolder($new_folder);
  	}catch (\Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
			$msg = $e->getMessage();
		  var_dump($msg);
      echo "<script type='text/javascript'>alert(".json_encode($msg).");</script>";
  	}
  }
/*----------Shares a folder----------*/
  public function share_a_folder()
  {
  	$this->load->helper('url');
		$app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
		$this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));

  	//$folder_name = url_title($this->input->post('folder_name'), 'dash', TRUE);
  	//$folder_name = substr_replace($folder_name, '/', 0, 0);
    $folder_name = $this->input->post('folder_name');
  	$shared_email = $this->input->post('shared_email');
  	$role = url_title($this->input->post('role'));
		$ACL = url_title($this->input->post('ACL'));
		
		var_dump($folder_name);
		var_dump($shared_email);

  	try {
  		$response = $this->dropbox->postToAPI('/sharing/share_folder', [
  			'path' => $folder_name, 
  			'acl_update_policy' => $ACL
  		]);
  		$data = $response->getDecodedBody();
  		$folderId = $data['shared_folder_id'];

  		$response = $this->dropbox->postToAPI('/sharing/add_folder_member', [
  			"shared_folder_id" => $folderId,
  			"members" => [
  				[
  					"member" => [
  					  ".tag" => "email",
  					  "email" => $shared_email
  				  ],
  				  "access_level" => $role
  			  ]
  			]
  		]);

  	}catch (\Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
      $msg = $e->getMessage();
      var_dump($msg);
      echo "<script type='text/javascript'>alert(".json_encode($msg).");</script>";
    }
  }

/*-------------------------SHARED FOLDER FUNCTIONS-------------------------*/


/*----------View content of a shared folder----------*/
public function shared_folder_display()
  {
		$app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
		$this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));

  	$path = $this->input->post('url');

  	$listFolderContents = $this->dropbox->listFolder($path);
		$items = $listFolderContents->getItems();
		$filesList = $items->all();

		$folder = $this->dropbox->getMetaData($path);
		$shared = $folder->getSharingInfo();
    if(empty($shared)){
      $this->load->view('user/folder_page', array(
        'account' => $account = $this->session->userdata('account'),
        'filesList' => $filesList,
        'path' => $path,
      ));
    }else{
			$sharedId = $shared->shared_folder_id;

			$response = $this->dropbox->postToAPI('/sharing/list_folder_members', [
					"shared_folder_id" => $sharedId,
					"actions" => [],
					"limit" => 10
				]);
			$data = $this->dropbox->postToAPI('/sharing/get_folder_metadata', [
				"shared_folder_id" => $sharedId
			]);
			$acl_policy = $data->getBody();
		
			if(strpos($acl_policy, '"acl_update_policy": {".tag": "owner"}') !== false){
				$policy = 'owner';
				$newPolicy = 'editors';
			}else if(strpos($acl_policy, '"acl_update_policy": {".tag": "editors"}') !== false){
				$policy = 'editors';
				$newPolicy = 'owner';
			}
					
			$this->load->view('user/shared_folder_page', array(
				'account' => $account = $this->session->userdata('account'),
				'filesList' => $filesList,
				'path' => $path,
				'sharedId' => $sharedId,
				'dropbox' => $this->dropbox,
				'response' => $response,
				'policy' => $policy,
				'newPolicy' => $newPolicy
			));
		}
  }

/*----------View content of a shared sub folder----------*/
public function shared_sub_folder_display()
  {
		$app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
		$this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));

  	$path = $this->input->post('url');

  	$listFolderContents = $this->dropbox->listFolder($path);
		$items = $listFolderContents->getItems();
		$filesList = $items->all();
  	  	
		$this->load->view('user/shared_sub_folder_page', array(
		 	'account' => $account = $this->session->userdata('account'),
		  'filesList' => $filesList,
		  'path' => $path,
		));
  }


/*----------Upload from inside a shared folder----------*/  
  public function upload_from_shared_folder() 
  {
		$app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
		$this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));
  
  	if (isset($_GET['path'])) 
		{    
	    $path = $_GET['path'];
	  }

	  $dirPath = realpath(__DIR__ . '/../..') . '\upload';
  	if(is_dir($dirPath)){
  		$this->deleteDir($dirPath);
  	}
  	mkdir($dirPath, 0777, TRUE);
	  if(!is_dir($dirPath .$path. "/")){
  		mkdir($dirPath .$path. "/", 0777, TRUE);
  	}

  	$config['upload_path'] = './upload/'.$path;
  	$config['allowed_types'] = 'gif|jpg|jpeg|png|pdf|doc|docx|ppt|pptx|pps|ppsx|odt|txt|xls|xlsx|key|zip|mp3|mp4|ogg|wav|m4v';
  	$config['max_size'] = 20000;
  	$config['remove_spaces'] = FALSE;

  	$this->load->library('upload', $config);

  	if (!$this->upload->do_upload('userfile'))
  	{
			$error = array('error' => $this->upload->display_errors());
			$error = array_shift($error);
      var_dump($error);
			//echo "<script type='text/javascript'>alert('.$error.');</script>";
  	}
  	else
  	{
  		$data = array('upload_data' => $this->upload->data());
  		$data = array_shift($data);
	 		
  		$pathToLocalFile = $data['full_path'];
  		$fileName = $data['file_name'];
  		$fileName = substr_replace($fileName, $path."/", 0, 0);
  		
  		$mode = DropboxFile::MODE_READ;
  		$dropboxFile = DropboxFile::createByPath($pathToLocalFile, $mode);
  		round($fileSize = $dropboxFile->getSize());
  		
  		if($fileSize >= 100000000)
  		{
  			round($chunkSize = intval($fileSize / 4));
			  $file = $this->dropbox->uploadChunked($dropboxFile, $fileName, $fileSize, $chunkSize, ['autorename' => true]);
  		}
  		else
  		{
  			$file = $this->dropbox->simpleUpload($dropboxFile, $fileName, ['autorename' => true]);
  		}
  	}
  } 

  /*----------Delete file in shared folder----------*/
  public function delete_from_shared_folder()
  {
		$app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
		$this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));

  	if (isset($_GET['path'])) 
		{    
	    $path = $_GET['path'];
	  }
    urldecode($path);
  	$path = str_replace('%20', ' ', $path);

  	$deletedFile = $this->dropbox->delete($path);  	  
  }

  /*----------Modal check when deleting from shared folder----------*/
  public function show_modal_shared_folder()
  {
    if(isset($_GET['path']))
    {
      $path = $_GET['path'];
    }
    urldecode($path);
    echo '
      <div class="modal fade" id="delete_from_shared_folder_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Delete shared file/folder</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <p class="text-muted">'.substr($path, strrpos($path, "/") +1).'</p>
              <p style="color:black">Are you sure you want to remove this shared file/folder from your Dropbox? This shared file/folder will stay shared with other members and you can re-add it later.</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary" onclick="delete_from_shared_folder('."'".urlencode($path)."'".');">Delete</button>
            </div>
          </div>
        </div>
      </div>
    ';
  } 

/*----------Modal check for unsharing a folder----------*/
  public function show_modal_unshare_folder()
  {
    $path = $this->input->post('path');

    echo '
      <div class="modal fade" id="unshare_folder_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Unshare folder</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <p class="text-muted">'.$path.'</p>
              <p style="color:black">Everyone will be removed from this folder. You will still keep a copy of this folder in your Dropbox.</p>
              <label class="form-check-label">
                <input id="unshare_folder_check" type="checkbox">
                  Let removed members keep a copy of this shared folder
              </label>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary" onclick="unshare_folder();">Unshare</button>
            </div>
          </div>
        </div>
      </div>
    ';
  }

/*----------Unshare a folder----------*/
  public function unshare_folder()
  {
		$app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
		$this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));
   
    $sharedId = $this->input->post('sharedId');
    $copy = $this->input->post('copy');
    if($copy === "true"){
      $copy = true;
    }else{
      $copy = false;
    }
     try{
		  	$response = $this->dropbox->postToAPI('/sharing/unshare_folder', [
		  	  "shared_folder_id" => $sharedId,
		  	  "leave_a_copy" => $copy
		  	]);
      }catch (\Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
        $msg = $e->getMessage();
        echo "<script type='text/javascript'>alert(".json_encode($msg).");</script>";
      }
  }
  /*----------Modal check for leaving a folder----------*/
  public function show_modal_leave_folder()
  {
    if (isset($_GET['path'])) 
    {    
      $path = $_GET['path'];
    }

    $path = $this->input->post('path');
    echo '
      <div class="modal fade" id="leave_folder_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Leave folder</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <p class="text-muted">'.$path.'</p>
              <p style="color:black">If you continue, you won’t be able to access this folder anymore.</p>
                <label class="form-check-label">
                  <input id="leave_folder_check" type="checkbox">
                    Keep a copy of this folder
                </label>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary" onclick="leave_folder();">Leave</button>
            </div>
          </div>
        </div>
      </div>
    ';
  }

/*----------Leaving a folder----------*/
  public function leave_folder()
  {
    $app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
    $this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));

    $sharedId = $this->input->post('sharedId');
    $copy = $this->input->post('copy');
    if($copy === "true"){
      $copy = true;
    }else{
      $copy = false;
    }

     try{
        $response = $this->dropbox->postToAPI('/sharing/relinquish_folder_membership', [
          "shared_folder_id" => $sharedId,
          "leave_a_copy" => $copy
        ]);
      }catch (\Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
        $msg = $e->getMessage();
        echo "<script type='text/javascript'>alert(".json_encode($msg).");</script>";
      }  
  }
/*----------Update a member of a shared folder----------*/
	public function update_member()
	{
	  $app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
 		$this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));

    $sharedId = $this->input->post('sharedId');
	  $email = $this->input->post('email');
	  $new_role = $this->input->post('new_role');
		  
	  try{
	  	$response = $this->dropbox->postToAPI('/sharing/update_folder_member', [
	  	  "shared_folder_id" => $sharedId,
	  	  "member" => [
	  	  	".tag" => "email",
	  	  	"email" => $email
	  	  ],
	  	  "access_level" => $new_role
	  	]);
  	}catch (\Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
	  	$msg = $e->getMessage();
      echo "<script type='text/javascript'>alert(".json_encode($msg).");</script>";
  	}
	}
/*----------Modal check for removing a member from shared folder----------*/
  public function show_modal_remove_member()
  {
    $name = $this->input->post('name');
    $path = $this->input->post('path');
    $email = $this->input->post('email');
    
    echo '
      <div class="modal fade" id="remove_member_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Remove member</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <p class="text-muted">'.$path.'</p>
              <p>If you remove '.$name.', they won’t be able to see future changes to this shared folder.</p>
                <label class="form-check-label">
                  <input id="remove_member_check" type="checkbox">
                    Let '.$name.' keep a copy of this shared folder
                </label>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary" onclick="remove_member('."'".$email."'".');">Remove</button>
            </div>
          </div>
        </div>
      </div>
    ';
  }
/*----------Remove a member from shared folder----------*/	
  public function remove_member()
  {
		$app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
		$this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));

    $sharedId = $this->input->post('sharedId');
	  $email = $this->input->post('email');
    $name = $this->input->post('name');
    $copy = $this->input->post('copy');
    if($copy === "true"){
      $copy = true;
    }else{
      $copy = false;
    }

 	  try{
  	  $response = $this->dropbox->postToAPI('/sharing/remove_folder_member', [
  	  	"shared_folder_id" => $sharedId,
	  	  "member" => [
	  	  	".tag" => "email",
	  	  	"email" => $email
	  	  ],
	  	  "leave_a_copy" => $copy
  	  ]);
 	  }catch (\Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
	  	$msg = $e->getMessage();
	  	echo "<script type='text/javascript'>alert(".json_encode($msg).");</script>";
  	} 	 
  }
/*----------Modal check for removing an invitee from shared folder----------*/
  public function show_modal_remove_invitee()
  {
    $name = $this->input->post('name');
    $path = $this->input->post('path');
    
    $path = str_replace('%20', ' ', $path);
    echo '
      <div class="modal fade" id="remove_invitee_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Remove member</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <p class="text-muted">'.$path.'</p>
              <p>If you remove '.$name.', they won’t be able to see future changes to this shared folder.</p>
              <label class="form-check-label">
                <input id="remove_invitee_check" type="checkbox">
                  Let '.$name.' keep a copy of this shared folder
              </label>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary" onclick="remove_invitee('."'".$name."'".');">Remove</button>
            </div>
          </div>
        </div>
      </div>
    ';
  }
/*----------Remove an invitee from shared folder----------*/  
  public function remove_invitee()
  {
    $app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
    $this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));

    $sharedId = $this->input->post('sharedId');
    $email = $this->input->post('email');
    $name = $this->input->post('name');
    $copy = $this->input->post('copy');
    if($copy === "true"){
      $copy = true;
    }else{
      $copy = false;
    }

    try{
      $response = $this->dropbox->postToAPI('/sharing/remove_folder_member', [
        "shared_folder_id" => $sharedId,
        "member" => [
          ".tag" => "email",
          "email" => $email
        ],
        "leave_a_copy" => $copy
      ]);
    }catch (\Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
      $msg = $e->getMessage();
      echo "<script type='text/javascript'>alert(".json_encode($msg).");</script>";
    }    
  }

/*----------Add member to shared folder----------*/
  public function add_member()
  {
		$app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
		$this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));

  	$sharedId = $this->input->post('sharedId');
  	$email = $this->input->post('email');
  	$role = $this->input->post('role');
    
    try{
	  	$response = $this->dropbox->postToAPI('/sharing/add_folder_member', [
	  		"shared_folder_id" => $sharedId,
	 			"members" => [
	 				[
	 					"member" => [
			  		  ".tag" => "email",
	  				  "email" => $email
	  			  ],
	  			  "access_level" => $role
	 			  ]
	 			]
	 		]);
 		}catch (\Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
	  	$msg = $e->getMessage();
	  	var_dump($msg);
      echo "<script type='text/javascript'>alert(".json_encode($msg).");</script>";
  	}
  }
/*----------Modal check for making a member owner of a shared folder----------*/
  public function show_modal_new_owner()
  {
    $name = $this->input->post('name');
    $path = $this->input->post('path');
    $accountId = $this->input->post('accountId');
    
    echo '
      <div class="modal fade" id="make_owner_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Transfer ownership</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <p class="text-muted">'.$path.'</p>
              <p style="color:black">Make '.$name.' owner of this folder?</p>
              <p>Only '.$name.' will be able to unshare this folder or change folder settings.</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary" onclick="make_owner('."'".$accountId."'".');">Make owner</button>
            </div>
          </div>
        </div>
      </div>
    ';
  }
/*----------Transfer ownrship of a shared folder----------*/
   public function make_owner()
  {
		$app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
		$this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));

  	$sharedId = $this->input->post('sharedId');
  	$accountId = $this->input->post('accountId');
    
    try{
	  	$response = $this->dropbox->postToAPI('/sharing/transfer_folder', [
	  		"shared_folder_id" => $sharedId,
	 			"to_dropbox_id" => $accountId
	 		]);
	  }catch (\Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
	  	$msg = $e->getMessage();
		  echo "<script type='text/javascript'>alert(".json_encode($msg).");</script>";
	  }
  }
/*----------Change the access control level policy on a shared folder----------*/
  public function change_acl_policy()
  {
		$app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
		$this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));

    $sharedId = $this->input->post('sharedId');
    $newPolicy = $this->input->post('newPolicy');
    try{
	  	$response = $this->dropbox->postToAPI('/sharing/update_folder_policy', [
	  		"shared_folder_id" => $sharedId,
	 			"acl_update_policy" => $newPolicy
	 		]);
	  }catch (\Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
	  	$msg = $e->getMessage();
	  	echo "<script type='text/javascript'>alert(".json_encode($msg).");</script>";
  	}
  }

/*-------------------------SHARED SUB FOLDER FUNCTIONS-------------------------*/

/*----------Upload from inside a shared sub folder----------*/  
  public function upload_from_shared_sub_folder() 
  {
    $app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
    $this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));
  
    if (isset($_GET['path'])) 
    {    
      $path = $_GET['path'];
    }

    $dirPath = realpath(__DIR__ . '/../..') . '\upload';
    if(is_dir($dirPath)){
      $this->deleteDir($dirPath);
    }
    mkdir($dirPath, 0777, TRUE);
    if(!is_dir($dirPath .$path. "/")){
      mkdir($dirPath .$path. "/", 0777, TRUE);
    }

    $config['upload_path'] = './upload/'.$path;
    $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf|doc|docx|ppt|pptx|pps|ppsx|odt|txt|xls|xlsx|key|zip|mp3|mp4|ogg|wav|m4v';
    $config['max_size'] = 20000;
    $config['remove_spaces'] = FALSE;

    $this->load->library('upload', $config);

    if (!$this->upload->do_upload('userfile'))
    {
      $error = array('error' => $this->upload->display_errors());
      $error = array_shift($error);
      var_dump($error);
      //echo "<script type='text/javascript'>alert('.$error.');</script>";
    }
    else
    {
      $data = array('upload_data' => $this->upload->data());
      $data = array_shift($data);
      
      $pathToLocalFile = $data['full_path'];
      $fileName = $data['file_name'];
      $fileName = substr_replace($fileName, $path."/", 0, 0);
      
      $mode = DropboxFile::MODE_READ;
      $dropboxFile = DropboxFile::createByPath($pathToLocalFile, $mode);
      round($fileSize = $dropboxFile->getSize());
      
      if($fileSize >= 100000000)
      {
        round($chunkSize = intval($fileSize / 4));
        $file = $this->dropbox->uploadChunked($dropboxFile, $fileName, $fileSize, $chunkSize, ['autorename' => true]);
      }
      else
      {
        $file = $this->dropbox->simpleUpload($dropboxFile, $fileName, ['autorename' => true]);
      }
    }
  } 
/*----------Modal check when deleting from shared sub folder----------*/
  public function show_modal_shared_sub_folder()
  {
    if(isset($_GET['path']))
    {
      $path = $_GET['path'];
    }
    urldecode($path);
    echo '
      <div class="modal fade" id="delete_from_shared_sub_folder_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Delete shared file/folder</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <p class="text-muted">'.substr($path, strrpos($path, "/") +1).'</p>
              <p style="color:black">Are you sure you want to remove this shared file/folder from your Dropbox? This shared file/folder will stay shared with other members and you can re-add it later.</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary" onclick="delete_from_shared_sub_folder('."'".urlencode($path)."'".');">Delete</button>
            </div>
          </div>
        </div>
      </div>
    ';
  } 
/*----------Creates new folder in current subfolder----------*/
  public function create_new_shared_sub_folder($path)
  {
    
    $this->load->helper('url');
    $app = new DropboxApp($this->session->userdata('client_id'), $this->session->userdata('client_secret'));
    $this->dropbox = new Dropbox($app);
    $this->dropbox->setAccessToken($this->session->userdata('accessToken'));

    $path = $this->input->post('path');
    $path = str_replace('%20', ' ', $path);
    $folder_name = $this->input->post('folder_name');
    $folder_name_with = substr_replace($folder_name, '/', 0, 0);
    $folder = $path . $folder_name_with;
    $sub_folder = substr($folder, 0, strrpos($path, "/"));
    $new_folder = $sub_folder . $folder_name_with;

    try {
      $this->dropbox->createFolder($new_folder);
    }catch (\Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
      $msg = $e->getMessage();
      var_dump($msg);
      echo "<script type='text/javascript'>alert(".json_encode($msg).");</script>";
    }
  }

}
