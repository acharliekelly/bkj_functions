<?php
// This function will search through the site's files and find any error.log or debug.log files and display them in the site's ITSR
function bkjf_get_debug_log() {
	$foundFiles = [];
	$bigFiles = [];
	$output = '';
	$flaggedFileCount = 0;
	$specialDatabaseFound = false; // Initialize flag for .mmdb files
	// Get the current directory
	$currentDirectory = __DIR__;

	// Go up three levels to get the desired directory
	$directory = dirname(dirname(dirname($currentDirectory)));

	$skip_extension = '^(?!.*\.(php|css|htm|html)$)';
	$files_to_find = '(php_errorlog|error_log|debug\.log|access_log|apache_error\.log|nginx_error\.log|syslog|app\.log|application\.log|.*\.mmdb)';
	$extensions_to_find = 'bak|zip';
	// Combine $skip_extension, $files_to_find, and $extensions_to_find in the pattern
	$pattern = '/(' . $skip_extension . '|' . $files_to_find . '|.*\.(?:' . $extensions_to_find . '))$/i';
	$maxfilesize = 5*1024*1024; // 1024*1024 = 1MB
	$maxfilesize_humanreadable = bkjf_formatFileSize($maxfilesize);
	$iterator = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
		RecursiveIteratorIterator::SELF_FIRST,
		RecursiveIteratorIterator::CATCH_GET_CHILD
		);

	foreach ($iterator as $file) {
		if ($file->isFile()) {
			$filename = $file->getFilename();
			
			if (preg_match($pattern, $filename)) {
				$foundFiles[] = $file->getPathname();
				$flaggedFileCount++; // Increment the counter
				
				// Check if the file is an .mmdb file
				if (preg_match('/\.mmdb$/i', $filename)) {
					// Get file modification time
					$fileMTime = filemtime($file);
					// Get current time and calculate the difference in seconds for 6 months (6 months = approximately 15778463 seconds)
					$sixMonthsAgo = time() - 15778463;
					
					if ($fileMTime < $sixMonthsAgo) {
						$specialDatabaseFound = true; // Set flag to true if file is older than 6 months
					}
				}
			}
			
			if (filesize($file) > $maxfilesize) {
				$bigFiles[] = $file->getPathname();
			}
		}
	}
    
	if (!empty($foundFiles)) {
        $output .= "<p style='color: red;'>Found $flaggedFileCount files that MAYBE should be removed?</p><table width='100%'>";
        foreach ($foundFiles as $file) {
			$fileSize = bkjf_formatFileSize(filesize($file));
		   	$fileDate = date("Y-m-d h:i:sa", filemtime($file));
			$file = str_replace($directory, '', $file);
			$output .= "<tr valign='top'><td class='namecolumn'>$file &nbsp;</td>";
			$output .= "<td class='sizecolumn'>$fileSize &nbsp;</td>";
			$output .= "<td class='datecolumn'>$fileDate </td></tr>";
        }
	   	$output .= "</table><br>";
    } else {
        $output .= "<p style='color: green;'>Found no error/log files, check FTP files just to be safe.</p><br>";
    }
	
	if (!empty($bigFiles)) {
		$output .= "<p style='color: red;'>Found " . count($bigFiles) . " files that are over $maxfilesize_humanreadable:</p><table width='100%'>";
		foreach ($bigFiles as $file) {
			$fileSize = bkjf_formatFileSize(filesize($file));
		   	$fileDate = date("Y-m-d h:i:sa", filemtime($file));
			$file = str_replace($directory, '', $file);
			$output .= "<tr valign='top'><td class='namecolumn'>$file &nbsp;</td>";
			$output .= "<td class='sizecolumn'>$fileSize &nbsp;</td>";
			$output .= "<td class='datecolumn'>$fileDate </td></tr>";
        }
	   	$output .= "</table><br>";
    }

   // Output message if an older .mmdb file is found
	if ($specialDatabaseFound) {
		$output .= "<p style='color:red;'>It looks like we have an older .mmdb file, consider refreshing.</p><br>";
	}
	
    return $output;
}

// Return output to report
$logOutput = bkjf_get_debug_log();
echo nl2br($logOutput);
