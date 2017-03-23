<?php
// Include the configuration file
include "configuration.php";


// This function is responsible for scanning and getting all the files starting from the given to the end of its sub-folders
function vpb_goten_file($dir) 
{
    if($dh = opendir($dir)) 
	{
        $files = Array();
        $inner_files = Array();
        while($file = readdir($dh)) 
		{
            if($file != "." && $file != ".." && $file[0] != '.') 
			{
                if(is_dir($dir . "/" . $file)) 
				{
                    $inner_files = vpb_goten_file($dir . "/" . $file);
                    if(is_array($inner_files)) $files = array_merge($files, $inner_files); 
                } 
				else 
				{
                    array_push($files, $dir . "/" . $file);
                }
            }
        }
        closedir($dh);
        return $files;
    }
}

// File compression / Resizing of the files
function vpb_resize_files($width, $height, $base_dir)
{
	$total_resized_files = 0;
	$total_un_resized_files = 0;
	$new_width = $new_height = 0;
					
	if(opendir($base_dir)) 
	{
		if (!$width) { $width = 99999999999999; }
		if (!$height) { $height	= 99999999999999; }
		
		$vpb_allowed_extensions = array("gif", "jpg", "jpeg", "png"); // Allowed file types
				
		foreach (vpb_goten_file($base_dir) as $key=>$the_file)
		{
			$vpb_file_extensions = pathinfo(strtolower(basename($the_file)), PATHINFO_EXTENSION);
			
			if (in_array($vpb_file_extensions, $vpb_allowed_extensions))
			{
				// Get original image x/y
				list($w, $h) = getimagesize($the_file);
				
				if (!$w) { $w = 99999999999999; }
				if (!$h) { $h	= 99999999999999; }
				
				$max_width = $width > $w ? $w : $width;
				$max_height = $height > $h ? $h : $height;
				
				// Calculate to determine a new width and height to give to the new compressed file
				if ($w > $h) {
				  $new_height = floor(($h/$w)*$max_width);
				  $new_width  = $max_width;
				} else {
				  $new_width  = floor(($w/$h)*$max_height);
				  $new_height = $max_height;
				}
				
				// Location to save the compressed files
				$path = $the_file;
				
				// Read binary data from image file 
				$imgString = @file_get_contents($the_file);
				
				// Create image from string 
				$image = @imagecreatefromstring($imgString);
				$tmp = @imagecreatetruecolor($new_width, $new_height);
				@imagecopyresampled($tmp, $image, 0, 0, 0, 0, $new_width, $new_height, $w, $h);
				
				// Save image file
				switch ($vpb_file_extensions) {
					case 'jpeg':
						$ok = imagejpeg($tmp, $path, 100);
						break;
					case 'jpg':
						$ok = imagejpeg($tmp, $path, 100);
						break;
					case 'png':
						$ok = imagepng($tmp, $path, 0);
						break;
					case 'gif':
						$ok = imagegif($tmp, $path);
						break;
					default:
						exit;
						break;
				}
				if($ok == 1) {
					$total_resized_files++;
				} else {
					$total_un_resized_files++;
				}
				
				// cleanup memory 
				imagedestroy($image);
				imagedestroy($tmp);
			}
			else
			{
				// Not supported file format
			}
		}
		return 'Total Files Resized: <b>'.$total_resized_files.'</b><br />
				Total Un-resized Files: <b>'.$total_un_resized_files.'</b>';
	}
	else
	{
		return "Could not open the directory: <b>".$base_dir."</b> for reading files";
	}
}


// Function to resize all the files in the different directories starting from the base directory
echo vpb_resize_files(RESIZE_FILES_TO_width, RESIZE_FILES_TO_height, UPLOADED_FILES_BASE_DIRECTORY); 

?>
