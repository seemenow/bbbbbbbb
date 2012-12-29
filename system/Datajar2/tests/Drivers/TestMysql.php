<?php

/**
 * @file TestDatajar.php
 * This file is part of Movim.
 *
 * @brief Tests the Datajar module.
 *
 * @author Guillaume Pasquet <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date 27 April 2011
 *
 * Copyright (C)2011 Movim Project.
 *
 * %license%
 */
if(function_exists("mysql_connect")) {
    define('DB_DEBUG', true);
    define('DB_LOGFILE', 'queries.log');

    if(!class_exists('Account')) {
        class Account extends DatajarBase
        {
            // Storable fields.
            protected $balance;
            protected $interest;

            protected function type_init()
            {
                $this->balance = DatajarType::float();
                $this->interest = DatajarType::float();
            }
        }
    }

    if(!class_exists('Mortgage')) {
        class Mortgage extends DatajarBase
        {
            // Storable fields.
            protected $balance;
            protected $interest;

            protected function type_init()
            {
                $this->balance = DatajarType::float();
                $this->interest = DatajarType::float();
            }
        }
    }

    class TestMysql
    {
        private $host = "localhost";
        private $port = "3366";
        private $username = "datajar";
        private $password = "datajar";
        private $schema = "datajar";

        function __construct()
        {
            datajar_load_driver('mysql');
            $this->_wipe();
        }

        private function _wipe($drop = false)
        {
            if(!isset($this->db)) {
                $this->db = mysql_connect($this->host.':'.$this->port,
                                          $this->username,
                                          $this->password);
                mysql_select_db($this->schema, $this->db);
            }

            $result = mysql_query("show tables from `".$this->schema."`", $this->db);
            while($row = mysql_fetch_row($result)) {
                mysql_query("delete from `".$row[0]."`", $this->db);
                if($drop) {
                    mysql_query("drop table `".$row[0]."`", $this->db);
                }
            }

            if(!isset($this->sdb)) {
                $this->sdb = new DatajarEngineMysql("mysql://".$this->username.":".$this->password."@".$this->host.":".$this->port."/".$this->schema);
                DatajarBase::bind($this->sdb);
            }
        }

        private function _count($table, $where)
        {
            $query = "SELECT count(*) as count FROM $table WHERE $where";
            $res = mysql_query($query, $this->db);
            $row = mysql_fetch_assoc($res);
            return $row['count'];
        }

        function testCreate()
        {
            $this->_wipe(true);
            $test = new Account();
            $this->sdb->create($test);

            $numtables = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'Account'", $this->db));
            ut_equals($numtables, 1);
        }

        function testSave()
        {
            $this->_wipe();
            $account = new Account();
            $account->balance = 100;
            $account->interest = 0.025;
            $this->sdb->save($account);

            $count = $this->_count('Account', "balance='100'");
            ut_equals($count, 1);
        }
        function testLoad()
        {
            $this->_wipe();
            $account = new Account();
            $account->balance = 100;
            $account->interest = 0.025;
            $this->sdb->save($account);
            $id = $account->id;
            
            $account = new Account();
            $this->sdb->load($account, array('id' => $id));
            ut_equals($account->balance, 100);
            ut_equals($account->interest, 0.025);
        }

        function testDelete()
        {
            $this->_wipe();
            $account = new Account();
            $account->balance = 200;
            $account->interest = 0.020;
            $this->sdb->save($account);

            $count = $this->_count('Account', "balance='200'");
            ut_equals($count, 1);

            $this->sdb->delete($account);

            $count = $this->_count('Account', "balance='200'");
            ut_nassert($account->id);
        }

        function testDrop()
        {
            $this->_wipe();
            $numtables = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'Account'", $this->db));
            ut_equals($numtables, 1);

            $account = new Account();
            $account->balance = 200;
            $account->interest = 0.020;
            $this->sdb->save($account);
            ut_differs($account->id, false);

            $this->sdb->drop($account);

            $numtables = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'Account'", $this->db));
            ut_equals($numtables, 0);
            ut_nassert($account->id);
        }

        function testSelect()
        {
            // Wiping
            $this->_wipe();

            // Inserting two accounts.
            $acc1 = new Account(array('balance' => 100, 'interest' => 0.015));
            $acc2 = new Account(array('balance' => 100, 'interest' => 0.025));

            $this->sdb->create($acc1);
            $this->sdb->save($acc1);
            $this->sdb->save($acc2);

            $objs = $this->sdb->select('Account', array('balance' => 100));

            ut_equals(count($objs), 2);

            // acc1
            ut_equals($objs[0]->balance, 100);
            ut_equals($objs[0]->interest, 0.015);

            // acc2
            ut_equals($objs[1]->balance, 100);
            ut_equals($objs[1]->interest, 0.025);
        }

		function testJoin()
		{
			$this->_wipe();

			$acc = new Account(array('balance' => 100, 'interest' => 0.015));
            $acc->create();
			$acc->save();
			
			$mort = new Mortgage(array('balance' => 200, 'interest' => 0.015));
            $mort->create();
			$mort->save();

            $objs = Account::run_query(Account::query()
                                       ->join('Mortgage',
                                              array('Account.interest' =>
                                                    'Mortgage.interest')));

			ut_equals(count($objs), 1); // One row!
			ut_equals(count($objs[0]), 2); // Two objects :D

			ut_equals($objs[0][0]->balance, 100);
			ut_equals($objs[0][1]->balance, 200);
		}

        function testOrder()
        {
            // Wiping
            $this->_wipe();

            // Inserting two accounts.
            $acc1 = new Account(array('balance' => 100, 'interest' => 0.015));
            $acc2 = new Account(array('balance' => 200, 'interest' => 0.015));
            $this->sdb->create($acc1);
            $this->sdb->save($acc1);
            $this->sdb->save($acc2);

            $objs = $this->sdb->select("Account", array(), "balance");
            ut_assert($objs[0]->balance < $objs[1]->balance);

            $objs = $this->sdb->select("Account", array(), "balance", true);
            ut_assert($objs[0]->balance > $objs[1]->balance);

            $objs = $this->sdb->select("Account", array('interest' => 0.015), "balance");
            ut_assert($objs[0]->balance < $objs[1]->balance);

            $objs = $this->sdb->select("Account", array('interest' => 0.015), "balance", true);
            ut_assert($objs[0]->balance > $objs[1]->balance);
        }
    }
}

?>
