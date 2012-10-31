<?php
function m_curl($tasks)
{
    $cmh = curl_multi_init();
    $tasks_curl = array();
    foreach ($tasks as $task) {
        $ch = curl_init($task);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $tasks_curl[$task] = $ch;
        curl_multi_add_handle($cmh, $ch);
    }
    $active = null;
    do {
        $mrc = curl_multi_exec($cmh, $active);
    }
    while ($mrc == CURLM_CALL_MULTI_PERFORM);
    while ($active && ($mrc == CURLM_OK)) {
//        if (curl_multi_select($cmh) != -1) {
            do {
                $mrc = curl_multi_exec($cmh, $active);
                $info = curl_multi_info_read($cmh);
                if ($info['msg'] == CURLMSG_DONE) {
                    $ch = $info['handle'];
                    $task = array_search($ch, $tasks_curl);
                    $tasks_curl[$task] = curl_multi_getcontent($ch);
                    curl_multi_remove_handle($cmh, $ch);
                    curl_close($ch);
                }
            }
            while ($mrc == CURLM_CALL_MULTI_PERFORM);
//        }
    }
    curl_multi_close($cmh);
    return $tasks_curl;
    return 0;
}
$task = array('ya.ru','google.com','vk.com');
;
print_r(sizeof(m_curl($task)));