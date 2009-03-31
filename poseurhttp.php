<?php

/**
 * @file
 * PHP lib for making HTTP connections.
 */
class PoseurHTTP{
  var $host;
  var $path;
  var $method;
  var $postdata = '';
  // Options
  var $timeout = 20;
  var $connect_timeout = 20;
  var $verify_ssl = FALSE;
  // Response vars
  var $last_http_status;

  function __construct($host) {
    $this->host = $host;
  }

  function get($path, $data = FALSE) {
    $this->path = $path;
    $this->method = 'GET';
    if ($data) {
      $this->path .= '?'.$this->buildQueryString($data);
    }
    return $this->makeRequest();
  }

  function post($path, $data) {
    $this->path = $path;
    $this->method = 'POST';
    $this->post_data = $this->buildQueryString($data);
  return $this->makeRequest();
  }

  function makeRequest() {/*{{{*/
    $ci = curl_init();
    /* Curl settings */
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connect_timeout);
    curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ci, CURLOPT_HTTPHEADER, array('Expect:'));
    curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->verify_ssl);
    curl_setopt($ci, CURLOPT_URL, $this->host.'/'.$this->path);
    if ('POST' === $this->method) {
      curl_setopt($ci, CURLOPT_POST, TRUE);
      curl_setopt($ci, CURLOPT_POSTFIELDS, $this->post_data);
    }

    /* Curl response and cleanup */
    $response = curl_exec($ci);
    /* If Curl fails to connect a second time */
    if (0 === $response) {
      $response = curl_exec($ci);
    }
    $this->last_http_status = curl_getinfo($ci, CURLINFO_HTTP_CODE);
    curl_close ($ci);
    return $response;
  }/*}}}*/

  function buildQueryString($data) {
    $query_string = '';
    $query_string_array = array();
    if (is_array($data)) {
      foreach ($data as $key => $value) {
        $query_string_array[] = implode('=', array(urlencode($key), urlencode($value)));
      }
      $query_string = implode('&',$query_string_array);
    } else {
      $query_string = $data;
    }
    return $query_string;
  }

  function parseQueryString($query_string) {
    $array = array();
    foreach (explode('&', $query_string) as $param) {
      $pair = explode('=', $param, 2);
      if (count($pair) != 2) continue;
      $array[urldecode($pair[0])] = urldecode($pair[1]);
    }
    return $array;
  }

