<?php
class CnCApi
{

    private $session = "";
    private $server;
    private $pollRequests = 0;
    private $url = null;

    private $ch = null;

    public function __construct($server)
    {
        $this->servers = require dirname(__FILE__) . DIRECTORY_SEPARATOR . "servers.php";

        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_HEADER, true);

        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);

        $this->selectWorld($server);

        if (!$this->getWorldSession()) {
            print_r("World session failed\r\n");
            die;
        }
    }

    public function startWorld($server)
    {
        $this->selectWorld($server);
        curl_setopt($this->ch, CURLOPT_URL, "https://gamecdnorigin.tiberiumalliances.com/WebWorldBrowser/start.aspx?server=$this->url&sessionID=$this->session");
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
            "User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:11.0) Gecko/20100101 Firefox/11.0",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: en-us,en;q=0.5",
            "Cache-Control: no-cache",
            "Connection: keep-alive"
        ));

        $res = curl_exec($this->ch);

        file_put_contents("c:\\selectWorld.html", $res);
    }

    public function selectWorld($server)
    {
        if (!isset($this->servers[$server])) {
            print_r("Server $server not found");
            die;
        }

        $this->server = $server;
        $this->url = $this->servers[$this->server]['Url'];
        print_r("Start on: " . $this->servers[$this->server]['Name'] . "\r\n");

        curl_setopt($this->ch, CURLOPT_COOKIEJAR, "." . DIRECTORY_SEPARATOR . "cookies" . DIRECTORY_SEPARATOR . "cookies_{$this->server}.txt");
        curl_setopt($this->ch, CURLOPT_COOKIEFILE, "." . DIRECTORY_SEPARATOR . "cookies" . DIRECTORY_SEPARATOR . "cookies_{$this->server}.txt");

    }

    public function  getData($method, $data = array(), $isRaw = false, $service = "Presentation")
    {

        $url = $this->url;
        curl_setopt($this->ch, CURLOPT_URL, $url . "/$service/Service.svc/ajaxEndpoint/" . $method);

        curl_setopt($this->ch, CURLOPT_TIMEOUT, 15);

        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_HEADER, false);


        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array( /*"Host: $host",*/
            "User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:10.0.2) Gecko/20100101 Firefox/10.0.2",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: en-us,en;q=0.5",
            "Accept-Encoding: gzip, deflate",
            "Content-Type: application/json; charset=UTF-8",
            "X-Qooxdoo-Response-Type: application/json",
            "Referer: $url/index.aspx",
            "Pragma: no-cache",
            "Cache-Control: no-cache"
        ));


        $data['session'] = $this->session;
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($this->ch);
        return $isRaw ? $response : json_decode($response);
    }

    public function setSession($ses)
    {
        $this->session = $ses;
    }

    public function isValidSession()
    {
        return $this->getPlayerCount() > 0;
    }

    public function getPlayerCount()
    {
        return $this->getData('RankingGetCount', array(
            "view" => 0,
            "rankingType" => 0)) - 1;
    }

    public function poll($request, $isRaw = false)
    {
        $request['requestid'] = $this->pollRequests;
        $request['sequenceid'] = $this->pollRequests;
        $this->pollRequests++;
        return $this->getData('Poll', $request, $isRaw);
    }

    public function createThread($title, $msg, $forumId)
    {
        $this->getData("CreateForumThread", array(
            "firstPostMessage" => $msg,
            "forumID" => $forumId,
            "subscribe" => true,
            "threadTitle" => $title
        ));
        foreach ($this->getData("GetForumThreads", array(
            "forumId" => $forumId,
            "skip" => 0,
            "take" => 10
        )) as $thread) {
            if ($thread->t == $title) {
                return $thread->i;
            }
        }
    }

    public function addPost($msg, $forumId, $threadId)
    {
        $this->getData("CreateForumPost", array(
            "postMessage" => $msg,
            "forumID" => $forumId,
            "threadID" => $threadId
        ));
    }

    public function getPlayers($from, $to)
    {
        return $this->getData('RankingGetData', array(
                "view" => 0,
                "rankingType" => 0,
                "ascending" => true,
                "firstIndex" => $from,
                "lastIndex" => $to,
                "sortColumn" => 0)
        );
    }

    public function getUserInfo($id)
    {
        return $this->getData('GetPublicPlayerInfo', array("id" => $id));
    }

    public function getServers()
    {
        $this->url = "https://gamecdnorigin.alliances.commandandconquer.com";
        return $this->getData('GetOriginAccountInfo', array(), false, "Farm");
    }

    public function getServer()
    {
        return $this->server;
    }

    public function getWorldSession()
    {
        $this->initCookie();
//        $this->logout();

        $this->getLoginPage();


        $this->postLoginData();

        return $this->launch();
    }

    public function authorize()
    {
        print_r("Open ingame session ");

        $data = $this->getData("OpenSession", array(
            "refId" => time() * 10,
            "rest" => "true",
            "version" => "-1"
        ));
        $gameSession = $data->i;

        if (!$gameSession || "00000000-0000-0000-0000-000000000000" == $gameSession) {
            print_r("failed\r\n");
//            file_put_contents("c:\\fail" . $this->getServer(), '');
            return false;
        }
        print_r("$gameSession\r\n");
        $this->setSession($gameSession);
        return $gameSession;
    }

    public function initCookie()
    {
        curl_setopt($this->ch, CURLOPT_URL, "https://www.tiberiumalliances.com/home");
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
            "User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:11.0) Gecko/20100101 Firefox/11.0",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: en-us,en;q=0.5",
            "Accept-Encoding: gzip, deflate",
            "Cache-Control: no-cache",
            "Connection: keep-alive"
        ));

        $res = curl_exec($this->ch);
        file_put_contents("c:\\init.html", $res);
        print_r("Cookie inited\r\n");
    }

    private function getLoginPage()
    {
        curl_setopt($this->ch, CURLOPT_URL, "https://www.tiberiumalliances.com/login/auth");
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
            "User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:11.0) Gecko/20100101 Firefox/11.0",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: en-us,en;q=0.5",
            "Accept-Encoding: gzip, deflate",
            "Referer: http://tiberiumalliances.com/intro/index",
            "Cache-Control: no-cache",
            "Connection: keep-alive"
        ));
        curl_setopt($this->ch, CURLOPT_POST, 0);

        $res = curl_exec($this->ch);
        file_put_contents("c:\\logpage.html", $res);
        print_r("Login page retrieved\r\n");
    }

    /**
     * @param $this->ch
     * @return String $worldSession
     * @throws Exception
     */
    private function postLoginData()
    {


        curl_setopt($this->ch, CURLOPT_URL, "https://www.tiberiumalliances.com/j_security_check");
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array("Host: www.tiberiumalliances.com",
            "User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:11.0) Gecko/20100101 Firefox/11.0",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: en-us,en;q=0.5",
            "Accept-Encoding: gzip, deflate",
            "DNT 1",
            "Referer: https://www.tiberiumalliances.com/login/auth",
            "Connection: keep-alive",
        ));

        print_r("Login " . $this->servers[$this->server]['u'] . ":" . $this->servers[$this->server]['p'] . "\r\n");

        $data = $this->makePostData(array(
            '_web_remember_me' => '',
            'spring-security-redirect' => '',
            'timezone' => 4,
            'id' => '',
            'j_username' => $this->servers[$this->server]['u'],
            'j_password' => $this->servers[$this->server]['p']
        ));
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);


        $res = curl_exec($this->ch);
        file_put_contents("c:\\login_post.html", $res);
    }

    private function logout()
    {
        print_r("Logout\r\n");

        curl_setopt($this->ch, CURLOPT_URL, "https://www.tiberiumalliances.com/en/logout");
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
            "User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:11.0) Gecko/20100101 Firefox/11.0",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: en-us,en;q=0.5",
            "Accept-Encoding: gzip, deflate",
            "Referer: 	https://www.tiberiumalliances.com/home",
            "DNT: 1",
            "Cache-Control: no-cache",
            "Connection: keep-alive",
            "x-insight:	activate"
        ));

        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_HEADER, true);
        $res = curl_exec($this->ch);
        file_put_contents("c:\\logout.html", $res);
        die;
    }

    private function launch()
    {
        print_r("Launching game \r\n");
        curl_setopt($this->ch, CURLOPT_URL, "https://www.tiberiumalliances.com/game/launch");
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array("Host: www.tiberiumalliances.com",
            "User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:11.0) Gecko/20100101 Firefox/11.0",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: en-us,en;q=0.5",
            "Accept-Encoding: gzip, deflate",
            "Referer: https://tiberiumalliances.com/home",
            "Connection: keep-alive",
        ));

        $res = curl_exec($this->ch);
        file_put_contents("c:\\launch.html", $res);
        preg_match('<input type="hidden" name="sessionId" value="(.*)?" \/>', $res, $session);

        if (isset($session[1])) {
            print_r("World session: {$session[1]}\r\n");

            preg_match("/ action=\"(.*?)\/index.aspx\" /", $res, $url);
            $this->session = $session[1];
            return $session[1];
        }
        return null;
    }

    private function enterWorld($worldSession)
    {
        print_r("Entering world\r\n");
        curl_setopt($this->ch, CURLOPT_URL, "$this->url/index.aspx");
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array("Host: prodgame05.tiberiumalliances.com",
            "User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:11.0) Gecko/20100101 Firefox/11.0",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: en-us,en;q=0.5",
            //        "Accept-Encoding: gzip, deflate",
            "Referer: http://tiberiumalliances.com/en/",
            "Cache-Control: no-cache",
            "Connection: keep-alive",
            "x-insight:	activate"
        ));

        curl_setopt($this->ch, CURLOPT_POST, 1);
        $data = $this->makePostData(array(
            'sessionId' => $worldSession
        ));
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);

        curl_setopt($this->ch, CURLOPT_HEADER, false);
        $res = curl_exec($this->ch);
//        file_put_contents("c:\\enter.html", $res);
    }

    public static function makePostData($data)
    {
        $postData = array();
        foreach ($data as $key => $value) {
            $postData[] = urlencode($key) . "=" . urlencode($value);
        }
        $postData = implode("&", $postData);
        return $postData;
    }

    public function close()
    {
        curl_close($this->ch);
    }

    public function getSession()
    {
        return $this->session;
    }

    public function register()
    {
        if ($this->getData("CreateNewPlayer", array("cityName" => 'limitium', "cityType" => 1, "name" => 'limitium', "startDir" => "rnd"))) {
            $this->servers[$this->server]['u'] = "limitium@gmail.com";
            print_r("Registered successful\r\n");
        } else {
            print_r("Register fail\r\n");
        }

    }

    public function checkNewServers()
    {
        foreach ($this->getServers()->Servers as $server) {
            if (!isset($this->servers[$server->Id])) {
                $server->x = 32;
                $server->y = 32;
                $server->u = "empty";
                $server->p = "qweqwe123";
                unset($server->Faction);
                unset($server->Friends);
                unset($server->Invites);
                unset($server->PlayerCount);
                unset($server->Online);
                unset($server->LastSeen);
                $this->servers[$server->Id] = (array)$server;
            } else {
                $this->servers[$server->Id]["Url"] = $server->Url;
            }
            if ($this->servers[$server->Id]["u"] == "empty") {
                print_r("New world: $server->Id - $server->Name\r\n");
            }
        }

    }

    public function saveServers()
    {
        $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        file_put_contents($dir . "servers.php", "<?php return " . var_export($this->servers, 1) . ";");

        function sortName($a, $b)
        {
            return (strtolower($a['name']) > strtolower($b['name'])) ? 1 : -1;
        }

        $clientServers = array();
        foreach ($this->servers as $k => $server) {
            if (!in_array($k, array('limitium', 'util'))) {
                $clientServers[] = array('id' => $server['Id'], 'name' => $server['Name']);
            }
        }

        uasort($clientServers, "sortName");
        file_put_contents("c:\\WebServers\\home\\ta-f\\www\\models\\servers.php", "<?php return " . var_export(array_values($clientServers), 1) . ";");
    }

}
