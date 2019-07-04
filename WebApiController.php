<?php
namespace MVC\Controllers;
use MVC\Models\UsersModel;
use MVC\Models\PersonModel;
use MVC\Models\PersonImagesModel;
use MVC\Models\PostsModel;
use MVC\Models\NotificationsModel;
use MVC\Libs\MyAPI;


class WebApiController extends AbstractController
{
    private $response = array();
    private $userId;

    public function __construct()
    {
        // if($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'GET' || $_SERVER['REQUEST_METHOD'] == 'DELETE')
        // {
        //     $this->userId = $_POST['userId'];
        // }
    }    
##########################################################################
    public function createUserAction()
    {        
        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            if(!empty($_POST['username']) && !empty($_POST['password']) && !empty($_POST['email']))
            {
                $user = new UsersModel();
                $user->username = $_POST['username'];
                $user->password = $_POST['password'];
                $user->email = $_POST['email'];
                $user->city = $_POST['city'];
                $user->number = $_POST['number'];
                $user->img = 'default-profile.jpg';
                if($user->create())
                {
                    $response['error'] = false;
                    $response['msg'] = 'user registered successfully !';
                    $response['id'] = $user->lastInsertedId(); 
                    $response['username'] = $user->username; 
                    $response['email'] = $user->email;                  
                }
                else
                {
                    $response['error'] = true;
                    $response['msg'] = 'some error occured try again later!';                    
                }
            }
            else
            {
                $response['error'] = true;
                $response['msg'] = 'Required fields missed';   
            }
        }
        else
        {
            $response['error'] = true;
            $response['msg'] = 'Invalid Request';   
        }
        echo json_encode($response);
    }
##########################################################################
public function loginAction()
  {
    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
      if(!empty($_POST['username']) && !empty($_POST['password']))
      {
        $this->username = $_POST['username'];
        $this->password = $_POST['password'];

        $user = new UsersModel();
        $userData = $user->getByName($this->username);

        if($userData)
        {
            if($userData['password'] == $this->password)
            {
                $response['error'] = false;
                $response['msg'] = 'user logged successfully !'; 
                $response['id'] = $userData['id']; 
                $response['username'] = $userData['username']; 
                $response['email'] = $userData['email'];                  
            }
            else
            {
                $response['error'] = true;
                $response['msg'] = 'password invalid try again!';          
            }
        }
        else
        {
            $response['error'] = true;
            $response['msg'] = 'invalid username!';
        }
      }
      else
      {
        $response['error'] = true;
        $response['msg'] = 'empty fileds!';
      }
    }
    else
    {
        $response['error'] = true;
        $response['msg'] = 'wrong request!';
    }
    echo json_encode($response);

}
##########################################################################
public function getPostsAction()
  {
    $result = array();
    if($_SERVER['REQUEST_METHOD'] == 'GET')
    {    
        $posts = new PostsModel();
        $posts = $posts->getAllPosts();

        if($posts == false)
        {
            $response['error'] = true;
            $response['msg'] = 'No Connection ##Some error occured try again later!';          
        }
        else
        {          
            $response['error'] = false;

            foreach($posts as $postIndex => $post){
                $post['img1'] = ROOT_URL . "uploads/" . $post['img1'];     ## To get the full path to image
                $post['img'] = ROOT_URL . "uploads/" . $post['img'];
                array_push($result,$post);                
            }            
            $response['posts'] = $result;
        }
    }
    else
    {
        $response['error'] = true;
        $response['msg'] = 'wrong request!';
    }
    echo json_encode($response);

}
###############################################################
public function getNotificationsAction()
  {
    $result = array();
    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $userId = $_POST['userId'];
        $notificationObj = new NotificationsModel();
        $notifications = $notificationObj->getAllByUserId($userId);

        if(empty($notifications))
        {
            $response['error'] = true;
            $response['msg'] = 'NoExistNotifications';          
        }
        else
        {          
            $response['error'] = false;
            foreach($notifications as $notificationRow => $notification){
                // $notification['img1'] = ROOT_URL . "uploads/" . $post['img1'];            
                array_push($result,$notification);
            }            
            $response['notifications'] = $result;
        }
    }
    else
    {
        $response['error'] = true;
        $response['msg'] = 'wrong request!';
    }
    echo json_encode($response);

}
###############################################################
public function getMatchingAction()
  {
    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
      //Generic
      $notification = new NotificationsModel();
      $notificationArr = $notification->getByPK($_POST['notifyId']);
      $uploadedPersonObj     = new PersonModel();
      $uploadedPersonArr  = $uploadedPersonObj->getByPK($notificationArr['personId']); //Get The Uploaded Person To Use it to get The Identfied       
      
      //Identified Person Sec
      $identifiedPerson    = new PersonModel();
      $identifiedPersonArr = $identifiedPerson->getByPK($uploadedPersonArr['identifiedId']);
      $identifiedPersonArr = $identifiedPerson->getPersonWithImg($identifiedPersonArr['id'],$identifiedPersonArr['userId']);
      $identifiedPersonArr['imgName'] = ROOT_URL . "uploads/" . $identifiedPersonArr['imgName'];
      $identifiedPersonArr['img'] = ROOT_URL . "uploads/" . $identifiedPersonArr['img'];
      $response['error'] = false;
      $response['msg'] = 'returned successfully';
      $response['identifiedPerson'] = $identifiedPersonArr;
    }
    else
    {
        $response['error'] = true;
        $response['msg'] = 'wrong request!';
    }
    echo json_encode($response);

}
//////////////////\
//               //
//               //
//               //
///////////////////
//   Real Job    //
//   Goes Here   //
///////////////////
//               // 
//               //
//               //
//////////////////\

public function addPersonAction()
  {
      if($_SERVER['REQUEST_METHOD'] == 'POST'){

        if(!empty($_POST['image'])){
          if($_POST['group'] === 'Lost'){
            $this->addLostAction();           
            echo json_encode($this->response);
            return;       
          }elseif($_POST['group'] === 'Found'){
            $this->addFoundAction();
            echo json_encode($this->response);
            return;
          }else{
            $this->response['error'] = true;
            $this->response['msg'] = "Failed, Person Group is Required";
            echo json_encode($this->response);
            return;
          }
        }else{
          $this->response['error'] = true;
          $this->response['msg'] = "Failed, Image is Required";
          echo json_encode($this->response);
          return;
        }
      }else{
        $this->response['error'] = true;
        $this->response['msg'] = "Invalid Request";
        echo json_encode($this->response);
        return;
    }    
  }
###############################################
  public function addLostAction()
  {          
      //// IMAGE Upload details

      ## Generating a name for image
      $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';      
      ###

      $currentDir = getcwd();
      $fileName   = substr(str_shuffle($permitted_chars), 0, 10);
      // $fileTmpName  = $_FILES['myfile']['tmp_name'];

      $uploadPath = $currentDir . UPLOAD_DIR . $fileName . ".jpg" ;
          ### END Img Details

      if(!file_put_contents($uploadPath, base64_decode($_POST['image']),1))
      {
        $this->response['error'] = true;
        $this->response['msg'] = "Error occured while uploading image";
        // $this->_data['msg'] = $this->response['msg'];
        // echo $this->response['msg'];
        return;
      }
      else
      {      
        $detectResponse = MyAPI::detectImage($uploadPath);
        if($detectResponse=='NoInternet')
        {
          $this->response['error'] = true;
          $this->response['msg'] = "Check Internet Connection";          
          return;
        }elseif(!$detectResponse){
          $this->response['error'] = true;
          $this->response['msg'] = "Error in detecting face";
          return;
        }
        else
        {
          $identifyResponse = MyAPI::identifyImage($detectResponse,FOUND_PERSONS_GROUP); ### return identification response

          // Error in identification request //
          if($identifyResponse=='NoInternet'){
            $this->response['error'] = true;
            $this->response['msg'] = "Check Internet Connection";
            return;
          }          
          else if(array_key_exists("error",$identifyResponse))
          {
            // var_dump($identifyResponse);
            $this->response['error'] = true;
            $this->response['msg'] = $identifyResponse['error'];
            if($identifyResponse['code'] != "PersonGroupNotTrained"){
              return;
            }
            $this->createLostPersonModel(false,$uploadPath,null,$fileName);
            return;
          }
          // request has done successfully //
          else  
          {           
            if(!array_key_exists("personId",$identifyResponse)) ## no matching
            {
                $this->createLostPersonModel(false,$uploadPath,null,$fileName);
                return;
            }
            else      ## there is matching
            { 
                $identifiedPerson = new PersonModel(); // to get identifiedPerson by its api id
                $identifiedPerson = $identifiedPerson->getByApiId($identifyResponse['personId']);                         
                $this->createLostPersonModel(true,$uploadPath,$identifiedPerson['id'],$fileName);  
                return;                                      
            }
          }          
        }
      }
  }

######################################################
  public function createLostPersonModel($i_result,$img,$identifiedPersonId=null,$fileName)
  {
      // $this->userId               = $_POST["userId"];
      $newPerson                  = new PersonModel();
      $newPerson->groupId         = LOST_PERSONS_GROUP;
      $newPerson->firstName       = $_POST['firstname'];
      $newPerson->fatherName      = $_POST['fathername'];
      $newPerson->lastName        = $_POST['lastname'];
      $newPerson->city            = $_POST['city'];
      $newPerson->age             = $_POST['age'];
      $newPerson->gender          = $_POST['gender'];        
      $newPerson->fullName        = $_POST['firstname'] . ' ' . $_POST['fathername'] . ' ' . $_POST['lastname'];
      $newPerson->personData      = $_POST['personData'];
      $newPerson->userId          = $_POST["userId"];

      ///// Post PersonGroup.Person to THE API
      if($i_result == false)
      {
          $newPerson->identified      = false;
          $newPerson->identifiedId    = null;

          $newPerson->apiId = MyAPI::createPerson($newPerson,LOST_PERSONS_GROUP);
          if(!$newPerson->apiId)
          {
              $this->response['error'] = true;
              $this->response['msg'] = "Error in creating person model";                
              // $this->_data['msg'] = $this->response['msg'];
              // echo $this->response['msg'];
              return;
          }else
          {
              $imgReponse = MyAPI::createPersonImage($newPerson->apiId,LOST_PERSONS_GROUP,$img);

              if(array_key_exists("error",$imgReponse)){
                  $this->response['error'] = true;
                  $this->response['msg'] = $imgReponse['error'];

                  if(MyAPI::deletePerson($newPerson->apiId,LOST_PERSONS_GROUP)){                        
                      $this->response['msg'] .= " ,Person Deleted";
                  }else{
                      $this->response['msg'] .= " ,Person Not Deleted";
                  }
                  $this->_data['msg'] = $this->response['msg'];
                  return;
              }else{
                  $newPersonId = $newPerson->create(); // create lost person in its group                        
                  $imgModel = new PersonImagesModel();
                  $imgModel->imgName = $fileName . ".jpg" ;
                  $imgModel->imgFaceId = $imgReponse['faceId'];
                  $imgModel->update($newPersonId);

                  $this->createPostAction($newPerson,$imgModel); // create post

                  $notify = new NotificationsModel();
                  $notify->message = "Report submittied successfully";
                  $notify->time = date("M,d,Y h:i:s A");
                  $notify->seen  = false;
                  $notify->type  = 'reporting';
                  $notify->userId  = $_POST['userId'];
                  $notify->personId  = $newPersonId;
                  $notify->create();  // create notification


                  $trainStatus = MyAPI::trainGroupAction(LOST_PERSONS_GROUP);  // train group

                  if($trainStatus){
                      $this->response['error'] = false;
                      $this->response['msg'] = "Posted Successfully";
                  }else{
                      $this->response['error'] = false;
                      $this->response['msg'] = "Data inserted , Error in Training";
                  }
                  // $this->_data['msg'] = $this->response['msg'];
                  return /*$this->response['msg'];*/;
                  
              }
          }                       
                                                                                        
      }
      else
      {
          $newPerson->identified      = true;
          $newPerson->identifiedId    = $identifiedPersonId;
          $newPerson->apiId           = null;
          $newPersonId = $newPerson->create();
          

          $imgModel = new PersonImagesModel();
          $imgModel->imgName = $fileName . ".jpg" ;
          $imgModel->imgFaceId = null;
          $imgModel->update($newPersonId); // update image table    

          $notify = new NotificationsModel(); ## Create Notification for the new user
          $notify->message = "Good News , Matching Occured!";
          $notify->time = date("M,d,Y h:i:s A");                   
          $notify->seen  = false;
          $notify->type  = 'matching';
          $notify->userId  = $_POST['userId'];
          $notify->personId  = $newPersonId;
          $notify->create();

          $identifiedPersonObj = new PersonModel();
          $identifiedPerson    = $identifiedPersonObj->getByPK($identifiedPersonId);          
          $identifiedPersonObj->updateToIdentified($newPersonId,$identifiedPerson['id']); // update the identified person as well to be matched

          $userOfIdentifiedPerson = new UsersModel();
          $userOfIdentifiedPerson = $userOfIdentifiedPerson->getByPK($identifiedPerson['userId']);
          
          $notify->userId   = $identifiedPerson['userId']; // change only personId to the
          $notify->personId =  $identifiedPersonId;
          $notify->create();
          
          $this->response['error'] = false;
          $this->response['msg'] = "Matching"; 
      }
      return;
  }
######################################################
  public function addFoundAction()
  {          
      //// IMAGE Upload details

      ## Generating a name for image
      $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';      
      ###

      $currentDir = getcwd();
      $fileName   = substr(str_shuffle($permitted_chars), 0, 10);
      // $fileTmpName  = $_FILES['myfile']['tmp_name'];

      $uploadPath = $currentDir . UPLOAD_DIR . $fileName;
          ### END Img Details

      if(!file_put_contents($uploadPath, base64_decode($_POST['image']), 1))
      {
        $this->response['error'] = true;
        $this->response['msg'] = "Error occured while uploading image";
        return;
      }else{
        $detectResponse = MyAPI::detectImage($uploadPath);
        if($detectResponse=='NoInternet')
        {
          $this->response['error'] = true;
          $this->response['msg'] = "Check Internet Connection";          
          return;
        }elseif(!$detectResponse){
          $this->response['error'] = true;
          $this->response['msg'] = "Error in detecting face";
          return;          
        }
        else
        {
          $identifyResponse = MyAPI::identifyImage($detectResponse,LOST_PERSONS_GROUP); ### return identification response

          // Error in identification request //
          if($identifyResponse=='NoInternet'){
            $this->response['error'] = true;
            $this->response['msg'] = "Check Internet Connection";
            return;
          }          
          else if(array_key_exists("error",$identifyResponse))
          {
            // var_dump($identifyResponse);
            $this->response['error'] = true;
            $this->response['msg'] = $identifyResponse['error'];
            if($identifyResponse['code'] != "PersonGroupNotTrained"){
              return;
            }
            $this->createFoundPersonModel(false,$uploadPath,$fileName);
            return;
          }
          // request has done successfully //
          else  
          {           
            if(!array_key_exists("personId",$identifyResponse)) ## no matching
            {
                $this->createFoundPersonModel(false,$uploadPath,$fileName);
                return;
            }
            else      ## there is matching
            { 
                $identifiedPerson = new PersonModel(); // to get identifiedPerson by its api id
                $identifiedPerson = $identifiedPerson->getByApiId($identifyResponse['personId']);                         
                $this->createFoundPersonModel(true,$uploadPath,$identifiedPerson['id'],$fileName);  
                return;
            }
          }          
        }   
      }      
  }

######################################################
  public function createFoundPersonModel($i_result,$img,$identifiedPersonId=null,$fileName)
  {
      // $this->userId               = $_POST["userId"];
      $newPerson                  = new PersonModel();
      $newPerson->groupId         = FOUND_PERSONS_GROUP;
      $newPerson->firstName       = $_POST['firstname'];
      $newPerson->fatherName      = $_POST['fathername'];
      $newPerson->lastName        = $_POST['lastname'];
      $newPerson->city            = $_POST['city'];
      $newPerson->age             = $_POST['age'];
      $newPerson->gender          = $_POST['gender'];        
      $newPerson->fullName        = $_POST['firstname'] . ' ' . $_POST['fathername'] . ' ' . $_POST['lastname'];
      $newPerson->personData      = $_POST['personData'];
      $newPerson->userId          = $_POST["userId"];

      ///// Post PersonGroup.Person to THE API
      if($i_result == false)
      {
          $newPerson->identified      = false;
          $newPerson->identifiedId    = null;

          $newPerson->apiId = MyAPI::createPerson($newPerson,FOUND_PERSONS_GROUP);
          if(!$newPerson->apiId)
          {
              $this->response['error'] = true;
              $this->response['msg'] = "Error in creating person model";                
              // $this->_data['msg'] = $this->response['msg'];
              // echo $this->response['msg'];
              return;
          }else
          {
              $imgReponse = MyAPI::createPersonImage($newPerson->apiId,FOUND_PERSONS_GROUP,$img);

              if(array_key_exists("error",$imgReponse)){
                $this->response['error'] = true;
                $this->response['msg'] = $imgReponse['error'];

                if(MyAPI::deletePerson($newPerson->apiId,FOUND_PERSONS_GROUP)){                        
                    $this->response['msg'] .= " ,Person Deleted";
                }else{
                    $this->response['msg'] .= " ,Person Not Deleted";
                }
                $this->_data['msg'] = $this->response['msg'];
                return;
              }else{
                  $newPersonId = $newPerson->create(); // create lost person in its group                        
                  $imgModel = new PersonImagesModel();
                  $imgModel->imgName = $fileName . ".jpg" ;;
                  $imgModel->imgFaceId = $imgReponse['faceId'];
                  $imgModel->update($newPersonId);

                  // $this->createPostAction($newPerson,$imgModel); // create post

                  $notify = new NotificationsModel();
                  $notify->message = "Report submittied successfully";
                  $notify->time = date("M,d,Y h:i:s A");
                  $notify->seen  = false;
                  $notify->type  = 'reporting';
                  $notify->userId  = $_POST["userId"];
                  $notify->personId  = $newPersonId;
                  $notify->create();  // create notification

                  $trainStatus = MyAPI::trainGroupAction(FOUND_PERSONS_GROUP);  // train group

                  if($trainStatus){
                      $this->response['error'] = false;
                      $this->response['msg'] = "Posted Successfully";
                  }else{
                      $this->response['error'] = false;
                      $this->response['msg'] = "Data inserted , Error in Training";
                  }
                  // $this->_data['msg'] = $this->response['msg'];
                  // echo $this->response['msg'];
                  return;
              }
          }                       
                                                                                        
      }
      else
      {
          $newPerson->identified      = true;
          $newPerson->identifiedId    = $identifiedPersonId;
          $newPerson->apiId           = null;
          $newPersonId = $newPerson->create();
          

          $imgModel = new PersonImagesModel();
          $imgModel->imgName = $fileName . ".jpg" ;
          $imgModel->imgFaceId = null;
          $imgModel->update($newPersonId); // update image table                               
          
          $notify = new NotificationsModel();
          $notify->message = "Good News , Matching Occured!";
          $notify->time = date("M,d,Y h");                   
          $notify->seen  = false;
          $notify->type  = 'matching';
          $notify->userId  = $_POST["userId"];
          $notify->personId  = $newPersonId;
          $notify->create();
          $identifiedPersonObj = new PersonModel();
          $identifiedPerson    = $identifiedPersonObj->getByPK($identifiedPersonId);          
          $identifiedPersonObj->updateToIdentified($newPersonId,$identifiedPerson['id']); // update the identified person as well to be matched

          $userOfIdentifiedPerson = new UsersModel();
          $userOfIdentifiedPerson = $userOfIdentifiedPerson->getByPK($identifiedPerson['userId']);
          
          $notify->userId   = $identifiedPerson['userId']; // change only personId to the
          $notify->personId =  $identifiedPersonId;
          $notify->create();

          $this->response['error'] = false;
          $this->response['msg'] = "Matching"; 
      }
      // $this->_data['msg'] = $this->response['msg'];
      // echo $this->response['msg'];
      return;
  }
#####################################################
public function createPostAction($newPerson,$imgModel)
    {    
      $newPost                = new PostsModel();
      $newPost->fullName      = $newPerson->fullName;
      $newPost->gender        = $newPerson->gender;
      $newPost->age           = $newPerson->age;
      $newPost->city          = $newPerson->city;
      $newPost->personData    = $newPerson->personData;
      $newPost->userId        = $_POST['userId'];
      $newPost->personId      = $newPerson->id;
      $newPost->img1          = $imgModel->imgName;
      $newPost->time          = date("M,d,Y h:i:s A");
      $newPost->create();        
        
    }
  






} //end class
