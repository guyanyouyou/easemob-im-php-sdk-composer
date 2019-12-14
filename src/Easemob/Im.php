<?php
/**
 * 环信即时通讯云 easemob IM PHP SDK（composer版）
 *
 * 书写规范：PSR2
 *
 * phpcs --standard=PSR2 *.php
 *
 * @author   sink <sinkcup@live.it>
 * @link     http://docs.easemob.com/im/100serverintegration/20users
 */

namespace Easemob;

class Im
{
    private $conf = array(
        'api_root' => 'https://a1.easemob.com/',
        'client_id' => null,
        'client_secret' => null,
        'org_name' => null,
        'app_name' => null,
        'access_token' => null,
    );

    public function __construct($conf)
    {
        $this->setConf($conf);
        if (empty($this->conf['access_token'])) {
            $this->conf['access_token'] = $this->grantToken()['access_token'];
        }
    }

    public function setConf($conf)
    {
        $this->conf = array_merge($this->conf, $conf);
        $this->conf['api_of_app'] = $this->conf['api_root'] . $this->conf['org_name'] . '/' . $this->conf['app_name'];
        return true;
    }

    /**
     * 获取授权管理员token
     *
     * @example shell curl -X POST "https://a1.easemob.com/easemob-demo/chatdemoui/token" -d '{"grant_type":"client_credentials","client_id":"YXA6wDs-MARqEeSO0VcBzaqg11","client_secret":"YXA6JOMWlLap_YbI_ucz77j-4-mI0dd"}'
     * @return boolean
     */
    public function grantToken()
    {
        $data = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->conf['client_id'],
            'client_secret' => $this->conf['client_secret'],
        ];
        $ch = curl_init();
        $options = array(
            CURLOPT_URL => $this->conf['api_of_app'] . '/token',
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
        );
        curl_setopt_array($ch, $options);
        $r = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code == 200) {
            return json_decode($r, true);
        }
        throw new Exception($r, $code);
    }

    /**
     * 注册 IM 用户[单个]
     *
     * @example shell curl -X POST -i "https://a1.easemob.com/easemob-demo/chatdemoui/users" -d '{"username":"jliu","password":"123456"}'
     * @return boolean
     */
    public function register($username, $password, $nickname = null)
    {
        $data = [
            'username' => $username,
            'password' => $password,
        ];
        if (!empty($nickname)) {
            $data['nickname'] = $nickname;
        }

        $ch = curl_init();
        $options = array(
            CURLOPT_URL => $this->conf['api_of_app'] . '/users',
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
        );
        if (!empty($this->conf['access_token'])) {
            $options[CURLOPT_HTTPHEADER][] = 'Authorization: Bearer ' . $this->conf['access_token'];
        }
        curl_setopt_array($ch, $options);
        $r = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code == 200) {
            return json_decode($r, true);
        }
        throw new Exception($r, $code);
    }

    /**
     * 给 IM 用户添加好友
     *
     * @example curl -X POST -H "Authorization: Bearer YWMtP_8IisA-EeK-a5cNq4Jt3QAAAT7fI10IbPuKdRxUTjA9CNiZMnQIgk0LEU2" -i  "https://a1.easemob.com/easemob-demo/chatdemoui/users/jliu/contacts/users/yantao"
     * @return boolean
     */
    public function addFriend($ownerUsername, $friendUsername)
    {
        $ch = curl_init();
        $options = array(
            CURLOPT_URL => $this->conf['api_of_app'] . '/users/' . $ownerUsername . '/contacts/users/' . $friendUsername,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
        );
        $options[CURLOPT_HTTPHEADER][] = 'Authorization: Bearer ' . $this->conf['access_token'];
        curl_setopt_array($ch, $options);
        $r = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code == 200) {
            return json_decode($r, true);
        }
        throw new Exception($r, $code);
    }

    /**
     * 发送文本消息
     *
     * @example curl -X POST -i -H "Authorization: Bearer YWMtxc6K0L1aEeKf9LWFzT9xEAAAAT7MNR_9OcNq-GwPsKwj_TruuxZfFSC2eIQ" "https://a1.easemob.com/easemob-demo/chatdemoui/messages" -d '{"target_type" : "users","target" : ["stliu1", "jma3", "stliu", "jma4"],"msg" : {"type" : "txt","msg" : "hello from rest"},"from" : "jma2"}'
     * @return boolean
     */
    public function sendMsg($target, $msg, $from, $targetType = 'users')
    {
        $data = [
            'target_type' => $targetType,
            'target' => $target,
            'msg' => $msg,
            'from' => $from,
        ];
        $ch = curl_init();
        $options = array(
            CURLOPT_URL => $this->conf['api_of_app'] . '/messages',
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
        );
        $options[CURLOPT_HTTPHEADER][] = 'Authorization: Bearer ' . $this->conf['access_token'];
        curl_setopt_array($ch, $options);
        $r = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code == 200) {
            return json_decode($r, true);
        }
        throw new Exception($r, $code);
    }

    /**
     * 获取聊天记录
     *
     * @example curl -X GET -i -H "Authorization: Bearer YWMtxc6K0L1aEeKf9LWFzT9xEAAAAT7MNR_9OcNq-GwPsKwj_TruuxZfFSC2eIQ" "https://a1.easemob.com/easemob-demo/chatdemoui/chatmessages"
     * @return boolean
     */
    public function getMsgs($ql = null, $limit = null, $cursor = null)
    {
        $data = [];
        if (!empty($ql)) {
            $data['ql'] = $ql;
        }
        if (!empty($limit)) {
            $data['limit'] = $limit;
        }
        if (!empty($cursor)) {
            $data['cursor'] = $cursor;
        }
        $ch = curl_init();
        $options = array(
            CURLOPT_URL => $this->conf['api_of_app'] . '/chatmessages?' . http_build_query($data),
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
        );
        $options[CURLOPT_HTTPHEADER][] = 'Authorization: Bearer ' . $this->conf['access_token'];
        curl_setopt_array($ch, $options);
        $r = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code == 200) {
            return json_decode($r, true);
        }
        throw new Exception($r, $code);
    }


    /**
     * 创建聊天室
     *
     * @param        $room_name
     * @param        $owner_name
     * @param string $room_description
     * @param int    $max_user
     * @param array  $member_users
     *
     * @return mixed
     */
    public function createRoom($room_name, $owner_name, $room_description = "描述", $max_user = 200, $member_users = [])
    {
        $data = [];
        if (!empty($room_name)) {
            $data['name'] = $room_name;
        }
        if (!empty($owner_name)) {
            $data['owner'] = $owner_name;
        }
        if (!empty($room_description)) {
            $data['description'] = $room_description;
        }
        if (!empty($max_user)) {
            $data['maxusers'] = $max_user;
        }
        if (!empty($member_users)) {
            $data['members'] = $member_users;
        }
        print_r($data);
        $ch = curl_init();
        $options = array(
            CURLOPT_URL => $this->conf['api_of_app'] . '/chatrooms',
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
        );
        $options[CURLOPT_HTTPHEADER][] = 'Authorization: Bearer ' . $this->conf['access_token'];
        curl_setopt_array($ch, $options);

        $r = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code == 200) {
            return json_decode($r, true);
        }
        throw new Exception($r, $code);
    }

    /**
     * 修改聊天室信息
     *
     * @param string $group_id
     * @param string $group_name
     * @param string $group_description
     * @param int    $max_user
     *
     * @return mixed
     * @throws EasemobError
     */
    public function editRoom($room_id, $room_name = "", $room_description = "", $max_user = 0)
    {
        $data = [];
        if (!empty($room_name)) {
            $data['name'] = $room_name;
        }
        if (!empty($room_description)) {
            $data['description'] = $room_description;
        }
        if (!empty($max_user)) {
            $data['maxusers'] = $max_user;
        }
        print_r($data);
        $ch = curl_init();
        $options = array(
            CURLOPT_URL => $this->conf['api_of_app'] . '/chatrooms/'.$room_id,
            //CURLOPT_PUT => true,
            CURLOPT_CUSTOMREQUEST=>'PUT',
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
        );
        $options[CURLOPT_HTTPHEADER][] = 'Authorization: Bearer ' . $this->conf['access_token'];
        curl_setopt_array($ch, $options);
        $r = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code == 200) {
            return json_decode($r, true);
        }
        throw new Exception($r, $code);
    }

     /**
     * 删除聊天室
     *
     * @param $room_id
     *
     * @return mixed
     */
    public function delRoom($room_id)
    {

        $ch = curl_init();
        $options = array(
            CURLOPT_URL => $this->conf['api_of_app'] . '/chatrooms/'.$room_id,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
        );
        $options[CURLOPT_HTTPHEADER][] = 'Authorization: Bearer ' . $this->conf['access_token'];
        curl_setopt_array($ch, $options);
        $r = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code == 200) {
            return json_decode($r, true);
        }
        throw new Exception($r, $code);
    }

    /**
     * 查询聊天室
     *
     * @param $room_id
     *
     * @return mixed
     */
    public function getRoom($room_id)
    {
        $ch = curl_init();
        $options = array(
            CURLOPT_URL => $this->conf['api_of_app'] . '/chatrooms/'.$room_id,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
        );
        $options[CURLOPT_HTTPHEADER][] = 'Authorization: Bearer ' . $this->conf['access_token'];
        curl_setopt_array($ch, $options);
        $r = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code == 200) {
            return json_decode($r, true);
        }
        throw new Exception($r, $code);
    }

    /**
     * 获取用户所有参加的聊天室
     *
     * @param $user
     *
     * @return mixed
     */
    public function userToRooms($username)
    {


        $ch = curl_init();
        $options = array(
            CURLOPT_URL => $this->conf['api_of_app'] . '/users/'.$username.'/joined_chatrooms',
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
        );

        $options[CURLOPT_HTTPHEADER][] = 'Authorization: Bearer ' . $this->conf['access_token'];
        curl_setopt_array($ch, $options);
        $r = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code == 200) {
            return json_decode($r, true);
        }
        throw new Exception($r, $code);
    }


    /**
     * 聊天室添加成员——批量
     *
     * @param string $room_id
     * @param array $users
     *
     * @return mixed
     */
    public function roomAddUsers($room_id, $users)
    {
        $data = [
            'usernames' => $users,
        ];

        $ch = curl_init();
        $options = array(
            CURLOPT_URL => $this->conf['api_of_app'] . 'chatrooms/'.$room_id.'/users',
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
        );
        if (!empty($this->conf['access_token'])) {
            $options[CURLOPT_HTTPHEADER][] = 'Authorization: Bearer ' . $this->conf['access_token'];
        }
        curl_setopt_array($ch, $options);
        $r = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code == 200) {
            return json_decode($r, true);
        }
        throw new Exception($r, $code);
    }


    /**
     * 聊天室删除成员——批量
     *
     * @param string $room_id
     * @param array $users
     *
     * @return mixed
     */
    public function roomDelUsers($room_id, $users)
    {
        $data = [
            'usernames' => $users,
        ];

        $ch = curl_init();
        $options = array(
            CURLOPT_URL => $this->conf['api_of_app'] . 'chatrooms/'.$room_id.'/users/'.implode(',', $users),
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
        );


        if (!empty($this->conf['access_token'])) {
            $options[CURLOPT_HTTPHEADER][] = 'Authorization: Bearer ' . $this->conf['access_token'];
        }
        curl_setopt_array($ch, $options);
        $r = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code == 200) {
            return json_decode($r, true);
        }
        throw new Exception($r, $code);
    }

}
