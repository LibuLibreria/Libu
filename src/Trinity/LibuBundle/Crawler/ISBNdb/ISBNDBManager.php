<?php

namespace Trinity\LibuBundle\Crawler\ISBNdb;

use Trinity\LibuBundle\Crawler\ISBNdb\ISBNDBService;
use Trinity\LibuBundle\Crawler\ISBNdb\ISBNDBServiceBook;


class ISBNDBManager
{

    public function buscarISBNdb($isbn)
    {
        $serv = new ISBNDBService();
        $result = $serv->search('http://isbndb.com/api/books.xml', 'isbn', $isbn);

        $datoslibro = $result->response->getElementsByTagName('BookData');
        if ($datoslibro->length > 0) {
            foreach ($datoslibro as $item) {
                $res = new ISBNDBServiceBook($item);
            } 
        } else {
                $res = false;     
        }

        return $res; 
    }
}
