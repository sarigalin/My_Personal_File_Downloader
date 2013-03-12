<?php
define(DOWNLOAD_DIR ,'files/');
function download()
{  
	if(isset($_POST['url']))
	{
		echo 'Download Started!</br>';
		//echo '<img src="images/load.gif" /><br />';
		flush();
		
		$url = $_POST["url"];
		$filename = basename($url);
		$pos = strpos($filename, '?');
		if ($pos !== false) {
			$filename = substr($filename,0, $pos);
		}
		$file_path = DOWNLOAD_DIR.$filename;
		$path = dirname($_SERVER['SCRIPT_FILENAME']).DIRECTORY_SEPARATOR. $file_path;
		
		$fp = fopen ($path, 'w+');
		$ch = curl_init($url); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 0);
		curl_setopt($ch, CURLOPT_FILE, $fp); 
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE); 
		$data=curl_exec($ch);
		$curlerror=curl_error($ch);
		curl_close($ch);
		fclose($fp);
	
		if($data==false){
			echo 'download of source file failed.<br />'.$curlerror;	
		}
		
		echo 'Download Finished!!!</br>';
		echo 'Download Link: <a href="' . $file_path . '">' . $filename .'</a>';
	}
}

function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 

    // Uncomment one of the following alternatives
     $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow]; 
} 

function sortRows($data)
{
	$size = count($data);

	for ($i = 0; $i < $size; ++$i) {
		$row_num = findSmallest($i, $size, $data);
		$tmp = $data[$row_num];
		$data[$row_num] = $data[$i];
		$data[$i] = $tmp;
	}

	return ( $data );
}

function findSmallest($i, $end, $data)
{
	$min['pos'] = $i;
	$min['value'] = $data[$i]['data'];
	$min['dir'] = $data[$i]['dir'];
	for (; $i < $end; ++$i) {
		if ($data[$i]['dir']) {
			if ($min['dir']) {
				if ($data[$i]['data'] < $min['value']) {
					$min['value'] = $data[$i]['data'];
					$min['dir'] = $data[$i]['dir'];
					$min['pos'] = $i;
				}
			} else {
				$min['value'] = $data[$i]['data'];
				$min['dir'] = $data[$i]['dir'];
				$min['pos'] = $i;
			}
		} else {
			if (!$min['dir'] && $data[$i]['data'] < $min['value']) {
				$min['value'] = $data[$i]['data'];
				$min['dir'] = $data[$i]['dir'];
				$min['pos'] = $i;
			}
		}
	}
	return ( $min['pos'] );
}
function show_dir()
{
	echo "\n\n";
	$dir = $_SERVER["SCRIPT_FILENAME"];
	$size = strlen($dir);
	while ($dir[$size - 1] != '/') {
		$dir = substr($dir, 0, $size - 1);
		$size = strlen($dir);
	}
	$dir = DOWNLOAD_DIR;
	if (is_dir($dir)) {
		if ($handle = opendir($dir)) {
			$size_document_root = strlen($_SERVER['DOCUMENT_ROOT']);
			$pos = strrpos($dir, "/");
			$topdir = substr($dir, 0, $pos + 1);
			$i = 0;
  	  		while (false !== ($file = readdir($handle))) {
        		if ($file != "." && $file != "..") {
					$rows[$i]['data'] = $file;
					$rows[$i]['dir'] = is_dir($dir . "/" . $file);
					$i++;
				}
			}
    		closedir($handle);
		}

		$size = count($rows);
		$rows = sortRows($rows);
		echo "<table style=\"white-space:pre;\">";

		for ($i = 0; $i < $size; ++$i) {
			$file = $dir.DIRECTORY_SEPARATOR.$rows[$i]['data'];
			echo "<tr>";
				echo "<td>";
					if ($rows[$i]['dir']) {
						echo "[DIR]";
						$file_type = "dir";
					} else {
						echo "[FILE]";
						$file_type = "file";
					}
				echo "</td>";
				echo "<td>    ";
		        		echo "<a href='", $file, "'>", $rows[$i]['data'], "</a>\n";
				echo "</td>";
	            		echo "<td>";
            				echo  formatBytes(filesize($file));
           	 		echo "</td>";
			echo "</tr>";
        	}
		echo "</table>";
	} else if (is_file($dir)) {
		$pos = strrpos($dir, "/");
		$topdir = substr($dir, 0, $pos);
		echo "<a href='", $self, "?dir=", $topdir, "'>", $topdir, "</a>\n\n";
		$file = file($dir);
		$size = count($file);
		for ($i = 0; $i < $size; ++$i)
			echo htmlentities($file[$i], ENT_QUOTES);
	} else {
		echo "bad file or unable to open";
	}
}	
?>




<html> 
<head> 
	<title>My Personal File Downloader</title> 
	<style TYPE="text/css">
	<!--
	
	* { font-family: courier new; font-size: 10pt;
	}
	
	A { text-decoration: none;
	}
	
	A:HOVER { text-decoration: underline;
	}
	
	-->
	</style>
</head> 
<body> 
	<form method="post" action="<?php echo $PHP_SELF;?>"> 
		URL:<input type="text" size="100" name="url"> <input type="submit" name="submit" value="Download">
	</form>
	<?php download() ?>
	</hr>
	<pre>
	<?php show_dir(); ?>
	</pre>
</body>
</html>
