<?php
namespace Trinity\LibuBundle\Isbn\ISBNdb;

use Trinity\LibuBundle\Isbn\ISBNdb\ISBNDBPaginator;
use Trinity\LibuBundle\Isbn\ISBNdb\ISBNDBServiceAuthor; 
use Trinity\LibuBundle\Isbn\ISBNdb\ISBNDBServiceBook;

/**
 * This class creates an interface to the webservice found at http://www.isbndb.com/
 * and allows users to search for book related information. This is a low level class
 * that requires a basic knowledge of the API before it can be used. It is advised
 * to extends this class to create a more high level API. See examples.
 * 
 * @author Robert van der Linde <robert@linde002.nl>
 * @version 1.0
 * @package ISBNDBService
 * @example examples/ISBNDBService.php
 *
 */
class ISBNDBService
{	
	const API_AUTHORS 	 = 'http://isbndb.com/api/authors.xml';
	const API_BOOKS 	 = 'http://isbndb.com/api/books.xml';
	const API_CATEGORIES = 'http://isbndb.com/api/categories.xml';
	const API_PUBLISHERS = 'http://isbndb.com/api/publishers.xml';
	const API_SUBJECTS 	 = 'http://isbndb.com/api/subjects.xml';
	
	
	const RESULT_TYPE_AUTHORS 		= 'authors';
	const RESULT_TYPE_CATEGORIES 	= 'categories';
	const RESULT_TYPE_DETAILS 		= 'details';
	const RESULT_TYPE_MARC 			= 'marc';
	const RESULT_TYPE_PRICES 		= 'prices';
	const RESULT_TYPE_PRICEHISTORY 	= 'pricehistory';
	const RESULT_TYPE_SUBCATEGORIES = 'subcategories';
	const RESULT_TYPE_SUBJECTS 		= 'subjects';
	const RESULT_TYPE_STRUCTURE 	= 'structure';
	const RESULT_TYPE_TEXTS 		= 'texts';
	
	
	const SEARCH_TYPE_BOOK_ID 		= 'book_id';
	const SEARCH_TYPE_CATEGORY_ID	= 'category_id';
	const SEARCH_TYPE_COMBINED 		= 'combined';
	const SEARCH_TYPE_DEWEY_DECIMAL = 'dewey_decimal';
	const SEARCH_TYPE_FULL 			= 'full';
	const SEARCH_TYPE_ISBN 			= 'isbn';
	const SEARCH_TYPE_LCC_NUMBER 	= 'lcc_number';
	const SEARCH_TYPE_NAME 			= 'name';
	const SEARCH_TYPE_PARENT_ID 	= 'parent_id';
	const SEARCH_TYPE_PERSON_ID 	= 'person_id';
	const SEARCH_TYPE_PUBLISHER_ID 	= 'publisher_id';
	const SEARCH_TYPE_SUBJECT_ID 	= 'subject_id';
	const SEARCH_TYPE_TITLE 		= 'title';
	
	/**
	 * @var string The API access key
	 */
	private $accessKey = "5YW8PFOV";
	
	/**
	 * Constructs a new ISBNDBService instance
	 * @param string $accessKey The accesskey used to interact with the API
	 */
	public function __construct($accessKey="5YW8PFOV")
	{
		$this->accessKey = $accessKey;
	}
	
	/**
	 * Queries the ISBNDBService for information by specified search field & criteria
	 * @param string $api The API to use to query the information. @see constants API_*
	 * @param string $searchBy Search field. @see constants SEARCH_TYPE_*
	 * @param string $criteria The search criteria
	 * @param array $resultTypes The resulttypes to return. @see constants RESULT_TYPE_*
	 * @return ISBNDBPaginator the found results paginator
	 */
	public function search($api, $searchBy, $criteria, $resultTypes = array())
	{
		echo "Ejecutando search";
		return $this->doRequest($api, $searchBy, $criteria, $resultTypes);
	}
	
	
	
	
	/**
	 * Makes the request to the webservice
	 * @param string $apiURL This sets the API url to be used.
	 * @param string $searchType The type of search to make
	 * @param string $searchCriteria The search query
	 * @param array $resultTypes an array of resulttypes to return
	 * @return ISBNDBPaginator The found results paginator
	 */
	protected function doRequest($apiURL, $searchType, $searchCriteria, $resultTypes)
	{
		$queryString = $apiURL;
		$queryString .= '?access_key='.$this->accessKey;
		$queryString .= '&value1='.str_replace(' ', '+', $searchCriteria);
		if(count($resultTypes))
		{
			if (!is_array($resultTypes)) {
				$queryString .= '&results='.$resultTypes;
			} else {
				$queryString .= '&results='.implode('+', $resultTypes);
			}
		}
		$queryString .= '&index1='.$searchType;		
		$responseXML = file_get_contents($queryString);
		return $this->createISBNDBServicePaginator($apiURL, $responseXML, $queryString);
	}
	
	/**
	 * This creates the paginator used to walk through the results
	 * @return ISBNDBPaginator The found results paginator
	 */
	protected function createISBNDBServicePaginator($apiURL, $responseXML, $queryString)
	{
		switch($apiURL)
		{
			case self::API_AUTHORS:
				$resultType = 'Author';
				break;
			case self::API_BOOKS:
				$resultType = 'Book';
				break;
			case self::API_CATEGORIES:
				$resultType = 'Category';
				break;
			case self::API_PUBLISHERS:
				$resultType = 'Publisher';
				break;
			case self::API_SUBJECTS:
				$resultType = 'Subject';
				break;
		}
		return new ISBNDBPaginator($responseXML, $resultType, $queryString);
	}
}
?>