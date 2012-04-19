<?php
class tc_dbmole extends tc_base{
	function test_uses_sequencies(){
		$this->assertFalse($this->my->usesSequencies());
		$this->assertTrue($this->ora->usesSequencies());
		$this->assertTrue($this->pg->usesSequencies());
	}

	function test_get_database_type(){
		$this->assertEquals("mysql",$this->my->getDatabaseType());
		$this->assertEquals("postgresql",$this->pg->getDatabaseType());
		$this->assertEquals("oracle",$this->ora->getDatabaseType());
		$this->assertEquals("unknown",$this->base->getDatabaseType());
	}

	function test_escape_string_4_sql(){
		$this->assertEquals("'o\\'neil \\\"da hacker\\\"'",$this->my->escapeString4Sql("o'neil \"da hacker\""));
		$this->assertEquals("'o''neil \"da hacker\"'",$this->pg->escapeString4Sql("o'neil \"da hacker\""));
	}

	function test_escape_bool_4_sql(){
		$this->assertEquals('TRUE',$this->pg->escapeBool4Sql(true));
		$this->assertEquals('FALSE',$this->pg->escapeBool4Sql(false));

		$this->assertEquals('TRUE',$this->my->escapeBool4Sql(true));
		$this->assertEquals('FALSE',$this->my->escapeBool4Sql(false));

		$this->assertEquals("Y",$this->ora->escapeBool4Sql(true));
		$this->assertEquals("N",$this->ora->escapeBool4Sql(false));
	}

	function test_parse_bool_from_sql(){
		$this->assertEquals(true,$this->pg->parseBoolFromSql("t"));
		$this->assertEquals(false,$this->pg->parseBoolFromSql("f"));

		$this->assertEquals(true,$this->my->parseBoolFromSql("1"));
		$this->assertEquals(false,$this->my->parseBoolFromSql("0"));
	}

	function test_common_behaviour(){
		$this->_test_common_behaviour($this->my);
		$this->_test_common_behaviour($this->pg);
		$this->_test_common_behaviour($this->ora);
	}

	function test_pgmole(){
		$dbmole = &$this->pg;
		$this->_test_select_sequence($dbmole);
	}

	function test_oraclemole(){
		$dbmole = &$this->ora;

		$this->_test_select_sequence($dbmole);

		$dbmole->insertIntoTable("test_table",array(
			"id" => "-123",
			"binary_data" => $this->_binary_data(),
		),array(
			"blobs" => array("binary_data")
		));

		$row = $dbmole->selectFirstRow("SELECT * FROM test_table WHERE id=:id",array(":id" => -123));
		$this->assertEquals(256,strlen($row["binary_data"]));
		$this->assertEquals($this->_binary_data(),$row["binary_data"]);

		$dbmole->insertIntoTable("test_table",array(
			"id" => "-124",
			"binary_data" => $this->_binary_data(),
			"binary_data2" => $this->_binary_data(1000),
			"text" => $this->_lorem_ipsum(),
		),array(
			"blobs" => array("binary_data","binary_data2"),
			"clobs" => array("text")
		));
		$row = $dbmole->selectFirstRow("SELECT * FROM test_table WHERE id=:id",array(":id" => -124));
		$this->assertEquals(256,strlen($row["binary_data"]));
		$this->assertEquals($this->_binary_data(),$row["binary_data"]);
		$this->assertEquals(1000*256,strlen($row["binary_data2"]));
		$this->assertEquals($this->_binary_data(1000),$row["binary_data2"]);
		$this->assertEquals($this->_lorem_ipsum(),$row["text"]);
	}

	function _test_common_behaviour(&$dbmole){
		$this->assertTrue($dbmole->doQuery("DELETE FROM test_table"));

		$this->_test_table_count($dbmole,0);

		$this->assertTrue($dbmole->insertIntoTable("test_table",array(
			"title" => "O'neil & daughter",
			"an_integer" => 11,
			"price" => 15.8,
			"text" => "\"O'neil has just 1 daughter\"",
			"create_date" => "2009-12-01",
			"flag" => false
		)));

		$this->_test_table_count($dbmole,1);

		$this->assertTrue($dbmole->insertIntoTable("test_table",array(
			"title" => "O'neil & sons",
			"an_integer" => 22,
			"price" => 33.3,
			"text" => "\"O'neil has 2 sons\"",
			"create_date" => "2009-12-31",
			"flag" => true
		)));

		$this->_test_table_count($dbmole,2);

		$this->assertTrue($dbmole->insertIntoTable("test_table",array(
			"title" => "O'neil & daughter",
			"an_integer" => 33,
			"price" => 15.8,
			"text" => "\"O'neil has just 1 daughter\"",
			"create_date" => "2009-12-31",
			"flag" => NULL,
		)));

		$this->_test_table_count($dbmole,3);
		
		$rows = $dbmole->selectRows("SELECT title,an_integer,price,text,create_date,flag FROM test_table ORDER BY an_integer");
		$rows[0]["create_date"] = preg_replace("/ .*$/","",$rows[0]["create_date"]); // 2009-12-31 00:00:00 -> 2009-12-31
		$rows[1]["create_date"] = preg_replace("/ .*$/","",$rows[1]["create_date"]);
		$rows[2]["create_date"] = preg_replace("/ .*$/","",$rows[2]["create_date"]);
		settype($rows[0]["price"],"float");
		settype($rows[1]["price"],"float");
		settype($rows[2]["price"],"float");

		$rows[0]['flag']=$dbmole->parseBoolFromSql($rows[0]['flag']);
		$rows[1]['flag']=$dbmole->parseBoolFromSql($rows[1]['flag']);
		$rows[2]['flag']=$dbmole->parseBoolFromSql($rows[2]['flag']);

		$this->assertEquals(array(
			array (
				'title' => "O'neil & daughter",
				'an_integer' => '11',
				'price' => 15.8,
				'text' => '"O\'neil has just 1 daughter"',
				'create_date' => '2009-12-01',
				'flag' => false
			),
			array (
				'title' => "O'neil & sons",
				'an_integer' => '22',
				'price' => 33.3,
				'text' => '"O\'neil has 2 sons"',
				'create_date' => '2009-12-31',
				'flag' => true,
			),
			array (
				'title' => "O'neil & daughter",
				'an_integer' => '33',
				'price' => 15.8,
				'text' => '"O\'neil has just 1 daughter"',
				'create_date' => '2009-12-31',
				'flag' => null,
			),
		),$rows);

		$row = $dbmole->selectFirstRow("SELECT title,an_integer,price,text,create_date FROM test_table ORDER BY an_integer DESC");
		$row["create_date"] = preg_replace("/ .*$/","",$row["create_date"]);
		$row["create_date"] = preg_replace("/ .*$/","",$row["create_date"]);
		settype($row["price"],"float");
		settype($row["price"],"float");
		$this->assertEquals(
			array (
				'title' => "O'neil & daughter",
				'an_integer' => '33',
				'price' => 15.8,
				'text' => '"O\'neil has just 1 daughter"',
				'create_date' => '2009-12-31',
			),
		$row);

		$ar = $dbmole->selectIntoArray("SELECT an_integer FROM test_table ORDER BY an_integer",array());
		$this->assertEquals(array("11","22","33"),$ar);

		$ar = $dbmole->selectIntoArray("SELECT an_integer FROM test_table ORDER BY an_integer",array(),array("limit" => 1));
		$this->assertEquals(array("11"),$ar);

		$ar = $dbmole->selectIntoArray("SELECT an_integer FROM test_table ORDER BY an_integer",array(),array("limit" => 1, "offset" => 0));
		$this->assertEquals(array("11"),$ar);

		$ar = $dbmole->selectIntoArray("SELECT an_integer FROM test_table ORDER BY an_integer",array(),array("limit" => 1, "offset" => 1));
		$this->assertEquals(array("22"),$ar);

		$ar = $dbmole->selectIntoArray("SELECT an_integer FROM test_table ORDER BY an_integer",array(),array("limit" => 1, "offset" => 2));
		$this->assertEquals(array("33"),$ar);

		$ar = $dbmole->selectIntoArray("SELECT an_integer FROM test_table ORDER BY an_integer",array(),array("limit" => 1, "offset" => 3));
		$this->assertEquals(array(),$ar);

		$ar = $dbmole->selectIntoArray("SELECT an_integer FROM test_table WHERE an_integer=-an_integer");
		$this->assertEquals(array(),$ar);

		$ar = $dbmole->selectIntoAssociativeArray("SELECT an_integer,an_integer+1 FROM test_table ORDER BY an_integer",array());
		$this->assertEquals(array("11" => "12","22" => "23","33" => "34"),$ar);

		$ar = $dbmole->selectIntoAssociativeArray("SELECT an_integer,an_integer+1 as f1, an_integer+2 as f2 FROM test_table ORDER BY an_integer",array());
		$this->assertEquals(array(
			"11" => array("f1" => "12", "f2" => "13"),
			"22" => array("f1" => "23", "f2" => "24"),
			"33" => array("f1" => "34", "f2" => "35")
		),$ar);

		$ar = $dbmole->selectIntoAssociativeArray("SELECT an_integer,an_integer+1 as f1, an_integer+2 as f2, null as f3 FROM test_table ORDER BY an_integer",array());
		$this->assertEquals(array(
			"11" => array("f1" => "12", "f2" => "13", "f3" => null),
			"22" => array("f1" => "23", "f2" => "24", "f3" => null),
			"33" => array("f1" => "34", "f2" => "35", "f3" => null)
		),$ar);

		// je docela dobry nesmysl vybirat do asociativniho pole jeden sloupec
		$ar = $dbmole->selectIntoAssociativeArray("SELECT an_integer FROM test_table ORDER BY an_integer",array());
		$this->assertEquals(array(
			"11" => array(),
			"22" => array(),
			"33" => array(),
		),$ar);

		$this->assertTrue($dbmole->doQuery("UPDATE test_table SET an_integer=44 WHERE an_integer=22"));

		$this->assertEquals(1,$dbmole->getAffectedRows(),"calling getAffectedRows() on ".$dbmole->getDatabaseType());

		$this->assertEquals("44",$dbmole->selectSingleValue("SELECT MAX(an_integer) FROM test_table"));

		// do_not_escape
		$this->assertTrue($dbmole->insertIntoTable("test_table",array(
			"id" => -333,
			"an_integer" => "22-12",
			"text" => "testing"
		),array(
			"do_not_escape" => "an_integer"
		)));
		$row = $dbmole->selectFirstRow("SELECT an_integer,text FROM test_table WHERE id=-333");
		$this->assertEquals(array("an_integer" => "10","text" => "testing"),$row);

		$this->assertTrue($dbmole->insertIntoTable("test_table",array(
			"id" => -444,
			"an_integer" => "23-12",
			"text" => "testing 2"
		),array(
			"do_not_escape" => array("an_integer")
		)));
		$row = $dbmole->selectFirstRow("SELECT an_integer,text FROM test_table WHERE id=-444");
		$this->assertEquals(array("an_integer" => "11","text" => "testing 2"),$row);


		$ints = $dbmole->selectIntoArray("SELECT an_integer FROM test_table WHERE an_integer IN :ints ORDER BY an_integer",array(
			":ints" => array(10,33),
		));

		$this->assertEquals(array("10","33"),$ints);
	}

	function _test_select_sequence(&$dbmole){
		$s1 = (int)$dbmole->selectSequenceNextval("test_table_id_seq");
		$s2 = (int)$dbmole->selectSequenceNextval("test_table_id_seq");
		$s3 = (int)$dbmole->selectSequenceCurrval("test_table_id_seq");

		$this->assertTrue($s2>$s1);
		$this->assertTrue($s3==$s2);
	}

	function _test_table_count(&$dbmole,$expected_count){
		$q = "SELECT COUNT(*) FROM test_table";

		$this->_test_string($dbmole->selectSingleValue($q),"$expected_count");

		$this->_test_integer($dbmole->selectSingleValue($q,"integer"),(int)$expected_count);
		$this->_test_integer($dbmole->selectSingleValue($q,array(),"integer"),(int)$expected_count);
		$this->_test_integer($dbmole->selectSingleValue($q,array(),array("type" => "integer")),(int)$expected_count);

		$this->_test_float($dbmole->selectSingleValue($q,"float"),(float)$expected_count);
		$this->_test_float($dbmole->selectSingleValue($q,array(),"float"),(float)$expected_count);
		$this->_test_float($dbmole->selectSingleValue($q,array(),array("type" => "float")),(float)$expected_count);
	}

	function _test_integer($i,$expected_val,$msg = ""){
		$this->assertTrue(is_integer($i),$msg);
		$this->assertEquals($expected_val,$i);
	}

	function _test_float($f,$expected_val,$msg = ""){
		$this->assertTrue(is_float($f),$msg);
		$this->assertEquals($expected_val,$f);
	}

	function _test_string($i,$expected_val,$msg = ""){
		$this->assertTrue(is_string($i),$msg);
		$this->assertEquals($expected_val,$i);
	}



	function _binary_data($repeated = 1){
		$out = array();

		for($i=0;$i<=255;$i++){
			$out[] = chr($i);
		}
		return str_repeat(join("",$out),$repeated);
	}

	function _lorem_ipsum(){
		return 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. Typi non habent claritatem insitam; est usus legentis in iis qui facit eorum claritatem. Investigationes demonstraverunt lectores legere me lius quod ii legunt saepius. Claritas est etiam processus dynamicus, qui sequitur mutationem consuetudium lectorum. Mirum est notare quam littera gothica, quam nunc putamus parum claram, anteposuerit litterarum formas humanitatis per seacula quarta decima et quinta decima. Eodem modo typi, qui nunc nobis videntur parum clari, fiant sollemnes in futurum.';
	}
}