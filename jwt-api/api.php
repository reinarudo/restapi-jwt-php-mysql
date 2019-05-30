<?php 

use Firebase\JWT\JWT;

class Api extends Rest
{

    public function __construct(){
        parent::__construct();
    }

    public function generateToken(){
        $email = $this->validateParameter('email', $this->param['email'], STRING);
        $pass = $this->validateParameter('pass', $this->param['pass'], STRING);

        try {
            $stmt = $this->dbConn->prepare("SELECT * FROM users WHERE email = :email AND password = :pass");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':pass', $pass);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!is_array($user)) {
                $this->returnResponse(INVALID_USER_PASS, ['message' => 'Email or Pasword is incorrect']);
            }

            if ($user['active'] == 0) {
                $this->returnResponse(USER_NOT_ACTIVE, 'User is not activated. Please contact admin.');
            }

            $payload = [
                'iat' => time(), //issued at
                'iss' => 'localhost', //issuer
                'exp' => time() + (60) * 15, //15 minutes
                'userId' => $user['id']
            ];

            $token = JWT::encode($payload, SECRET_KEY);
        } catch (Exception $e) {
            $this->throwError(JWT_PROCESSING_ERROR, $e->getMessage());
        }
            
        $data = ['token' => $token];
        $this->returnResponse(SUCCESS_RESPONSE, $data);
    }

    public function addCustomer(){
        $name = $this->validateParameter('name', $this->param['name'], STRING);
        $email = $this->validateParameter('email', $this->param['email'], STRING);
        $addr = $this->validateParameter('addr', $this->param['addr'], STRING);
        $mobile = $this->validateParameter('mobile', $this->param['mobile'], STRING);

        $cust = new Customer;
        $cust->setName($name);
        $cust->setEmail($email);
        $cust->setAddress($addr);
        $cust->setMobile($mobile);
        $cust->setCreatedBy($this->userId);
        $cust->setCreatedOn(date('Y-m-d'));

        if (!$cust->insert()) {
            $message = 'Failed to insert.';
        } else {
            $message = 'Inserted successfully.';
        }

        $this->returnResponse(SUCCESS_RESPONSE, $message);
    }

    public function getCustomerDetails(){
        $customerId = $this->validateParameter('customerId', $this->param['customerId'], INTEGER, false);

        $cust = new Customer;
        $cust->setId($customerId);
        $customer = $cust->getCustomerDetailsById();
        if (!is_array($customer)) {
            $this->returnResponse(SUCCESS_RESPONSE, ['message' => 
                'Customer details are not in database.']);
        }

        $response['customerId'] = $customer['id'];
        $response['customerName'] = $customer['name'];
        $response['email'] = $customer['email'];
        $response['mobile'] = $customer['mobile'];
        $response['address'] = $customer['address'];
        $response['createdBy'] = $customer['created_user'];
        $response['lastUpdatedBy'] = $customer['updated_user'];

        $this->returnResponse(SUCCESS_RESPONSE, $response);
    }

    public function updateCustomer(){
        $customerId = $this->validateParameter('customerId', $this->param['customerId'], STRING);
        $name = $this->validateParameter('name', $this->param['name'], STRING);
        $addr = $this->validateParameter('addr', $this->param['addr'], STRING);
        $mobile = $this->validateParameter('mobile', $this->param['mobile'], STRING);

        $cust = new Customer;
        $cust->setId($customerId);
        $cust->setName($name);
        $cust->setAddress($addr);
        $cust->setMobile($mobile);
        $cust->setUpdatedBy($this->userId);
        $cust->setUpdatedOn(date('Y-m-d'));

        if (!$cust->update()) {
            $message = 'Failed to updated.';
        } else {
            $message = 'Updated successfully.';
        }

        $this->returnResponse(SUCCESS_RESPONSE, $message);
    }

    public function deleteCustomer(){
        $customerId = $this->validateParameter('customerId', $this->param['customerId'], STRING);
        
        $cust = new Customer;
        $cust->setId($customerId);

        if (!$cust->delete()) {
            $message = 'Failed to delete.';
        } else {
            $message = 'Deleted successfully.';
        }

        $this->returnResponse(SUCCESS_RESPONSE, $message);
    }
}