<?php
/**
 * PHP CORS Proxy
 *
 * Licensed under the terms of the Simplified BSD License
 *
 * Copyright (C) 2013, Three Wise Men Inc.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this
 *    list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * The views and conclusions contained in the software and documentation are those
 * of the authors and should not be interpreted as representing official policies,
 * either expressed or implied, of Three Wise Men Inc.
 */

class PHPCorsProxyConfig {
    public $proxies = array();

    public function addProxy($url, $prefix) {
        array_push($this->proxies, array($url, $prefix));
    }
}

class PHPCorsProxy {
    protected $config = array();

    public function __construct($config = array()) {
        $this->config = $config;
    }

    public function serviceRequest() {
        $postData = file_get_contents("php://input");
        $postFields = substr_count($postData, "&") + 1;
        $incomingHeaders = apache_request_headers();

        $newHeaders = array(
            'Accept: ' . $incomingHeaders['Accept'],
            'Content-Type: ' . $incomingHeaders['Content-Type']
        );

        foreach ($this->config as $item) {
            $string = "/" . $item[1] . "([^\?]*)(\?.*)?/";
            if (preg_match($string, $_SERVER['REQUEST_URI'], $matches) > 0) {
                $ch = curl_init();
                $url = $item[0] . $matches[1] . $matches[2];
                curl_setopt($ch, CURLOPT_URL, $item[0] . $matches[1] . $matches[2]);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_VERBOSE, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_POST, count($_POST));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $newHeaders);

                $result = curl_exec($ch);

                list($header, $body) = explode("\r\n\r\n", $result);
                $headerStrings = explode("\r\n", $header);
                $headers = array();
                foreach ($headerStrings as $headerString) {
                    $tmp = explode(": ", $headerString);
                    $headers[$tmp[0]] = $tmp[1];
                }

                curl_close($ch);

                header('Content-Type: ' . $headers['Content-Type']);

                echo $body;
            }
        }
    }
}

$config = new PHPCORSProxyConfig();
$config->addProxy("http://coin-toss.herokuapp.com", "gameon");
$proxy = new PHPCORSProxy($config->proxies);
$proxy->serviceRequest();
?>
