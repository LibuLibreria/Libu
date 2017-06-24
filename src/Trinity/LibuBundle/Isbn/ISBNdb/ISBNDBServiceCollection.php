<?php
/**
 * This class represents a collection of found results from the ISBNDB webservice
 * It works as an iterator to loop through all results
 * 
 * @author Robert van der Linde <robert@linde002.nl>
 * @package ISBNDBService
 * @version 1.0
 * @example examples/ISBNDBServiceCollection.php
 */
class ISBNDBServiceCollection implements Iterator, Countable
{
	/**
	 * @var DOMDocument The response from the ISBNDB Service
	 */
	protected $response;
	
	/**
	 * @var array The found results
	 */
	protected $results = array();
	
	/**
	 * @var string The result type for this collection. i.e. 'Author', 'Book', etc.
	 */
	protected $resultType = null;
	
	/**
	 * Constructs a new ISBNDBServiceCollection
	 * @param string The XML response received from the webservice
	 * @param string The resulttype given.
	 */
	public function __construct ($responseXML, $resultType)
	{
		$this->response = DOMDocument::loadXML($responseXML);
		$this->resultType = $resultType;
		$this->parseResponse();	
	}

	
	/**
	 * Parses the response into a collection of search results
	 */
	protected function parseResponse()
	{
		$this->results = array();
		$className = 'ISBNDBService'.$this->resultType;
		foreach($this->response->getElementsByTagName($this->resultType.'Data') as $resultElement)
		{
			$result = new $className($resultElement);
			$this->results[] = $result; 
		}
	}
	
	/**
	 * Iterator function
	 * @see reset()
	 */
	public function rewind()
	{
		reset($this->results);
	}
	
	/**
	 * Iterator function
	 * @see current()
	 */
	public function current()
	{
		return current($this->results);
	}
	
	/**
	 * Iterator function
	 * @see key()
	 */
	public function key()
	{
		return key($this->results);
	}
	
	/**
	 * Iterator function
	 * @see next()
	 */
	public function next()
	{
		return next($this->results);
	}
	
	/**
	 * Iterator function
	 * @see current()
	 */
	public function valid()
	{
		return ($this->current() !== false);
	}
	
	/**
	 * Counts the number of found results
	 * @see count()
	 */
	public function count()
	{
		return count($this->results);
	}
	
	/**
	 * Returns the ISBNDBServiceResult at the given index
	 * @param int $index The index to retrieve the result from
	 * @return ISBNDBServiceResult 
	 */
	public function get($index)
	{
		return $this->results[$index];
	}
}
?>