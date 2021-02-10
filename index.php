<?php
	
	$redis = new Redis();
	$redis->connect('127.0.0.1', 6379);

	$prefix   = '-game';

	$request  = $_GET['request'];

	$start  = $_GET['start'];
	$end  = $_GET['end'];

	$score  = $_GET['score'];

	$username = $_GET['username'];

	function get_user_id($username){
		global $prefix;

		return 'user_id:'.sha1($username . $prefix);
	}

	function get_user($username, $field){
		global $prefix;
		global $redis;

		return $redis->hGet(get_user_id($username), $field);
	}

	function get_user_by_id($id, $field){
		global $prefix;
		global $redis;

		return $redis->hGet($id, $field);
	}

	/*
	$redis->hSet('user:'.$hash, 'password', '1c2c3c4c3');
	*/

	if($request == 'create_account'){
		$hash = get_user_id($username);
		echo $hash;

		$redis->hSet(get_user_id($username), 'username', $username);
	}

	if($request == 'get_user'){
		var_dump(get_user($username, 'username'));
	}

	if($request == 'add_game'){
		$user_id = get_user_id($username);

		$item_id = $redis->lLen('user@games:'.$user_id);
		$redis->rPush('user@games:'.$user_id, json_encode(
			array(
				'item_id' => $item_id,
				'time' => 100, 'score' => rand(0, 100), 'time' => date('Y-m-d H:i:s')
			)
		));
	}

	if($request == 'add_leaderboard'){
		$leaderboard = $redis->zAdd('players@score', (int) $score, get_user_id($username));

		echo get_user_id($username);
	}

	if($request == 'get_leaderboard'){
		$leaderboard = $redis->zRevRangeByScore(
			'players@score', 100, 0, 
			array(
				'withscores' => true
			)
		);

		foreach ($leaderboard as $key => $score) {
			echo get_user_by_id($key, 'username') . ' -> ' .$score. "\n";
		}
	}

	if($request == 'get_games'){
		//0, -1 all games
		//-5, -1 last 5 games
		var_dump($redis->lRange('user@games:'.$hash, 0, -1));
	}

	if($request == 'get_range'){
		var_dump($redis->lRange('user@games:'.$hash, $start, $end));
	}

	//echo $redis->hGet('user:'.$hash, 'password');