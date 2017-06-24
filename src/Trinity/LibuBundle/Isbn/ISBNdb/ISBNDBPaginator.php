<?php

namespace Trinity\LibuBundle\Isbn\ISBNdb;
use \Iterator;
use \Countable;
use \DOMDocument;

/**
 * This class is an interface to the paging used by the ISBNDB webservice and
 * contains the ISBNDBCollection for the found results.
 * It is used as an iterator. Collections of results found on page are loaded
 * when accessed. The default page loaded is the first
 * 
 * @author Robert van der Linde <robert@linde002.nl>
 * @package ISBNDBService
 * @version 1.0
 * @example examples/ISBNDBPaginator.php
 */
class ISBNDBPaginator implements Iterator, Countable
{
	/**
	 * @var ISBNDBServiceCollection The collection from the current page
	 */
	protected $currentCollection = null;
	
	/**
	 * @var string The query string used to get a page
	 */
	protected $queryString = null;
	
	/**
	 * @var int The total number results found
	 */
	protected $totalResults = null;
	
	/**
	 * @var int The total number of pages
	 */
	protected $totalPages = null;
	
	/**
	 * @var int The current page id
	 */
	protected $currentPage = null;
	
	/**
	 * @var string The result type for the ISBNServiceCollection
	 */
	protected $resultType = null;
		
	/**
	 * @var string The response
	 */
	public $response = null;

	/**
	 * Creates a new instance of ISBNDBServicePaginator
	 * @param string $responseXML The response returned from the webservice
	 * @param string $resultType The result type for the ISBNServiceCollection
	 * @param string $queryString The querystring used to get the results
	 */
	public function __construct ($responseXML, $resultType, $queryString)
	{
		$this->response = new \DOMDocument();
		$this->response->loadXML($responseXML);		
		$this->resultType= $resultType;
		$this->queryString = $queryString;
		$this->parsePagination();
	}
	
	/**
	 * This sets up all information needed to make the paginator work
	 */
	protected function parsePagination()
	{
		$resultList = $this->response->getElementsByTagName($this->resultType.'List')->item(0);
		$this->totalResults = $resultList->getAttribute('total_results');
		$this->currentPage = 1;
		$this->totalPages = floor($this->totalResults/$resultList->getAttribute('page_size'));
		if($this->totalResults%$resultList->getAttribute('page_size'))
		{
			$this->totalPages = $this->totalPages+1;
		}
	}
	
	/**
	 * This sets the current page and loads the associated collection. Allows for lazy loading
	 * @param int $pageNumber The current page number to retrieve
	 */
	protected function setCurrentResultPage($pageNumber)
	{
		if((int)$pageNumber < 1)
		{
			$pageNumber = 1;
		}
		$queryString = $this->queryString.'&page_number='.$pageNumber;
		$responseXML = file_get_contents($queryString);
		$this->currentCollection = new ISBNDBServiceCollection($responseXML, $this->resultType);
	}
	
	/**
	 * Iterator function
	 * @see reset()
	 */
	public function rewind()
	{
		$this->setCurrentResultPage(1);
	}
	
	/**
	 * Iterator function
	 * @see current()
	 * @return ISBNDBServiceCollection or false
	 */
	public function current()
	{
		if($this->currentPage <= $this->totalPages)
		{
			return $this->currentCollection;
		}
		else 
		{
			return false;
		}
	}
	
	/**
	 * Iterator function
	 * @return int the current page
	 */
	public function key()
	{
		return key($this->currentPage);
	}
	
	/**
	 * Iterator function
	 * @see next()
	 * @retur ISBNDBServiceCollection
	 */
	public function next()
	{
		$this->currentPage = $this->currentPage+1;
		$this->setCurrentResultPage($this->currentPage);
		return $this->currentCollection;
	}
	
	/**
	 * Iterator function
	 * @see current()
	 * @return bool
	 */
	public function valid()
	{
		return ($this->currentPage <= $this->totalPages);
	}
	
	/**
	 * Counts the number of found pages
	 * @see count()
	 * @return int
	 */
	public function count()
	{
		return $this->totalPages;
	}
	
	/**
	 * Returns the ISBNDBServiceCollection at the given index
	 * @param int $index The index to retrieve the collection from
	 * @return ISBNDBServiceCollection 
	 */
	public function get($index)
	{
		$this->setCurrentResultPage($index);
		return $this->currentCollection;
	}
}
?>