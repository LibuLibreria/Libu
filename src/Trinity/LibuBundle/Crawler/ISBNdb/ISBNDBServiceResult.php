<?php

namespace Trinity\LibuBundle\Crawler\ISBNdb;

/**
 * This is the base class for a ISBNDB webservice result. 
 * Subclasses need to implement the parseResponse() method to be able to parse a specific
 * response to their ResultType (i.e. Book needs to parse title, author etc)
 * Subclasses can then contain ResultType specific methods used to retrieve information
 * For examples
 * @see ISBNDBServiceAuthor
 * @author Robert van der Linde <robert@linde002.nl>
 * @version 1.0
 * @package ISBNDBService
 * @example examples/ISBNDBServiceResult.php
 * 
 */
abstract class ISBNDBServiceResult
{
	/**
	 * @var DOMElement
	 */
	protected $resultElement = null;
	
	/**
	 * Constructs a new IBSNDBServiceResult that contains information about a 
	 * single search result.
	 * @param DOMElement $resultElement The result element representing a result
	 */
	public function __construct ($resultElement)
	{
		$this->resultElement = $resultElement;
		$this->parseResponse();
	}
	
	/**
	 * Parses the result element
	 */
	protected abstract function parseResponse();
}
?>