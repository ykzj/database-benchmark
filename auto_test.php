<?php
	//database parameters, RDS or On-Promise
	$db_host = '';
	$db_user = '';
	$db_pass = '';
	$db_name = '';
	$db_port = 3306;

	//use sysbench to prepare database first!
	$table_count = 16;
	$table_size = 100000000;

	//test threads, can/should be different on different workload
	$test_threads_readonly = array(1, 5, 10, 20, 40, 50);
	$test_threads_writeheavy = array(1, 5, 10, 20, 40, 50);
	$test_threads_mixed = array(1, 5, 10, 20, 40, 50);

	//test iterate for each testing
	$test_iterate = 3;
	//test duration in second
	$test_duration = 600;
	//sleep between each test
	$test_sleep_time = 60;

	//sysbench installation dir
	$sysbench_dir = '/home/ec2-user/sysbench/sysbench/';
	//test result log file
	$log_file = '/tmp/auto_test.log';

	if(is_file($log_file))
	{
		unlink($log_file);
	}
	

	foreach($test_threads_readonly as $thread)
	{
		for($i=0; $i<$test_iterate; $i++)
		{
			$j = $i + 1;
			testlog("\n=[" . date("Y-m-d H:i:s") . "] $db_host readonly test, $thread thread, iterate $j, started=\n", $log_file);

			$cmd_readonly = "${sysbench_dir}/sysbench --test=${sysbench_dir}/tests/db/oltp.lua --mysql-host=$db_host --oltp-tables-count=$table_count --mysql-user=$db_user --mysql-password=$db_pass --mysql-port=$db_port --db-driver=mysql --oltp-tablesize=$table_size --mysql-db=$db_name --max-requests=0 --oltp_simple_ranges=0 --oltp-distinct-ranges=0 --oltp-sum-ranges=0 --oltp-order-ranges=0 --max-time=$test_duration --oltp-read-only=on --num-threads=$thread run";
			exec($cmd_readonly, $output);
			$result = implode("\n", $output);

			testlog("\n[". date("Y-m-d : H:i:s") ."]test results: \n $result \n\n", $log_file);
			testlog("sleeping $test_sleep_time seconds.\n", $log_file);
			sleep($test_sleep_time);
		}

	}
	foreach($test_threads_writeheavy as $thread)
	{
		for($i=0; $i<$test_iterate; $i++)
		{
			$j = $i + 1;
			testlog("\n=[" . date("Y-m-d H:i:s") . "] $db_host writeheavy test, $thread thread, iterate $j, started=\n", $log_file);

			$cmd_writeheavy = "${sysbench_dir}/sysbench --test=${sysbench_dir}/tests/db/oltp.lua --mysql-host=$db_host --oltp-tables-count=$table_count --mysql-user=$db_user --mysql-password=$db_pass --mysql-port=$db_port --db-driver=mysql --oltp-tablesize=$table_size --mysql-db=$db_name --max-requests=0 --max-time=$test_duration --oltp_simple_ranges=0 --oltp-distinct-ranges=0 --oltp-sum-ranges=0 --oltporder-ranges=0 --oltp-point-selects=0 --num-threads=$thread --randtype=uniform run";
			
			exec($cmd_writeheavy, $output);
			$result = implode("\n", $output);

			testlog("\n[". date("Y-m-d : H:i:s") ."]test results: \n $result \n\n", $log_file);
			testlog("sleeping $test_sleep_time seconds.\n", $log_file);
			sleep($test_sleep_time);
		}

	}
	foreach($test_threads_mixed as $thread)
	{
		for($i=0; $i<$test_iterate; $i++)
		{
			$j = $i + 1;
			testlog("\n=[" . date("Y-m-d H:i:s") . "] $db_host mixed test, $thread thread, iterate $j, started=\n", $log_file);

			$cmd_mixed = "${sysbench_dir}/sysbench --test=${sysbench_dir}/tests/db/oltp.lua --db-driver=mysql --mysql-user=$db_user --mysql-password=$db_pass --mysql-db=$db_name --mysql-table-engine=innodb --mysql-host=$db_host --mysql-port=$db_port --oltp-tables-count=$table_count --oltp-table-size=$table_size --max-requests=0 --max-time=$test_duration --num-threads=$thread run";
			
			exec($cmd_mixed, $output);
			$result = implode("\n", $output);

			testlog("\n[". date("Y-m-d : H:i:s") ."]test results: \n $result \n\n", $log_file);
			testlog("sleeping $test_sleep_time seconds.\n", $log_file);
			sleep($test_sleep_time);
		}

	}
	testlog("Done!", $log_file);


	function testlog($log_msg, $log_file = '/tmp/auto_test.log')
	{
		$fp = fopen($log_file, 'a') or die("can not open log file : $log_file .");
		fwrite($fp, $log_msg);
		fclose($fp);
	}
?>