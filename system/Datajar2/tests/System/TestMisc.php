<?php

class TestMisc
{
    function testVersion()
    {
        ut_equals(datajar_version(), '0.01');
    }

    function testBackend() {}

    function testBackends()
    {
        $backends = datajar_test_backends();

        // Testing the drivers.
        ut_equals($backends['mysql'], function_exists('mysql_connect'));
        ut_equals($backends['sqlite'], class_exists('SQLite3'));
        ut_equals($backends['mongo'], class_exists('Mongo'));
    }
}

?>
