<?php

namespace Trinity\LibuBundle\Crawler\ISBNdb;


class ISBNDBServiceCategory extends ISBNDBServiceResult 
{
	/**
	 * @var string
	 */
	public $categoryId = null;
	
	/**
	 * @var string
	 */
	public $parentId = null;
	
	/**
	 * @var string
	 */
	public $name = null;
	
	/**
	 * @var string
	 */
	public $summary = null;
	
	/**
	 * @var string
	 */
	public $depth = null;
	
	/**
	 * @var array
	 */
	public $subCategoryList = array();
	
	/**
	 * Constructs a new ISBNDBServiceCategory that contains information about a 
	 * single search result.
	 * @param DOMElement $categoryData The CategoryData element representing a category
	 */
	public function __construct ($categoryData)
	{
		parent::__construct($categoryData);
	}
	
	/**
	 * Parses the $authorData element
	 */
	protected function parseResponse()
	{
		$this->categoryId = $this->resultElement->getAttribute('category_id');
		$this->parentId = $this->resultElement->getAttribute('parent_id');
		
		/**
		 * If details are requested. Parse these as well
		 */
		$details = $this->resultElement->getElementsByTagName('Details')->item(0);
		if($details)
		{
			$this->name = $this->resultElement->getElementsByTagName('Name')->item(0)->nodeValue;
			$this->summary = $details->getAttribute('summary');
			$this->depth = $details->getAttribute('depth');
			$this->summary = $details->getAttribute('element_count');
		}
		
		/**
		 * If categories are requested. Parse these as well
		 */
		$subCategories = $this->resultElement->getElementsByTagName('SubCategories')->item(0);
		if($subCategories)
		{
			foreach($subCategories->getElementsByTagName('SubCategory') as $subCategory)
			{
				$subCategoryDetails = array('categoryId'=>$subCategory->getAttribute('category_id'));
				$this->subCategoryList[] = $subCategoryDetails;
			}
		}
	}
}
?>