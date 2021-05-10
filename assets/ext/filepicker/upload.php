<?php
class UploadHandler
{
	protected $options;
	// PHP File Upload error message codes:
	// http://php.net/manual/en/features.file-upload.errors.php
	protected $error_messages;

	function __construct($initialize = true,$fieldid,$fileTypes="",$myapp) {
		$this->error_messages = array(
			1 => __('The uploaded file exceeds the upload_max_filesize directive in php.ini','emd-plugins'),
			2 => __('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form','emd-plugins'),
			3 => __('The uploaded file was only partially uploaded','emd-plugins'),
			4 => __('No file was uploaded','emd-plugins'),
			6 => __('Missing a temporary folder','emd-plugins'),
			7 => __('Failed to write file to disk','emd-plugins'),
			8 => __('A PHP extension stopped the file upload','emd-plugins'),
			'post_max_size' => __('The uploaded file exceeds the post_max_size directive in php.ini','emd-plugins'),
			'max_file_size' => __('File is too big','emd-plugins'),
			'min_file_size' => __('File is too small','emd-plugins'),
			'accept_file_types' => __('Filetype not allowed','emd-plugins'),
			'max_number_of_files' => __('Maximum number of files exceeded','emd-plugins'),
			'max_width' => __('Image exceeds maximum width','emd-plugins'),
			'min_width' => __('Image requires a minimum width','emd-plugins'),
			'max_height' => __('Image exceeds maximum height','emd-plugins'),
			'min_height' => __('Image requires a minimum height','emd-plugins')
			);

		if (!empty($_FILES)) {
			$upload_file = 1;
			// Validate the file type
			if(!empty($fileTypes))
			{
				$fileTypes_arr = explode(",",$fileTypes);
		
				$fileParts = pathinfo($_FILES['file']['name']);
				if (!in_array(strtolower($fileParts['extension']),$fileTypes_arr)) {
					$upload_file = 0;
					echo __('Invalid file type.','emd-plugins');
				}
				else {
					$upload_file = 1;
				}
			}
			
			if($upload_file == 1){
				$file = wp_handle_upload($_FILES['file'] , array( 'test_form' => false ) );
				if(isset($file['error'])){
					echo $file['error'];
				}
				else {
					$_FILES['file']['path'] = $file['file'];
					if(!empty($myapp)){
						$new_sess_files = Array();
						$sess_name = strtoupper($myapp);
						$session_class = $sess_name();
						$sess_files = $session_class->session->get('uploads');
						if(!empty($sess_files) && is_array($sess_files)){
							$new_sess_files = $sess_files;
						}
						if(empty($sess_files[$fieldid])){
							$new_sess_files[$fieldid][]  = $_FILES['file'];
						}
						elseif(is_array($sess_files[$fieldid])){
							$new_sess_files[$fieldid]  = $sess_files[$fieldid];
							$new_sess_files[$fieldid][]  = $_FILES['file'];
						}
						$session_class->session->set('uploads',$new_sess_files);
					}
					echo '1';
				}
			}
		}
	}
}
?>
