<?php
class ISBNDBServicePublisher extends ISBNDBServiceResult 
{

	/**
	 * @var string
	 */
	public $publisherId = null;
	
	/**
	 * @var string
	 */
	public $name = null;
	
	/**
	 * @var string
	 */
	public $location = null;
	
	/**
	 * @var array
	 */
	public $categoryList = array();
	
	
	/**
	 * Constructs a new ISBNDBServicePublisher that contains information about a 
	 * single search result.
	 * @param DOMElement $publisherData The authorData element representing a book
	 */
	public function __construct ($publisherData)
	{
		parent::__construct($publisherData);
	}
	
	/**
	 * Parses the $publisherData element
	 */
	protected function parseResponse()
	{
		$this->publisherId = $this->resultElement->getAttribute('publisher_id');
		$this->name = $this->resultElement->getElementsByTagName('Name')->item(0)->nodeValue;
		$details = $this->resultElement->getElementsByTagName('Details')->item(0);
		if($details)
		{
			$this->location = $details->getAttribute('details');
		}
		
		/**
		 * If categories are requested. Parse these as well
		 */
		$categories = $this->resultElement->getElementsByTagName('Categories')->item(0);
		if($categories)
		{
			foreach($categories->getElementsByTagName('Category') as $category)
			{
				$categoryDetails = array('categoryId'=>$category->getAttribute('category_id'),
										 'categoryName'=>$category->nodeValue);
				$this->categoryList[] = $categoryDetails;
			}
		}
	}
}
?>