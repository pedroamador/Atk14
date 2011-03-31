<?php
class tc_string extends tc_base{
	function test_get_id(){
		$s = new String("Hi");
		$this->assertTrue(is_string($s->getId()));
		$this->assertEquals("Hi",$s->getId());
	}

	function test_random_string(){
		$s1 = String::RandomString();
		$s2 = String::RandomString();
		$s3 = String::RandomString(22);

		$this->assertEquals(32,strlen($s1));
		$this->assertEquals(32,strlen($s2));
		$this->assertEquals(22,strlen($s3));

		$this->assertTrue($s1!=$s2);
	}

	function test_instance(){
		$string = "Hello World";
		$stringer = new String($string);

		$this->assertTrue(is_object($stringer));
		$this->assertFalse(is_object($string));

		$this->assertEquals("$string","$stringer");
		$this->assertEquals(11,strlen($stringer));

		// String::ToObject()
		$string = String::ToObject("Hello World");
		$this->assertTrue(is_object($string));
		$string2 = String::ToObject($string); 
		$this->assertTrue(is_object($string2));
		
	}

	function test_clone_and_copy(){
		$orig = new String("Hello World");
		$clone = clone $orig;
		$copy = $orig->copy();

		$orig->replace("Hello","Hi");

		$this->assertEquals("Hi World",(string)$orig);
		$this->assertEquals("Hello World",(string)$clone);
		$this->assertEquals("Hello World",(string)$copy);
	}

	function test_replace(){
		$str = new String("Hello World");
		$this->assertEquals("Hello Guys",(string)$str->replace("World","Guys"));

		$str = new String("Hello World");
		$this->assertEquals("Hi Guys",(string)$str->replace(array(
			"Hello" => "Hi",
			"World" => "Guys",
		)));

		$str = new String("Hello World");
		$this->assertEquals("Hello World",(string)$str->replace(array()));
	}

	function test_prepend_and_append(){
		$string = new String("World");
		$this->assertEquals("Hello World",(string)$string->prepend("Hello "));
		$this->assertEquals("Hello World",(string)$string);

		$string = new String("Hi");
		$this->assertEquals("Hi World",(string)$string->append(" World"));
		$this->assertEquals("Hi World",(string)$string);
	}

	function test_match(){
		$domain = new String("domain.cz");
		$this->assertEquals(true,(bool)$domain->match("/.*\\.cz$/"));
		$this->assertEquals(false,(bool)$domain->match("/.*\\.sk$/"));
		
		$domain->match("/(.*)\\.cz$/",$matches);
		$this->assertEquals("domain",(string)$matches[1]);
	}

	function test_at(){
		$str = new String("Hello");
		$this->assertEquals("H",(string)$str->at(0));
		$this->assertEquals("e",(string)$str->at(1));
		$this->assertEquals("o",(string)$str->at(-1));

		$this->assertEquals("",(string)$str->at(10));
	}

	function test_contains(){
		$str = new String("Hello");
		$this->assertTrue($str->contains("ll"));
		$this->assertTrue($str->contains("lo"));
		$this->assertTrue($str->contains("He"));
		$this->assertTrue($str->contains("Hello"));
		$this->assertFalse($str->contains("HELLO"));

		$this->assertFalse($str->contains(new String("HELLO")));
		$this->assertTrue($str->contains(new String("Hello")));
	}

	function test_camelize(){
		foreach(array(
			"hello_world" => "HelloWorld",
			"hello_123" => "Hello123",
			"a_b_c_d" => "ABCD",
		) as $str => $result){
			$str = new String($str);
			$this->assertEquals($result,(string)$str->camelize());
		}

		$str = String::ToObject("hello_world");
		$this->assertEquals("helloWorld",(string)$str->camelize(array("lower" => true)));
		$this->assertEquals("HelloWorld",(string)$str->camelize());
	}

	function test_underscore(){
		foreach(array(
			"HelloWorld" => "hello_world",
			"ABCD" => "abcd",
			"Hello123" => "hello123",
			"123Hello" => "123_hello",
		) as $str => $result){
			$str = new String($str);
			$this->assertEquals($result,(string)$str->underscore());
		}	
	}

	function test_tableize(){
		foreach(array(
			"Book" => "books",
			"BlogPost" => "blog_posts",
			"Sheep" => "sheep",
			"Person" => "people",
		) as $class_name => $table_name){
			$str = new String($class_name);
			$this->assertEquals($table_name,(string)$str->tableize());
		}	
	}

	function test_pluralize_and_singularize(){
		foreach(array(
			"apple" => "apples",
			"Apple" => "Apples",
			"rotten apple" => "rotten apples",
			"Rotten Apple" => "Rotten Apples",
			"RottenApple" => "RottenApples",
			"rotten_apple" => "rotten_apples",

			"sheep" => "sheep",
			"man" => "men",
			"virus" => "viruses",
		) as $singular => $plural){
			$str = new String($singular);
			$this->assertEquals($plural,(string)$str->pluralize());

			$str = new String($plural);
			$this->assertEquals($singular,(string)$str->singularize());
		}
	}
}