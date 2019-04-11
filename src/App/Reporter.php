<?php

namespace App;

use Exception;

class Reporter extends Utility
{

    public $baseURL = "https://sandbox-reporting.rpdpymnt.com";
    protected $loginURL = "__BASE_URL__/api/v3/merchant/user/login"; // Login​ ​with​ ​email​ ​and​ ​password.
    protected $transActionsReportURL = "__BASE_URL__/api/v3/transactions/report"; // Request​ ​for​ ​list​ ​of​ ​transaction.
    protected $transactionListURL = "__BASE_URL__/api/v3/transaction/list"; // Request​ ​for​ ​list​ ​of​ ​transaction.
    protected $transActionURL = "__BASE_URL__/api/v3/transaction"; // Request​ ​for​ ​list​ ​of​ ​transaction.
    protected $clientURL = "__BASE_URL__/api/v3/client"; // Request​ ​for​ ​all​ ​information​ ​of​ ​transaction.
    protected $tokenTimeOut = (60 * 10) - 20; // 10 minutes - 20 minutes delay
    protected $username = "";
    protected $password = "";
    protected $tokenFile = "";

    private $token;
    private $tokenExpireTime;


    public function setTokenExpireTime($time)
    {
        $this->tokenExpireTime = $time;

    }

    public function getTokenExpireTime()
    {
        return $this->tokenExpireTime;
    }


    public function setTokenFile($file)
    {
        $this->tokenFile = $file;
    }

    public function getTokenFile()
    {
        return $this->tokenFile;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getUsername()
    {
        if ($this->username == "") {
            throw  new Error("Username can not be blank");
        }
        return $this->username;
    }

    public function getPassword()
    {
        if ($this->password == "") {
            throw  new Error("Password can not be blank");
        }
        return $this->password;
    }


    public function getToken()
    {
        return $this->token;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getTokenFromCache()
    {
        $cacheDATA = $this->getCache($this->getTokenFile());
        if ($cacheDATA == false) {
            $this->doLogin();
        }

        $this->setToken($cacheDATA['token']);
        $this->setTokenExpireTime($cacheDATA['tokenExpireTime']);

        // Token Expire
        if ($this->getTokenExpireTime() == "" || $this->getTokenExpireTime() < time()) {
            $this->doLogin();
        }

    }


    public function doLogin()
    {
        $data['url'] = $this->loginURL;
        $data['post'] = array(
            "email" => $this->getUsername(),
            "password" => $this->getPassword()
        );

        $result = $this->getData($data);
        $result = json_decode($result, TRUE);

        if (isset($result) && $result !== NULL) {
            if ($result["status"] == "APPROVED") {

                //Set Token
                $this->setToken($result["token"]);
                $this->setTokenExpireTime(time() + $this->tokenTimeOut);
                $this->writeCache($this->getTokenFile(), array(
                    'token' => $this->getToken(),
                    'tokenExpireTime' => $this->getTokenExpireTime(),
                ));
                return true;
            } else {
                $this->_error("Server Response %s Status", $result['status']);
            }
        } else {
            $this->_error("Login Return Empty Result");
        }
    }

    public function transActionReport($report)
    {

        if ($this->getToken() !== NULL) {
            $data["post"] = $report;
            $data['url'] = $this->transActionsReportURL;
            $data['header'] = array(
                "Authorization: " . $this->getToken()
            );
            $result = $this->getData($data);
            $result = json_decode($result, TRUE);

            if (!isset($result['status'])) {
                $result = json_decode($result, true);
                $this->_error("Unexpected error from server.");
            }
            if ($result['status'] != "APPROVED") {
                $result = json_decode($result, true);
                $this->_error("Server Response %s Status", $result['status']);

            }
            return $result;
        } else {
            $this->_error("Token Invalid");
        }


    }

    public function transActionList($list)
    {


        if ($this->getToken() !== NULL) {
            $data["post"] = $list;
            $data['url'] = $this->transactionListURL;
            $data['header'] = array(
                "Authorization: " . $this->getToken()
            );
            $result = $this->getData($data);
            $result = json_decode($result, TRUE);

            if (!isset($result['from'])) {
                $this->_error("Unexpected error from server.");
            }
            return $result;
        } else {
            $this->_error("Token Invalid");
        }


    }

    public function transAction($id)
    {
        if ($this->getToken() !== NULL) {
            if ($id !== NULL && isset($id)) {
                $data['url'] = $this->transActionURL;
                $data['post'] = array(
                    "transactionId" => $id
                );
                $data['header'] = array(
                    "Authorization:$this->token"
                );
                $result = $this->getData($data);
                $result = json_decode($result, TRUE);
                if (!isset($result['transaction'])) {
                    $this->_error("Server Response %s Status. Message: %s ", $result['status'], $result['message']);
                }
                return $result;
            } else {
                $this->_error("TRANSACTION ID  EMPTY");
            }


        } else {
            $this->_error("Token Invalid");
        }
    }


    public function getClient($id)
    {
        if ($this->getToken() !== NULL) {
            if ($id !== NULL && isset($id)) {
                $data['url'] = $this->clientURL;
                $data['post'] = array(
                    "transactionId" => $id
                );
                $data['header'] = array(
                    "Authorization:$this->token"
                );
                $resultData = $this->getData($data);
                $result = json_decode($resultData, true);
                if (!isset($result['customerInfo'])) {
                    $result = json_decode($result, true);
                    $this->_error("Server Response %s Status. Message: %s ", $result['status'], $result['message']);
                }
                return $result;
            } else {
                $this->_error("TRANSACTION ID  EMPTY CLIENT");
            }
        } else {
            $this->_error("Token Invalid");
        }
    }

    public function getJwt()
    {
        list($header, $payload, $signature) = explode(".", $this->getToken());
        return json_decode(base64_decode($payload), true);
    }

    public function _error($message, $status = NULL, $rmsg = NULL)
    {
        if (isset($status)) {
            if (isset($rmsg)) {
                $err = sprintf($message, $status, $rmsg);
                throw new Exception($err);
            }
            $err = sprintf($message, $status);
            throw new Exception($err);
        }
        throw new Exception($message);


    }


}


