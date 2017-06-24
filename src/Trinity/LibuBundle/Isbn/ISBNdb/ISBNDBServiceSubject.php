<?php
class ISBNDBServiceSubject extends ISBNDBServiceResult 
{

	/**
	 * @var string
	 */
	public $subjectId = null;
	
	/**
	 * @var string
	 */
	public $bookCount = null;
	
	/**
	 * @var string
	 */
	public $marcField = null;
	
	/**
	 * @var string
	 */
	public $marcIndicator = null;
	
	/**
	 * @var string
	 */
	public $marcIndicator2 = null;
	
	/**
	 * @var string
	 */
	public $subjectName = null;
	
	/**
	 * @var array
	 */
	public $categoryList = array();
	
	/**
	 * @var array
	 */
	public $subjectStructure = array();
	
	/**
	 * Constructs a new ISBNDBServiceSubject that contains information about a 
	 * single search result.
	 * @param DOMElement $subjectData The authorData element representing a book
	 */
	public function __construct ($subjectData)
	{
		parent::__construct($subjectData);
	}
	
	/**
	 * Parses the $subjectData element
	 */
	protected function parseResponse()
	{
		$this->subjectId = $this->resultElement->getAttribute('subject_id');
		$this->bookCount = $this->resultElement->getAttribute('book_count');
		$this->marcField = $this->resultElement->getAttribute('marc_field');
		$this->marcIndicator = $this->resultElement->getAttribute('marc_indicator');
		$this->marcIndicator2 = $this->resultElement->getAttribute('marc_indicator_2');
		$this->subjectName = $this->resultElement->nodeValue;
		
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
		
		$structure = $this->resultElement->getElementsByTagName('SubjectStructure')->item(0);
		if($structure)
		{
			foreach($structure->getElementsByTagName('SubjectElement') as $subjectElement)
			{
				$subjectDetails = array('text' 			=> $subjectElement->getAttribute('text'),
										'type' 			=> $subjectElement->getAttribute('type'),
										'marcSubfield' 	=> $subjectElement->getAttribute('marc_subfield'));
				$this->subjectStructure[] = $subjectDetails;
			}
		}
	}
}
?>