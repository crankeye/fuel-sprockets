<?php

/**
 * Part of Fuel Sprockets
 *
 * @package    Fuel Sprockets
 * @version    1.0
 * @author     Veselin Vasilev @vesselinv
 * @license    MIT License
 * @copyright  2013 Veselin Vasilev
 * @link       http://vesselinv.com/fuel-sprockets
 */

namespace Sprockets;

/**
 * The File class allows for working with files and directories
 *
 * @package     Fuel Sprockets
 */

class Sprockets_File 
{
	// What file types should be attepted to be parsed
	protected	$accepted_file_types = array(
		'js', 'coffee', 'css', 'scss', 'less'
	);

	function __construct() {}

	/**
	 * @access 	public
	 * @param 	string filepath
	 * @return 	string file source
	 */
	public function read_source($file)
	{
		$path = trim($file);

		if ( $source = file_get_contents($path) )
		{
			return $source;
		} else {
			throw new FileNotFound("File $file could not be found.", 1);
		}
	}	

	/**
	 * @access 	public
	 * @param 	string filepath
	 * @param 	string source
	 * @return 	void / FileException
	 */
	public function save_file($file_path, $source)
	{
		$path = trim($file_path);

		if ( ! $save = file_put_contents($path, $source) )
		{
			throw new FileException("$file_path could not be saved. Do you have write permissions?", 1);
		}
	}

	/**
	 * @access 	public
	 * @param 	string filepath
	 * @return 	string extension
	 */
	public function get_extension($file_path)
	{
		$path = trim($file_path);

		return pathinfo($path, PATHINFO_EXTENSION);
	}

	/**
	 * @access 	public
	 * @param 	string filepath
	 * @return 	int last modified time in UTC
	 */
	public function get_filemtime($file_path)
	{
		$path = trim($file_path);

		if ( ! is_file($path) ) {
			throw new FileNotFound("Could not get Last Modified Date for $path", 1);
		}
		return filemtime($path);
	}

	/**
	 * @access 	public
	 * @param 	string url
	 * @return 	int last modified time in UTC
	 */
	public function get_remotemtime($url)
	{
		$h = get_headers(trim($url), 1);

		if ( ! empty($h) && strstr($h[0], '200') !== FALSE ) {
		    return strtotime($h['Last-Modified']);
		} else {
			throw new FileException("Could not get Last Modified Date for $url", 1);
			
		}
	}
	
	/**
	 * @access 	public
	 * @param 	string directory path
	 * @return 	array  list of full file paths
	 */
	public function get_files_in_dir($dir_path, $recursive = false) {

		# Add traling slash
		$folder = $this->fix_trailing_slash($dir_path); 

		# Scan entire directory
		$scan = array_diff(scandir($folder), array('..', '.'));

		$files = array();
		foreach ($scan as $key => $file) {

			$fullpath = $folder . $file;
			$ext = pathinfo($fullpath, PATHINFO_EXTENSION);

			# Add file to final file list
			is_file($fullpath) && in_array($ext, $this->accepted_file_types) 
				and $files[] = $fullpath;

			if ( is_dir($folder . $file) && $recursive == true ) {

				$subdir = $this->fix_trailing_slash( $folder . $file );
				$files[] = $this->get_files_in_dir($subdir .'/', $recursive);				
			}
		}

		# Flatten the array
		$file_list 	= \Arr::flatten($files, '_');

		# Remove empty values
		$file_list 	= array_filter($file_list, function($item){
			return ! empty($item);
		});

		# Remove duplicates
		return array_unique($file_list);
	}

	/**
	 * Add traling slash to end a folderpath if not present
	 * @access 	public
	 * @param 	string directory path
	 * @return 	string directory path
	 */
	public function fix_trailing_slash($input) {
		substr($input, -1) !== "/" and $input = $input . "/";
		return $input;
	}
}

class FileNotFound extends \FuelException {}
class FileException extends \FuelException {}