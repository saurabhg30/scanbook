<?php
include_once 'configuration.php';
include_once 'googleBooks.php';
class Database {

  public function __construct(){
    $this->conn = new mysqli(serverName, databaseUsername, databasePassword, databaseName);
    if($this->conn->connect_error)
      die('database connection error');
    else
      return $this->conn;
  }

  public function selectBookDetails($isbn){
    $selectBookDetails = "SELECT * FROM book_details WHERE isbn LIKE '".$isbn."';";
    $resultBook = mysqli_query($this->conn,$selectBookDetails);

    if ($resultBook->num_rows == 1) {
      $dataBook = mysqli_fetch_array($resultBook,MYSQLI_ASSOC);
      $title = $dataBook['title'];
      $pageCount = $dataBook['number_of_pages'];
      $book_id = $dataBook['book_id'];
      $readStatus = $dataBook['book_read'];
      $notes = $dataBook['notes'];

      $selectAuthorDetails = "SELECT * FROM author_details WHERE book_id LIKE '".$book_id."';";
      $resultAuthor = mysqli_query($this->conn,$selectAuthorDetails);

      $author = array();
      for($i=0;$i<($resultAuthor->num_rows);$i++){
        $dataAuthor = mysqli_fetch_array($resultAuthor, MYSQLI_ASSOC);
        array_push($author,$dataAuthor['author_name']);
      }
      return $this->responseBookDetails($book_id,$isbn,$title,$pageCount,$author,$readStatus,$notes);
    }
    else
      return $this->insertBookDetails($isbn);
  }

  public function updateBookDetails($bookId, $notes, $readStatus){
    $updateQuery = "UPDATE book_details SET book_read = '".$readStatus."',notes = '".$notes."' WHERE book_id = ".$bookId.";";
    $updateBook = mysqli_query($this->conn,$updateQuery);
    if(mysqli_affected_rows($this->conn) == 1){
      return 1;
    }
    else{
      return 0;
    }
  }

  private function insertBookDetails($isbn){
    $googleBooks = new GoogleBooks;
    $book_details = $googleBooks->fetchBookDetails($isbn);
    if($book_details != null){
      $title = $book_details['title'];
      $pageCount = $book_details['pageCount'];

      $insertBookDetails = "INSERT INTO book_details(title,isbn,number_of_pages) value('".$title."','".$isbn."', '".$pageCount."');";
      $insertResult = mysqli_query($this->conn,$insertBookDetails);
      $book_id = mysqli_insert_id($this->conn);

      if($book_id > 0){
        $author = array();
        foreach ($book_details['author'] as $key => $value) {
          $insertBookAuthor = "INSERT INTO author_details(book_id,author_name) value('".$book_id."','".$value."');";
          $insertResult = mysqli_query($this->conn,$insertBookAuthor);
          array_push($author,$value);
        }
        return $this->responseBookDetails($book_id,$isbn,$title,$pageCount,$author,0,null);
      }
    }
    return null;
  }

  private function responseBookDetails($bookId,$isbn,$title,$pageCount,$author,$readStatus, $notes){
    return array(
      'bookId'=>$bookId,
      'isbn'=>$isbn,
      'title'=>$title,
      'pageCount'=>$pageCount,
      'author'=>$author,
      'readStatus'=>$readStatus,
      'notes'=>$notes,
      );
  }
}
?>
