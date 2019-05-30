<?php
require_once('constants.php');

use Firebase\JWT\JWT;

class Rest 
{
    protected $request;
    protected $serviceName;
    protected $param;
    protected $dbConn;
    protected $userId;

    public function __construct()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->throwError(REQUEST_METHOD_NOT_VALID, 'Request Method is not valid');
        }

        $handler = fopen('php://input', 'r'); //read raw POST data
        $this->request = stream_get_contents($handler);
        $this->validateRequest($this->request);

        $db = new DbConnect;
        $this->dbConn = $db->connect();

        if ( 'generatetoken' != strtolower($this->serviceName) ) {
            $this->validateToken();
        }        
    }

    public function validateRequest($request)
    {
        if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
            $this->throwError(REQUEST_CONTENTTYPE_NOT_VALID, 'Request content type is not valid');
        }

        $data = json_decode($this->request, true);

        if (!isset($data['name']) || $data['name'] == '') {
            $this->throwError(API_NAME_REQUIRED, 'API name required');
        }
        $this->serviceName = $data['name'];

        if (!is_array($data['param'])) {
            $this->throwError(API_PARAM_REQUIRED, 'API Param is required');
        }

        $this->param = $data['param'];
    }

    public function processApi()
    {
        $api = new API;
        $rMethod = new ReflectionMethod('API', $this->serviceName);
        if (!method_exists($api, $this->serviceName)) {
            $this->throwError(API_DOES_NOT_EXIST, 'API does not exist.');
        }
        $rMethod->invoke($api);
    }

    public function validateParameter($fieldName, 
        $value, $dataType, $required = true)
    {
        if ($required && empty($value)) {
            $this->throwError(VALIDATE_PARAMETER_REQUIRED, 
            "$fieldName Parameter is required.");
        }

        switch ($dataType) {
            case BOOLEAN:
                if (!is_bool($value)) {
                    $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for  $fieldName. It should be boolean.");
                }
                break;
            
            case INTEGER:
                if (!is_numeric($value)) {
                    $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for $fieldName. It should be numeric.");
                }
                break;

            case STRING:
                if (!is_string($value)) {
                    $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for $fieldName. It should be string.");
                }
                break;

            default:
                $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for $fieldName.");
                break;
        }

        return $value;
    }

    public function throwError($code, $message)
    {
        header('Content-Type: applicaltion/json');
        $errorMsg = json_encode(['error' => ['status' => $code, 'message' => $message] ]);
        echo $errorMsg; exit;

    }

    public function returnResponse($code, $data)
    {
        header('Content-Type: application/json');
        $response = json_encode(['response' => ['status' => $code, 'result' => $data]]);
        echo $response; exit;
    }

    
    /**
     * Code from google search: how to  get bearer token. 
     * This function checks if Authorization header is set
     */
    public function getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            //Nginx or fast CGI
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of
            //this means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords',
                array_keys($requestHeaders)), array_values($requestHeaders));
            
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }

        return $headers;
    }

    // Code from google search: how to  get bearer token.
    public function getBearerToken()
    { 
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the acess token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }

        $this->throwError(AUTHORIZATION_HEADER_NOT_FOUND, 'Access Token Not Found.');
    }

    public function validateToken(){
        try {
            $token = $this->getBearerToken();
            $payload = JWT::decode($token, SECRET_KEY, ['HS256']);

            //check admin
            $stmt = $this->dbConn->prepare("SELECT * FROM users WHERE id = :userId");
            $stmt->bindParam(':userId', $payload->userId);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!is_array($user)) {
                $this->returnResponse(INVALID_USER_PASS, ['message' => 'This user is not found in our database.']);
            }

            if ($user['active'] == 0) {
                $this->returnResponse(USER_NOT_ACTIVE, 'May be deactivated. Please contact admin.');
            }

            $this->userId = $payload->userId;
        } catch (Exception $e) {
            $this->throwError(ACCESS_TOKEN_ERRORS, $e->getMessage());
        }
    } 
}