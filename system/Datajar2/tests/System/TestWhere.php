<?php

class TestWhere
{
    function testSimple()
    {
        $clause = array("foo" => "bar");
        $where = DatajarSQL::gen_where($clause);
        ut_whitespace("?foo?=?'bar'?", $where);
    }

    function testAnd()
    {
        $clause = array('foo' => 'bar', 'bar' => 'baz');
        $where = DatajarSQL::gen_where($clause);
        ut_whitespace("?foo?=?'bar' ?AND ?bar?=?'baz'?", $where);

        $clause = array('foo' => array('bar', 'baz'));
        $where = DatajarSQL::gen_where($clause);
        ut_whitespace("?foo?=?'bar' ?AND ?foo?=?'baz'?", $where);
    }

    function testOr()
    {
        $clause = array('foo' => 'bar', '|bar' => 'baz');
        $where = DatajarSQL::gen_where($clause);
        ut_whitespace("?foo?=?'bar' ?OR ?bar?=?'baz'?", $where);

        $clause = array('foo' => array('bar', '|baz'));
        $where = DatajarSQL::gen_where($clause);
        ut_whitespace("?foo?=?'bar' ?OR ?foo?=?'baz'?", $where);
    }

    function testOperators()
    {
        $where = DatajarSQL::gen_where(array('foo >' => 3));
        ut_whitespace("?foo?>?'3'?", $where);

        $where = DatajarSQL::gen_where(array('foo >=' => 3));
        ut_whitespace("?foo?>=?'3'?", $where);

        $where = DatajarSQL::gen_where(array('foo <' => 3));
        ut_whitespace("?foo?<?'3'?", $where);

        $where = DatajarSQL::gen_where(array('foo <=' => 3));
        ut_whitespace("?foo?<=?'3'?", $where);

        $where = DatajarSQL::gen_where(array('foo !' => 3));
        ut_whitespace("?foo?!=?'3'?", $where);

        $where = DatajarSQL::gen_where(array('foo %' => '%ba%r'));
        ut_whitespace("?foo?LIKE?'%ba%r'?", $where);
    }

    function testVerbatim()
    {
        $where = DatajarSQL::gen_where(array('foo' => '@bar'), '`');
        ut_whitespace("?`foo` bar?", $where);

        $where = DatajarSQL::gen_where(array('foo' => '#bar'), '`');
        ut_whitespace("?`foo`?=?bar?", $where);
    }

    function testRecursive()
    {
        $query = array('foo' => 'bar',
                       array('foo' => 'baz', '|bar >' => 3));
        $where = DatajarSQL::gen_where($query);
        ut_whitespace("?foo?=?'bar' ?AND ?(?foo?=?'baz' ?OR ?bar?>?'3'?)?", $where);

        $query = array('foo' => 'bar',
                       '|' => array('foo' => 'baz', 'bar >' => 3));
        $where = DatajarSQL::gen_where($query);
        ut_whitespace("?foo?=?'bar' ?OR ?(?foo?=?'baz' ?AND ?bar?>?'3'?)?", $where);
    }
}

?>
