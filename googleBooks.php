<?php
class GoogleBooks{
  public function fetchBookDetails($isbn){
    $json_file = file_get_contents('https://www.googleapis.com/books/v1/volumes?q=isbn:'.$isbn.'');
    $json = json_decode($json_file,true);
    if($json['totalItems'] > 0){
      $title = $json['items'][0]['volumeInfo']['title'];
      $author = $json['items'][0]['volumeInfo']['authors'];
      $page_count = $json['items'][0]['volumeInfo']['pageCount'];
      return array(
        'title'     => $title,
        'author'    => $author,
        'pageCount' => $page_count
      );
    }
  else {
    return null;
    }
  }
}

?>
