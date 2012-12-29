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
if(class_exists("Mongo")) {
    if(!class_exists('Account')) {
        class Account extends DatajarBase
        {
            // Storable fields.
            protected $balance;
            protected $interest;
            protected $owners;

            protected function type_init()
            {
                $this->balance = DatajarType::float();
                $this->interest = DatajarType::float();
            }
        }
    }

    if(!class_exists('Owner')) {
        class Owner extends DatajarBase
        {
            protected $name;
            protected $dob;
            protected $account;

            protected function type_init()
            {
                $this->name = DatajarType::varchar(256);
                $this->dob = DatajarType::date();
                $this->foreignkey('account', 'Account');
            }
        }
    }

    class TestMongo
    {
        private $host = "localhost";
        private $schema = "datajar";
        private $saved_account_id;

        function __construct()
        {
            datajar_load_driver('mongo');
            $this->_wipe();
        }

        private function _wipe()
        {
            if(!isset($this->db)) {
                $this->mongo = new Mongo("mongodb://".$this->host);
                $this->db = new MongoDB($this->mongo, $this->schema);
                $this->account_col = new MongoCollection($this->db, 'Account');
            }

            $colls = $this->db->listCollections();
            foreach($colls as $col) {
                $col->drop();
            }

            if(!isset($this->sdb)) {
                $this->sdb = new DatajarEngineMongo("mongodb://".$this->host."/".$this->schema);
                DatajarBase::bind($this->sdb);
            }
        }

        function testSave()
        {
            $account = new Account();
            $account->balance = 100;
            $account->interest = 0.025;
            $account->save();
            //$this->saved_account_id = $account->id;

            $count = $this->account_col->find(array('balance' => 100))->count();
            ut_equals($count, 1);
        }

        function testLoad()
        {
            $account = new Account();
            $account->load(array('id' => $this->saved_account_id));
            ut_equals($account->balance, 100);
            ut_equals($account->interest, 0.025);
        }

        function testDelete()
        {
	    $this->_wipe();
	    $account = new Account();
            $account->balance = 100;
            $account->interest = 0.025;
            $account->save();
            $count = $this->account_col->find(array('balance' => 100))->count();
	    $saved_ok = $count > 0;

	    $account->delete();
	    $count = $this->account_col->find(array('balance' => 100))->count();
	    $deleted_ok = $count == 0;

	    ut_assert($saved_ok && $deleted_ok);
        }

        function testSelect()
        {
            $this->_wipe();

            // Inserting two accounts.
            $acc1 = new Account(array('balance' => 100, 'interest' => 0.015));
            $acc2 = new Account(array('balance' => 100, 'interest' => 0.025));

            $acc1->create();
            $acc1->save();
            $acc2->save();

            $objs = $this->sdb->select('Account', array('balance' => 100));

            ut_equals(count($objs), 2);
            // acc1
            ut_equals($objs[0]->balance, 100);
            ut_equals($objs[0]->interest, 0.015);

            // acc2
            ut_equals($objs[1]->balance, 100);
            ut_equals($objs[1]->interest, 0.025);
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

            $objs = Account::select(array(), "balance");
            ut_assert($objs[0]->balance < $objs[1]->balance);

            $objs = Account::select(array(), "balance", true);
            ut_assert($objs[0]->balance > $objs[1]->balance);

            $objs = Account::select(array('interest' => 0.015), "balance");
            ut_assert($objs[0]->balance < $objs[1]->balance);

            $query = Account::query()->where(array('interest' => 0.015))->orderby("balance");
            $objs = Account::run_query($query);
            ut_assert($objs[0]->balance < $objs[1]->balance);

            $objs = Account::select(array('interest' => 0.015), "balance", true);
            ut_assert($objs[0]->balance > $objs[1]->balance);

            $query = Account::query()->where(array('interest' => 0.015))->orderby("balance", true);
            $objs = Account::run_query($query);
            ut_assert($objs[0]->balance > $objs[1]->balance);
        }

        function testLimit()
        {
            $this->_wipe();

            $acc1 = new Account(array('balance' => 100, 'interest' => 0.015));
            $acc2 = new Account(array('balance' => 200, 'interest' => 0.015));
            $acc3 = new Account(array('balance' => 300, 'interest' => 0.015));
            $acc4 = new Account(array('balance' => 400, 'interest' => 0.015));
            $acc5 = new Account(array('balance' => 500, 'interest' => 0.015));
            $acc1->create();
            $acc1->save();
            $acc2->save();
            $acc3->save();
            $acc4->save();
            $acc5->save();

            $objs = Account::select(array(), false, false, array(0, 2));
            ut_equals(count($objs), 2);
            ut_equals($objs[0]->balance, $acc1->balance);
            ut_equals($objs[1]->balance, $acc2->balance);

            $objs = Account::select(array(), false, false, array(2, 2));
            ut_equals(count($objs), 2);
            ut_equals($objs[0]->balance, $acc3->balance);
            ut_equals($objs[1]->balance, $acc4->balance);

            $objs = Account::select(array(), false, false, array(4, 1));
            ut_equals(count($objs), 1);
            ut_equals($objs[0]->balance, $acc5->balance);
        }

        function testDrop()
        {
	    $account = new Account();
	    $account->balance = 100;
            $account->interest = 0.025;
	    $account->save();
	    $saved_ok = $this->account_col->find(array('balance' => 100))->count() > 0;

	    $account->drop();
	    $deleted_ok = $this->account_col->find(array('balance' => 100))->count() == 0;

	    ut_assert($saved_ok && $deleted_ok);
        }
    }
}

?>
