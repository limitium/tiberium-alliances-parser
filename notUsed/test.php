<?php

/*
Login with username and password
*/

$request =  'https://alliances.commandandconquer.com/j_security_check'; 

$session = curl_init($request);

$data['spring-security-redirect'] = '';
$data['timezone'] = 2;
$data['j_username'] = urlencode('xxx@gmail.com');
$data['j_password'] = urlencode('xxx');

curl_setopt ($session, CURLOPT_POST, true);
curl_setopt ($session, CURLOPT_POSTFIELDS, 'spring-security-redirect=&timezone=2&j_username='.urlencode('wijchers@gmail.com').'&j_password='.urlencode('@ppelmoes3'));
curl_setopt($session, CURLOPT_HEADER, true);
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
curl_setopt($session, CURLOPT_COOKIEFILE, '/tmp/cookies/cookie.txt');
curl_setopt($session, CURLOPT_COOKIEJAR, '/tmp/cookies/cookie.txt');
curl_setopt($session, CURLOPT_HTTPHEADER, array("Content-Type:application/x-www-form-urlencoded")); 

$response = curl_exec($session);

curl_close($session);

echo nl2br($response);

echo "==next==<br /><br />";

/*
Retrieve sessionID
*/

$request =  'https://alliances.commandandconquer.com/en/game/worldBrowser'; 

$session2 = curl_init($request);

curl_setopt ($session2, CURLOPT_HTTPGET, true);
curl_setopt($session2, CURLOPT_HEADER, true);
curl_setopt($session2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($session2, CURLOPT_COOKIEFILE, '/tmp/cookies/cookie.txt');
curl_setopt($session2, CURLOPT_COOKIEJAR, '/tmp/cookies/cookie.txt');

$response = curl_exec($session2);

curl_close($session2);

echo nl2br($response);
echo "==next==<br /><br />";
preg_match('/<input type="hidden" name="sessionID" value="(.*?)" \/>/',$response,$match);

print_r($match);
echo "==next==<br /><br />";

/*
Call player info
*/

unset($data);

$request =  'https://gamecdnorigin.alliances.commandandconquer.com/Farm/Service.svc/ajaxEndpoint/GetOriginAccountInfo'; 

$data['session'] = $match['1'];

$session3 = curl_init($request);

curl_setopt ($session3, CURLOPT_POST, true);
curl_setopt ($session3, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($session3, CURLOPT_HEADER, false);
curl_setopt($session3, CURLOPT_RETURNTRANSFER, true);
curl_setopt($session3, CURLOPT_HTTPHEADER, array("Content-Type: application/json; charset=utf-8")); 

$response = curl_exec($session3);

curl_close($session3);
    
print_r(json_decode($response, true));

/*
Open session
*/
echo "==next==<br /><br />";
unset($data);

$request =  'https://prodgame03.alliances.commandandconquer.com/11/Presentation/Service.svc/ajaxEndpoint/OpenSession'; 

$data['session'] = $match['1'];
$data['reset'] = 'true';
$data['refId'] = 1334746681386;
$data['version'] = -1;

$session4 = curl_init($request);

curl_setopt ($session4, CURLOPT_POST, true);
curl_setopt ($session4, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($session4, CURLOPT_HEADER, false);
curl_setopt($session4, CURLOPT_RETURNTRANSFER, true);
curl_setopt($session4, CURLOPT_HTTPHEADER, array("Content-Type: application/json; charset=utf-8")); 

$response = curl_exec($session4);

curl_close($session4);
$response = json_decode($response, true);
print_r($response);

/*
Now check if I'm really logged in on the world succesful
*/
echo "==next==<br /><br />";
unset($data);

$request =  'https://prodgame03.alliances.commandandconquer.com/11/Presentation/Service.svc/ajaxEndpoint/GetServerInfo'; 

$data['session'] = $response['i'];

$session5 = curl_init($request);

curl_setopt ($session5, CURLOPT_POST, true);
curl_setopt ($session5, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($session5, CURLOPT_HEADER, false);
curl_setopt($session5, CURLOPT_RETURNTRANSFER, true);
curl_setopt($session5, CURLOPT_HTTPHEADER, array("Content-Type: application/json; charset=utf-8")); 

$response = curl_exec($session5);

curl_close($session5);
    
print_r(json_decode($response, true));



?>
