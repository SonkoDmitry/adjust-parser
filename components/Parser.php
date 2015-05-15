<?php

namespace app\components;

use yii\base\Component;
use yii\console\Exception;
use yii\helpers\StringHelper;

/**
 * Class Parser
 * @property string $token Get current auth token
 * @package app\components
 */
class Parser extends Component
{
    /**
     * @var array App token
     */
    public $app;

    /**
     * @var array Login for auth
     */
    public $login;

    /**
     * @var array password for auth
     */
    public $password;

    /**
     * @var string Token for requests
     */
    protected $authToken = null;

    /**
     * @var string Api url
     */
    public $apiUrl = 'https://api.adjust.com/apps/:app?end_date=:date&start_date=:date&sandbox=false&user_token=:token';

    public function doSomething() {
        return '123';
    }

    public function init() {
        if (empty($this->app)) {
            throw new Exception('Config value "app" is not be null or empty');
        }

        if (empty($this->login)) {
            throw new Exception('Config value "login" is not be null or empty');
        }

        if (empty($this->password)) {
            throw new Exception('Config value "password" is not be null or empty');
        }
        //$this->apiUrl = str_replace(':app', $this->app, $this->apiUrl);
    }

    protected function auth() {
    }

    public function get($data) {
        $url = strtr($this->apiUrl, [
            ':app' => $this->app,
            ':date' => $data,
            ':token' => $this->token,
            //':kpis'=>'clicks',
        ]);

        //var_dump($url);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);

        if ((int) $info['http_code'] == 401) {
            throw new Exception('Invalid auth token. Please try again');
        } elseif (!empty($result)) {
            $result = json_decode($result, true);
        } else {
            throw new Exception('Invalid error');
        }
        curl_close($ch);

        return $result;
    }

    public function getByTracker($data, $tracker) {
        $url = strtr($this->apiUrl, [
            ':app' => $this->app . '/trackers/' . $tracker,
            ':date' => $data,
            ':token' => $this->token.'&grouping=days',
            //':kpis'=>'clicks,installs,click_conversion_rate,reattributions,sessions,revenue_events,revenues,daus,waus,maus'
        ]);

        //var_dump($url);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);

        if ((int) $info['http_code'] == 401) {
            throw new Exception('Invalid auth token. Please try again');
        } elseif (!empty($result)) {
            $result = json_decode($result, true);
        } else {
            throw new Exception('Invalid error');
        }
        curl_close($ch);

        return $result;
    }

    protected function getToken() {
        if (empty($this->authToken)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://accounts.adjust.com/users/sign_in.json');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_VERBOSE, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, 'user[email]=' . urlencode($this->login) . '&user[password]=' . urlencode($this->password));

            $result = curl_exec($ch);
            $info = curl_getinfo($ch);

            if ((int) $info['http_code'] == 401) {
                throw new Exception('Invalid "login" or "password" config values');
            } elseif (!empty($result)) {
                $result = json_decode($result, true);
                if (isset($result['authentication_token']) && $result['authentication_token']) {
                    $this->authToken = $result['authentication_token'];
                } else {
                    throw new Exception('Invalid error');
                }
            } else {
                throw new Exception('Invalid error');
            }
            curl_close($ch);
        }

        return $this->authToken;
    }
}