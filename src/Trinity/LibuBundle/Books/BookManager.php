<?php

namespace Trinity\LibuBundle\Books;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BookManager extends Controller {

	static protected $file; 

	static protected $filename; 

	static protected $array_file; 

    public function __construct() {
    	self::$file = ""; 
    	self::$filename = ""; 
    	self::$array_file = ""; 
    }	

    public function setFilename($filename) {
    	self::$filename = $filename;   	
    }

    public function getFilename() {
    	return self::$filename;
    }

    public function setFile($file) {
    	self::$file = $file;  	
    }

    public function getFile() {
    	return self::$file;
    }

    public function convertArrayFile() {
        self::$array_file = array_map('str_getcsv', file(self::$file));
        echo "<meta charset='UTF-8' />"; echo "<pre>";  print_r(self::$array_file); echo "</pre>";
        return self::$array_file;
    }

    public function getArrayFile() {
    	return self::$array_file; 
    }

    public function saluda() {
    	echo "<br>Hola";
    }

}