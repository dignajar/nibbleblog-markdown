<?php

/*
 * Nibbleblog -
 * http://www.nibbleblog.com
 * Author Diego Najar

 * All Nibbleblog code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
*/

class Session {

	public static function init()
	{
		$comment = array(
			'author_name'=>'',
			'author_email'=>'',
			'content'=>'',
			'post_allow_comments'=>false,
			'id_post'=>0
		);

		$_SESSION['nibbleblog'] = array(
			'error'=>false,
			'alert'=>'',
			'comment'=>$comment,
			'last_comment_at'=>0
		);
	}

	public static function reset()
	{
		$last_comment_at = $_SESSION['nibbleblog']['last_comment_at'];
		self::init();
		$_SESSION['nibbleblog']['last_comment_at'] = $last_comment_at;
	}

	public static function get_error()
	{
		return($_SESSION['nibbleblog']['error']);
	}

	public static function get_last_comment_at()
	{
		return($_SESSION['nibbleblog']['last_comment_at']);
	}

	public static function get_alert()
	{
		self::set_error(false);
		return($_SESSION['nibbleblog']['alert']);
	}

	public static function get_comment($key)
	{
		if(isset($_SESSION['nibbleblog']['comment'][$key]))
		{
			return($_SESSION['nibbleblog']['comment'][$key]);
		}

		return false;
	}

	public static function get_comment_array()
	{
		return($_SESSION['nibbleblog']['comment']);
	}

	public static function set_error($boolean = true)
	{
		$_SESSION['nibbleblog']['error'] = $boolean;
	}

	public static function set_last_comment_at($time)
	{
		$_SESSION['nibbleblog']['last_comment_at'] = $time;
	}

	public static function set_alert($text = '')
	{
		$_SESSION['nibbleblog']['alert'] = $text;
	}

	public static function set_comment($comment)
	{
		$_SESSION['nibbleblog']['comment']['author_name'] = $comment['author_name'];
		$_SESSION['nibbleblog']['comment']['author_email'] = $comment['author_email'];
		$_SESSION['nibbleblog']['comment']['content'] = $comment['content'];
		$_SESSION['nibbleblog']['comment']['post_allow_comments'] = $comment['post_allow_comments'];
		$_SESSION['nibbleblog']['comment']['id_post'] = $comment['id_post'];
	}

}

?>