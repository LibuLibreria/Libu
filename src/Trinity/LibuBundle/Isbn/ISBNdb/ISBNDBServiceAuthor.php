<?php
namespace Trinity\LibuBundle\Isbn\ISBNdb;


/**
 * This class represents an Author result from the ISBNDB webservice
 * It allows access to all properties that can be found in a result
 * 
 * @author Robert van der Linde <robert@linde002.nl>
 * @version 1.0
 * @package ISBNDBService
 * @example examples/ISBNDBResult
 *
 */
class ISBNDBServiceAuthor extends ISBNDBServiceResult 
{
	/**
	 * @var string The person_id
	 */
	public $personId = null;
	
	/**
	 * @var string The author's fullname
	 */
	public $name = null;
	
	/**
	 * @var string The author's first name
	 */
	public $firstName = null;
	
	/**
	 * @var string The author's last name
	 */
	public $lastName = null;
	
	/**
	 * @var string The author's lifespan (i.e. '1960-2005' or '1960-') 
	 */
	public $dates = null;
	
	/**
	 * @var string If the author has any books in the ISBNDB database
	 */
	public $hasBooks = null;
	
	/**
	 * @var array The list of categories this author appears in.
	 * An array of associated arrays in the form of:
	 * <code>
	 * array(
	 *  [0] => array(
	 *           'categoryId'=>'romantic.novels.space',
	 *           'categoryName'=>'Romantic space novels'
	 *         )
	 * )
	 * </code>
	 */
	public $categoryList = array();
	
	/**
	 * @var array The list of subjects this author appears in
     * An array of associated arrays in the form of:
     * <code>
	 * array(
	 *  [0] => array(
	 *           'subjectId'=>'space.love',
	 *           'bookCount'=>'2',
	 *           'subjectName'=>'Space love'
	 *         )
	 * )
	 * </code>
	 */
	public $subjects = array();
	
	/**
	 * Constructs a new ISBNDBServiceAuthor that contains information about a 
	 * single search result.
	 * @param DOMElement $authorData The authorData element representing a book
	 */
	public function __construct ($authorData)
	{
		parent::__construct($authorData);
	}
	
	/**
	 * Parses the $authorData element
	 */
	protected function parseResponse()
	{
		$this->personId = $this->resultElement->getAttribute('person_id');
		$this->name = $this->resultElement->getElementsByTagName('Name')->item(0)->nodeValue;
		
		/**
		 * If details are requested. Parse these as well
		 */
		$details = $this->resultElement->getElementsByTagName('Details')->item(0);
		if($details)
		{
			$this->firstName = $details->getAttribute('first_name');
			$this->lastName = $details->getAttribute('last_name');
			$this->dates = $details->getAttribute('dates');
			$this->hasBooks = $details->getAttribute('has_books');
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
		
		/**
		 * If subjects are requested. Parse these as well
		 */
		$subjects = $this->resultElement->getElementsByTagName('Subjects')->item(0);
		if($subjects)
		{
			foreach($subjects->getElementsByTagName('Subject') as $subject)
			{
				$subjectDetails = array('subjectId'=>$subject->getAttribute('subject_id'),
										'bookCount'=>$subject->getAttribute('book_count'),
										'subjectName'=>$subject->nodeValue);
				$this->subjects[] = $subjectDetails;
			}
		}
	}
}
?>