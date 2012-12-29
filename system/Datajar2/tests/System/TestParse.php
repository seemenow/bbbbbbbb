<?php

class TestParse extends DatajarEngineBase
{
    function testMySQL()
    {
        $conn = $this->parse_conn_string("mysql://user:password@host:1337/database");
        ut_aarray_equals($conn, array('username' => 'user',
                                      'password' => 'password',
                                      'host' => 'host',
                                      'port' => '1337',
                                      'database' => 'database'));
        $conn = $this->parse_conn_string("mysql://user:password@host/database");
        ut_aarray_equals($conn, array('username' => 'user',
                                      'password' => 'password',
                                      'host' => 'host',
                                      'port' => '',
                                      'database' => 'database'));
        $conn = $this->parse_conn_string("mysql://user:password@host:1337/database");
        ut_aarray_differs($conn, array('username' => 'user',
                                       'password' => 'password',
                                       'host' => 'host',
                                       'port' => '',
                                       'database' => 'database'));
    }

    function testSQLite()
    {
        $conn = $this->parse_conn_string("sqlite:///database");
        ut_aarray_equals($conn, array('username' => '',
                                      'password' => '',
                                      'host' => '',
                                      'port' => '',
                                      'database' => 'database'));
    }

    function create($object) {}
    function save($object) {}
    function delete($object) {}
    function load($object, array $cond) {}
    function select($objecttype, array $cond, $order = false, $desc = false) {}
    function run($query) {}
    function drop($object) {}
    function close() {}
    static function test_backend() {}
}

?>
