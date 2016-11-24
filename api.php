<?php
include_once 'database.php';

header('content-type: application/json');

class API {
  public function __construct() {
    switch ($_SERVER['REQUEST_METHOD']) {
      case 'POST':
        $this->methodCall($_POST);
        break;

      case 'GET':
        $this->methodCall($_GET);
        break;

      default:
        echo $this->failure('error');
        break;
    }
  }

  private function methodCall($data){
    $func = strtolower(trim(str_replace("/", "", $data['request'])));
        if ((int) method_exists($this, $func) > 0)
            $this->$func($data);
        else
            echo $this->failure('Failed');
  }

  private function bookDetails($data){
    $isbn = $data['isbn'];
    $database = new Database;
    $data = $database->selectBookDetails($isbn);
    if($data != null){
      echo $this->success($data,'success');
    }
    else{
      echo $this->failure('invalid ISBN');
    }
  }

  private function updateBookDetails($data){
    $bookId = $data['bookId'];
    $notes  = $data['notes'];
    $readStatus = $data['readStatus'];
    $database = new Database;
    $data = $database->updateBookDetails($bookId,$notes, $readStatus);
    if($data == 1){
      echo $this->success(array('notes'=>$notes, 'readStatus' => $readStatus),'Book details updated');
    }
    else{
      echo $this->failure('Failed to update');
    }
  }

  private function success($data,$message){
    $successMessage = array_merge(array(
                          'status'  => 200,
                          'message' => $message,),
                          $data
                        );
    return json_encode($successMessage);
  }

  private function failure($message){
    $failureMessage = array(
                        'status'  => 400,
                        'message' => $message,
                      );
    return json_encode($failureMessage);
  }
}
$api = new API;
