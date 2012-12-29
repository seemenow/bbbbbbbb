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

if(class_exists("SQLite3")) {
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
            protected $balance;
            protected $interest;

            protected function type_init()
            {
                $this->balance = DatajarType::float();
                $this->interest = DatajarType::float();
            }
        }
    }

    class TestSqlite
    {
        private $db_file;

        function __construct()
        {
            datajar_load_driver('sqlite');
            $this->db_file = ut_res('tests.db');
            $this->_wipe();
        }

        private function _wipe()
        {
            if(isset($this->sdb))
                unset($this->sdb);
            if(isset($this->db))
                unset($this->db);

            unlink($this->db_file);
            $this->sdb = new DatajarEngineSqlite("sqlite:///" . $this->db_file);
            $this->db = new SQLite3($this->db_file);
            DatajarBase::bind($this->sdb);
        }

        function testCreate()
        {
            $test = new Account();
            $this->sdb->create($test);

            $numtables = $this->db->querySingle(
                'SELECT count(name) as count FROM sqlite_master WHERE type="table" AND name="Account"');
            ut_equals($numtables, 1);

            $this->_wipe();
            $test->create();
            $numtables = $this->db->querySingle(
                'SELECT count(name) as count FROM sqlite_master WHERE type="table" AND name="Account"');
            ut_equals($numtables, 1);
        }

        function testPopulate()
        {
            $vals = array('balance' => 50, 'interest' => 0.01);

            $test = new Account();
            $test->populate($vals);
            ut_equals($test->balance, 50);
            ut_equals($test->interest, 0.01);

            $account = new Account($vals);
            ut_equals($account->balance, 50);
            ut_equals($account->interest, 0.01);
        }

        function testSave()
        {
            $account = new Account();
            $account->balance = 100;
            $account->interest = 0.025;
            $this->sdb->save($account);

			// Checking that it exists.
            $count = $this->db->querySingle(
                'SELECT count(*) as count FROM Account '.
                'WHERE balance="100" AND interest="0.025"');
            ut_equals($count, 1);

			// Getting the number of rows for later.
            $rows = $this->db->querySingle('SELECT count(*) as count FROM Account');

            $account->balance = 200;
            $account->interest = 0.015;
            $account->save();

            $count = $this->db->querySingle(
                'SELECT count(*) as count FROM Account '.
                'WHERE balance="200" AND interest="0.015"');
            ut_equals($count, 1);

			$count = $this->db->querySingle('SELECT count(*) as count FROM Account');
			ut_equals($count, $rows);
        }

        function testLoad()
        {
            $account = new Account();
            $this->sdb->load($account, array('id' => 1));
            ut_equals($account->balance, 200);
            ut_equals($account->interest, 0.015);

            $account = null;
            $account = new Account();
            $account->load(array('id' => 1));
            ut_equals($account->balance, 200);
            ut_equals($account->interest, 0.015);
        }

        function testDelete()
        {
            $account = new Account();
            $account->balance = 200;
            $account->interest = 0.020;
            $this->sdb->save($account);

            $count = $this->db->querySingle(
                'SELECT count(*) as count FROM Account '.
                'WHERE balance="200" AND interest="0.020"');
            ut_equals($count, 1);

            $this->sdb->delete($account);

            $count = $this->db->querySingle(
                'SELECT count(*) as count FROM Account '.
                'WHERE balance="200" AND interest="0.020"');
            ut_equals($count, 0);

            ut_nassert($account->id);

            $account = null;
            $account = new Account();
            $account->balance = 200;
            $account->interest = 0.020;
            $account->save();

            $count = $this->db->querySingle(
                'SELECT count(*) as count FROM Account '.
                'WHERE balance="200" AND interest="0.020"');
            ut_equals($count, 1);

            $account->delete();

            $count = $this->db->querySingle(
                'SELECT count(*) as count FROM Account '.
                'WHERE balance="200" AND interest="0.020"');
            ut_equals($count, 0);

            ut_nassert($account->id);
        }

        function testDrop()
        {
            $numtables = $this->db->querySingle(
                'SELECT count(name) as count FROM sqlite_master WHERE type="table" AND name="Account"');
            ut_equals($numtables, 1);

            $account = new Account();
            $account->balance = 200;
            $account->interest = 0.020;
            $this->sdb->save($account);
            ut_differs($account->id, false);

            $this->sdb->drop($account);

            $numtables = $this->db->querySingle(
                'SELECT count(name) as count FROM sqlite_master WHERE type="table" AND name="Account"');
            ut_equals($numtables, 0);

            ut_nassert($account->id);
        }

        function testSelect()
        {
            // Wiping
            $this->_wipe();

            // Inserting two accounts.
            $acc1 = new Account(array('balance' => 100, 'interest' => 0.015));
            $acc2 = new Account(array('balance' => 200, 'interest' => 0.015));

            $acc1->create();
            $acc1->save();
            $acc2->save();

			$objs = Account::select();
			ut_equals(count($objs), 2);

            $objs = Account::select(array('interest>' => 0.014));
            ut_equals(count($objs), 2);

            $objs = Account::run_query(Account::query()->where(array('interest>' => 0.014)));
            ut_equals(count($objs), 2);

            // acc1
            ut_equals($objs[0]->balance, 100);
            ut_equals($objs[0]->interest, 0.015);

            // acc2
            ut_equals($objs[1]->balance, 200);
            ut_equals($objs[1]->interest, 0.015);
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
            $acc1->create();
            $acc1->save();
            $acc2->save();

            $objs = Account::select(null, "balance");
            ut_assert($objs[0]->balance < $objs[1]->balance);

            $objs = Account::select(null, "balance", true);
            ut_assert($objs[0]->balance > $objs[1]->balance);

            $objs = Account::select(array('interest>' => 0.014), "balance");
            ut_assert($objs[0]->balance < $objs[1]->balance);

            $query = Account::query()->where(array('interest>' => 0.014))->orderby("balance");
            $objs = Account::run_query($query);
            ut_assert($objs[0]->balance < $objs[1]->balance);

            $objs = Account::select(array('interest>' => 0.014), "balance", true);
            ut_assert($objs[0]->balance > $objs[1]->balance);

            $query = Account::query()->where(array('interest>' => 0.014))->orderby("balance", true);
            $objs = Account::run_query($query);
            ut_assert($objs[0]->balance > $objs[1]->balance);
        }

        function testLimit()
        {
            // Wiping
            $this->_wipe();

            // Inserting two accounts.
            $acc1 = new Account(array('balance' => 100, 'interest' => 0.015));
            $acc2 = new Account(array('balance' => 200, 'interest' => 0.015));

            $acc1->create();
            $acc1->save();
            $acc2->save();

            $objs = Account::run_query(Account::query()->select()->limit(0, 1));
            ut_equals(count($objs), 1);
        }

        function __destruct()
        {
        }
    }
}

?>
