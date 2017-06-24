<?php

namespace Trinity\LibuBundle\Isbn\ISBNdb;


class ISBNDBServiceBook extends ISBNDBServiceResult
{
	/**
	 * @var string
	 */
	public $title = null;
	/**
	 * @var string
	 */
	public $titleLong = null;
	/**
	 * @var string
	 */
	public $authorsText = null;
	/**
	 * @var array
	 */
	public $authorsList = array();
	/**
	 * @var string
	 */
	public $publisherText = null;
	/**
	 * @var string
	 */
	public $summary = null;
	/**
	 * @var string
	 */
	public $notes = null;
	/**
	 * @var string
	 */
	public $urlsText = null;
	/**
	 * @var string
	 */
	public $awardsText = null;
	/**
	 * @var string
	 */
	public $isbn = null;
	/**
	 * @var string
	 */
	public $isbn13 = null;
	
	/**
	 * @var array
	 */
	public $pricesList = array();
	
	/**
	 * @var array
	 */
	public $subjectList = array();
	
	/**
	 * @var array
	 */
	public $marcList = array();
	
	/**
	 * Constructs a new ISBNDBServiceBook that contains information about a 
	 * single search result.
	 * @param DOMElement $bookData The BookData element representing a book
	 */
	public function __construct ($bookData)
	{
		parent::__construct($bookData);	
	}
	
	/**
	 * Returns the list of authors a string
	 * @param string $seperator The string to separate the authors with
	 * @return string A list of authors as a string
	 */
	public function getAuthorsListAsString($separator = ',')
	{
		if(empty($this->authorsList))
		{
			if(strpos($this->authorsText, ',') == strlen($this->authorsText)-2)
				return substr($this->authorsText, 0, strlen($this->authorsText)-2);
			else
				return strpos($this->authorsText, ',')."-".strlen($this->authorsText);
		}
		$authors = array();
		foreach($this->authorsList as $author)
		{
			$authors[] = $author['personName'];
		}
		return implode($separator, $authors); 
	}
	
	/**
	 * Parses the BookData element
	 */
	protected function parseResponse()
	{
		$bookTags = array('title', 'titleLong', 'authorsText', 'publisherText', 'summary', 'notes', 'urlsText', 'awardsText');
		foreach($bookTags as $tag)
		{
			$item = $this->resultElement->getElementsByTagName(ucfirst($tag))->item(0);
			if($item)
			{
				$this->$tag = $item->nodeValue;
			}	
		}
		$this->isbn = $this->resultElement->getAttribute('isbn');
		$this->isbn13 = $this->resultElement->getAttribute('isbn13');
		$authorList = $this->resultElement->getElementsByTagName('Authors')->item(0);
		if($authorList)
		{
			foreach($authorList->getElementsByTagName('Person') as $person)
			{
				$authorDetails = array('personId'	=> $person->getAttribute('person_id'),
									   'personName' => $person->nodeValue);
				$this->authorsList[] = $authorDetails;
			}
		}
		$pricesList = $this->resultElement->getElementsByTagName('Prices')->item(0);
		if($pricesList)
		{
			foreach($pricesList->getElementsByTagName('Price') as $price)
			{
				$priceDetails = array('storeISBN'	=> $price->getAttribute('store_isbn'),
									  'storeTitle'	=> $price->getAttribute('store_title'),
									  'storeURL'	=> $price->getAttribute('store_url'),
									  'storeId'		=> $price->getAttribute('store_id'),
									  'currencyCode'=> $price->getAttribute('currency_code'),
									  'isInStock'	=> $price->getAttribute('is_in_stock'),
									  'isNew'		=> $price->getAttribute('is_new'),
									  'checkTime'	=> $price->getAttribute('check_time'),
									  'currencyRate'=> $price->getAttribute('currency_rate'),
									  'price'		=> $price->getAttribute('price'));
				$this->pricesList [] = $priceDetails;
			}
		}
		$subjectList = $this->resultElement->getElementsByTagName('Subjects')->item(0);
		if($subjectList)
		{
			foreach($subjectList->getElementsByTagName('Subject') as $subject)
			{
				$subjectDetails = array('subjectId'	=> $subject->getAttribute('subject_id'),
									  	'subjectName'	=> $subject->nodeValue);
				$this->subjectList [] = $subjectDetails;
			}
		}
		$marcList = $this->resultElement->getElementsByTagName('MARCRecords')->item(0);
		if($marcList)
		{
			foreach($marcList->getElementsByTagName('MARC') as $marc)
			{
				$marcDetails = array('library'		=> $marc->getAttribute('library_name'),
									 'lastUpdate'	=> $marc->getAttribute('last_update'),
									 'marcURL'		=> $marc->getAttribute('marc_url'));
				$this->marcList [] = $marcDetails;
			}
		}
	}
}
?>