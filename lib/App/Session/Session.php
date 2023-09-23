<?php
namespace App\Session;

use Core\Bundle;

class Session extends Bundle
{
    private $session_name = "SIMFRA";
    private $cookie_path = "/";
    private $session_timeout = 60*60;
    private $cookie_timeout = 60*60;
    private $browserSalt = "*&^#%KHG^!1a";
    private $requestRegenerate = 5;
    private $refreshTime = 60*60;

    public function __construct()
    {
        if (!@session_id()) {
            session_start();
        }
    }

    public function logout($redirect = "")
    {
        $this->destroy();
        if ($redirect != "") {
            $this->getKernel()->redirectByName("login", $this->getKernel()->page->lang, ["redirect" => $redirect]);
        } else {
            $this->getKernel()->redirectByName("login", $this->getKernel()->page->lang);
        }
    }

    public function refreshUser($username)
    {
        $user = $this->getService("App\User\User", true);
        $user->getUserByUsername($username);
        $this->setSession($user->getData(), $user->getPermissions(true));
    }

    public function checkSession()
    {
        echo "<br />browser: " . md5($_SERVER["HTTP_USER_AGENT"] . $this->browserSalt);
        echo "<br />stored: " . $this->get("BROWSER");
        //echo "<pre>";
        //$this->setSession(["asdas"=> []],["llll"=> []]);
        //print_r($_SESSION);
        //echo "</pre>";
        $logger = $this->getBundle("Logger");

        if (isset($_SESSION[$this->session_name])) {
            // JEST SESJA
            echo "<br/>jest sesja";
            if (isset($_SESSION[$this->session_name]['ACTIVITY']) && ((time() - $_SESSION[$this->session_name]['ACTIVITY']) > $this->session_timeout)) {
                // wylogowanie
                $logger->logActivity("SESSION", "USER_INACTIVITY_LOGOUT");
                $this->logout($this->getKernel()->page->url);
            } elseif (md5($_SERVER["HTTP_USER_AGENT"] . $this->browserSalt) != $this->get("BROWSER")) {
                // something wrong with browser
                echo "inna przeglądarka";
                $logger->logActivity("SESSION", "USER_BROWSER_ERROR_LOGOUT", $this->get("USER")["id"]);
                $this->logout();
            } else {
                $this->set("ACTIVITY", time());
                if ($this->get("REFRESH_COUNT") >= $this->requestRegenerate || ((time() - $_SESSION[$this->session_name]['ACTIVITY']) > $this->session_timeout)) {
                    // odswiez dane w sesji z bany
                    echo "<br/>odswiezanie sesji z bazy";
                    $this->refreshUser($this->get("USER")["username"]);
                    $this->set("REFRESH_COUNT", 0);

                } else {
                    $this->set("REFRESH_COUNT", $this->get("REFRESH_COUNT")+1);
                    echo "<br/>odswiezanie sesji";
                }
                // odswiezenie sesji
                //echo "odswiezanie sesji";
            }
        } else {
            echo "Brak sesji $redirect";
            $this->logout();
            $logger->logActivity("SESSION", "NO_SESSION");
            // TODO: update in db last activity ?
        }
        //
        //$this->destroy();
    }



    public function setSession($user, $permission)
    {
        session_regenerate_id(true);
        $this->set("SESSION_ID", session_id());
        $this->set("USER", $user);
        $this->set("PERMISSIONS", $permission);
        $this->set("ACTIVITY", time());
        $this->set("BROWSER", md5($_SERVER["HTTP_USER_AGENT"] . $this->browserSalt));
        setcookie($this->session_name, $_SESSION[$this->session_name]['SESSION_ID'], time() + $this->cookie_timeout, $this->cookie_path);
    }


    public function getData()
    {
        if (isset($_SESSION[$this->session_name])) {
            return $_SESSION[$this->session_name];
        }
        return false;
    }

    public function getUser()
    {
        if (isset($_SESSION[$this->session_name]['USER'])) {
            return $_SESSION[$this->session_name]['USER'];
        }
        return false;
    }

    public function getPermissions()
    {
        if (isset($_SESSION[$this->session_name]['PERMISSIONS'])) {
            return $_SESSION[$this->session_name]['PERMISSIONS'];
        }
        return false;
    }

    public function destroy()
    {
        unset($_SESSION[$this->session_name]);
        if (isset($_COOKIE[$this->session_name])) {
            setcookie($this->session_name, $_SESSION[$this->session_name]['SESSION_ID'], time() - 3600, $this->cookie_path);
        }
        session_unset();
        session_destroy();
    }

    public function set($key, $value)
    {
        $_SESSION[$this->session_name][$key] = $value;
    }

    public function get($key)
    {
        if (isset($_SESSION[$this->session_name][$key])) {
            return $_SESSION[$this->session_name][$key];
        } else {
            trigger_error("Undefined variable $key in SESSION");
            return false;
        }
    }

}