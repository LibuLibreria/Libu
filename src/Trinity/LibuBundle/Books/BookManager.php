<?php

namespace Trinity\LibuBundle\Books;

class BookManager {

	public $file; 

	public $filename; 

    public function setFilename($filename) {
    	$this->filename = $filename;   	
    }

    public function getFilename() {
    	return $this->filename;
    }

    public function setFile($file) {
    	$this->file = $file;  	
    }

    public function getFile() {
    	return $this->file;
    }

    public function getArrayFile() {
        $csv = array_map('str_getcsv', file($this->file));
        echo "<meta charset='UTF-8' />"; echo "<pre>";  print_r($csv); echo "</pre>";
    }

}